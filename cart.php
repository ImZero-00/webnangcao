<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    // chưa đăng nhập → chuyển về trang login
    header('Location: login.php');
    exit;
}

// cart.php
require __DIR__ . '/db.php';

// 1) LẤY DANH SÁCH SẢN PHẨM TỪ API NỘI BỘ
$apiUrl = 'http://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME'])
        . '/products_api.php';

$json = @file_get_contents($apiUrl);
$allProducts = $json ? json_decode($json, true) : [];
if (!is_array($allProducts)) {
    $allProducts = [];
}

// Build map [id => product]
$prodMap = [];
foreach ($allProducts as $p) {
    if (isset($p['id'])) {
        $prodMap[$p['id']] = $p;
    }
}

// 2) LẤY CART ITEMS TỪ CSDL
$session = session_id();
$stmt = $pdo->prepare("
    SELECT id, product_id, quantity
    FROM cart_items
    WHERE session_id = ?
");
$stmt->execute([$session]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!is_array($cartItems)) {
    $cartItems = [];
}

// Tính tổng tiền và số lượng trong giỏ hàng
$totalAmount = 0;
$totalItems = 0;
foreach ($cartItems as $item) {
    $prodId = $item['product_id'];
    if (!isset($prodMap[$prodId])) continue;
    $prod = $prodMap[$prodId];
    $price = (int)$prod['price'];
    $quantity = (int)$item['quantity'];
    $totalAmount += $price * $quantity;
    $totalItems += $quantity;
}

include 'templates/header.php';
?>

<main class="container py-5">
    <div class="cart-page">
        <!-- Tiêu đề trang -->
        <div class="page-header mb-4">
            <h1 class="page-title">Giỏ hàng của bạn</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
                </ol>
            </nav>
        </div>
        
        <!-- Thông báo giỏ hàng trống -->
        <?php if (empty($cartItems)): ?>
        <div class="empty-cart text-center py-5">
            <div class="empty-cart-icon mb-4">
                <i class="fas fa-shopping-cart fa-4x text-muted"></i>
            </div>
            <h3 class="mb-3">Giỏ hàng của bạn đang trống</h3>
            <p class="text-muted mb-4">Hãy thêm sản phẩm để tiếp tục mua sắm</p>
            <a href="products.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
        <?php else: ?>
        
        <!-- Nội dung giỏ hàng -->
        <div class="row">
            <!-- Danh sách sản phẩm -->
            <div class="col-lg-8">
                <div class="cart-list mb-4">
                    <div class="cart-header d-none d-md-flex">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">Sản phẩm</label>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">Đơn giá</div>
                        <div class="col-md-2 text-center">Số lượng</div>
                        <div class="col-md-2 text-center">Thành tiền</div>
                    </div>
                    
                    <div id="cart-body">
                        <?php foreach ($cartItems as $item): 
                            $prodId = $item['product_id'];
                            if (!isset($prodMap[$prodId])) continue;
                            $prod = $prodMap[$prodId];
                            $price = (int)$prod['price'];
                            $quantity = (int)$item['quantity'];
                            $subtotal = $price * $quantity;
                        ?>
                        <div class="cart-item" data-id="<?= $item['id'] ?>">
                            <div class="row align-items-center py-3">
                                <div class="col-md-6">
                                    <div class="product-info d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input select-item" type="checkbox" checked>
                                        </div>
                                        <div class="product-image">
                                            <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" class="img-fluid">
                                        </div>
                                        <div class="product-details">
                                            <h3 class="product-title"><?= htmlspecialchars($prod['title']) ?></h3>
                                            <div class="product-category"><?= isset($prod['category']) ? htmlspecialchars($prod['category']) : 'Smartphone' ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="product-price text-md-center">
                                        <?= number_format($price, 0, ',', '.') ?> đ
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="quantity-control text-md-center">
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary decrease" type="button">-</button>
                                            <input type="text" class="form-control text-center" value="<?= $quantity ?>">
                                            <button class="btn btn-outline-secondary increase" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="product-subtotal text-md-center" data-raw="<?= $subtotal ?>">
                                        <div class="price"><?= number_format($subtotal, 0, ',', '.') ?> đ</div>
                                        <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Buttons giỏ hàng -->
                <div class="cart-actions d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="master-select" checked>
                            <label class="form-check-label" for="master-select">Chọn tất cả</label>
                        </div>
                        <button id="delete-selected" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> Xóa mục đã chọn
                        </button>
                    </div>
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
            
            <!-- Tóm tắt đơn hàng -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h3 class="summary-title">Tóm tắt đơn hàng</h3>
                    
                    <div class="summary-item">
                        <span class="summary-label">Tạm tính</span>
                        <span class="summary-value" id="subtotal-amount"><?= number_format($totalAmount, 0, ',', '.') ?> đ</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Giảm giá</span>
                        <span class="summary-value">0 đ</span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-item total">
                        <span class="summary-label">Tổng cộng</span>
                        <span class="summary-value" id="total-amount"><?= number_format($totalAmount, 0, ',', '.') ?> đ</span>
                    </div>
                    
                    <button id="checkout-btn" class="btn btn-primary btn-lg w-100 mt-3">
                        Thanh toán ngay
                    </button>
                    
                    <div class="payment-methods mt-3">
                        <div class="payment-method-title">Chấp nhận thanh toán</div>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-paypal"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'templates/footer.php'; ?>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirm-message">Bạn có chắc chắn muốn xóa sản phẩm này?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirm-action">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Cart page styles */
.cart-page {
    min-height: 400px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #1A1A1A;
}

.breadcrumb {
    margin-bottom: 0;
}

.breadcrumb-item a {
    color: #7A7A7A;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #F86338;
}

/* Empty cart */
.empty-cart-icon {
    color: #ccc;
}

/* Cart list */
.cart-list {
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.cart-header {
    font-weight: 600;
    padding: 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.cart-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.product-image {
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 8px;
    margin: 0 15px;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #1A1A1A;
}

.product-category {
    font-size: 12px;
    color: #7A7A7A;
}

.product-price {
    font-weight: 600;
    color: #1A1A1A;
}

.quantity-control .input-group {
    width: 100px;
    margin: 0 auto;
}

.quantity-control input {
    height: 36px;
}

.product-subtotal {
    position: relative;
}

.price {
    font-weight: 700;
    color: #F86338;
}

.delete-btn {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    transition: color 0.2s;
    margin-top: 5px;
    padding: 5px;
    display: inline-block;
}

.delete-btn:hover {
    color: #dc3545;
}

/* Order summary */
.order-summary {
    background-color: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 20px;
}

.summary-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.summary-label {
    color: #7A7A7A;
}

.summary-value {
    font-weight: 600;
}

.summary-divider {
    height: 1px;
    background-color: #eee;
    margin: 15px 0;
}

.summary-item.total {
    font-size: 18px;
    font-weight: 700;
}

.summary-item.total .summary-value {
    color: #F86338;
}

/* Payment methods */
.payment-methods {
    text-align: center;
}

.payment-method-title {
    font-size: 12px;
    color: #7A7A7A;
    margin-bottom: 5px;
}

.payment-icons {
    font-size: 24px;
    color: #7A7A7A;
}

.payment-icons i {
    margin: 0 5px;
}

/* Buttons */
.btn-primary {
    background-color: #F86338;
    border-color: #F86338;
}

.btn-primary:hover, .btn-primary:focus {
    background-color: #e54e20;
    border-color: #e54e20;
}

.btn-outline-primary {
    border-color: #F86338;
    color: #F86338;
}

.btn-outline-primary:hover, .btn-outline-primary:focus {
    background-color: #F86338;
    border-color: #F86338;
    color: #fff;
}

/* Responsive styles */
@media (max-width: 767.98px) {
    .product-details {
        min-width: 0;
    }
    
    .product-title {
        font-size: 14px;
    }
    
    .product-subtotal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
    }
    
    .cart-item {
        padding-bottom: 15px;
    }
    
    .order-summary {
        margin-top: 20px;
    }
}
</style>

<script>
// Format số thành chuỗi tiền
function fmt(v) {
  return v.toLocaleString('vi-VN') + ' đ';
}

// Cập nhật tổng tiền dựa trên các checkbox
function updateTotal() {
  let total = 0;
  document.querySelectorAll('.cart-item').forEach(item => {
    const chk = item.querySelector('.select-item');
    if (chk.checked) {
      const raw = parseInt(item.querySelector('.product-subtotal').dataset.raw, 10);
      total += raw;
    }
  });
  document.getElementById('subtotal-amount').textContent = fmt(total);
  document.getElementById('total-amount').textContent = fmt(total);
  document.getElementById('checkout-btn').disabled = total === 0;
}

// Hiển thị modal xác nhận
function showConfirm(message, callback) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    document.getElementById('confirm-message').textContent = message;
    
    // Remove previous event listener
    const confirmBtn = document.getElementById('confirm-action');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', function() {
        modal.hide();
        callback();
    });
    
    modal.show();
}

// Gọi AJAX cập nhật quantity
async function changeQty(row, newQty) {
  const id = row.dataset.id;
  try {
    const response = await fetch('update_cart.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({id, quantity:newQty})
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Cập nhật subtotal hiển thị
      const price = parseInt(row.querySelector('.product-price').textContent.replace(/\D/g,''), 10);
      const sub = price * newQty;
      const cell = row.querySelector('.product-subtotal');
      cell.querySelector('.price').textContent = fmt(sub);
      cell.dataset.raw = sub;
      updateTotal();
    } else {
      alert('Lỗi cập nhật số lượng: ' + (data.error || 'Không xác định'));
    }
  } catch (err) {
    console.error(err);
    alert('Lỗi kết nối server');
  }
}

// Gọi AJAX xóa item
async function deleteItem(row) {
  const id = row.dataset.id;
  try {
    const response = await fetch('delete_cart_item.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({id})
    });
    
    const data = await response.json();
    
    if (data.success) {
      row.remove();
      updateTotal();
      
      // Kiểm tra nếu giỏ hàng trống
      if (document.querySelectorAll('.cart-item').length === 0) {
        location.reload(); // Reload để hiển thị empty cart
      }
    } else {
      alert('Lỗi xóa sản phẩm: ' + (data.error || 'Không xác định'));
    }
  } catch (err) {
    console.error(err);
    alert('Lỗi kết nối server');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const cartBody = document.getElementById('cart-body');

  // Xử lý tăng/giảm và xóa
  if (cartBody) {
    cartBody.addEventListener('click', e => {
      const cartItem = e.target.closest('.cart-item');
      if (!cartItem) return;

      if (e.target.classList.contains('increase')) {
        const inp = cartItem.querySelector('input[type="text"]');
        const v = Math.max(1, parseInt(inp.value,10) + 1);
        inp.value = v;
        changeQty(cartItem, v);
      }
      if (e.target.classList.contains('decrease')) {
        const inp = cartItem.querySelector('input[type="text"]');
        const v = Math.max(1, parseInt(inp.value,10) - 1);
        inp.value = v;
        changeQty(cartItem, v);
      }
      if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
        showConfirm('Bạn có chắc chắn muốn xóa sản phẩm này?', function() {
          deleteItem(cartItem);
        });
      }
    });

    // Xử lý input số lượng
    cartBody.addEventListener('change', e => {
      if (e.target.classList.contains('form-control')) {
        const cartItem = e.target.closest('.cart-item');
        let v = parseInt(e.target.value, 10);
        if (isNaN(v) || v < 1) {
          v = 1;
          e.target.value = v;
        }
        changeQty(cartItem, v);
      }
    });

    // Chọn từng dòng
    cartBody.addEventListener('change', e => {
      if (e.target.classList.contains('select-item')) {
        updateTotal();
        
        // Update master checkbox
        const allCheckboxes = document.querySelectorAll('.select-item');
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        document.getElementById('select-all').checked = allChecked;
        document.getElementById('master-select').checked = allChecked;
      }
    });
  }

  // Chọn tất cả
  const selectAll = document.getElementById('select-all');
  if (selectAll) {
    selectAll.addEventListener('change', e => {
      const v = e.target.checked;
      document.querySelectorAll('.select-item').forEach(ch => ch.checked = v);
      document.getElementById('master-select').checked = v;
      updateTotal();
    });
  }

  const masterSelect = document.getElementById('master-select');
  if (masterSelect) {
    masterSelect.addEventListener('change', e => {
      const v = e.target.checked;
      document.getElementById('select-all').checked = v;
      document.querySelectorAll('.select-item').forEach(ch => ch.checked = v);
      updateTotal();
    });
  }

  // Xóa nhiều mục
  const deleteSelected = document.getElementById('delete-selected');
  if (deleteSelected) {
    deleteSelected.addEventListener('click', () => {
      const selectedItems = document.querySelectorAll('.select-item:checked');
      
      if (selectedItems.length === 0) {
        alert('Vui lòng chọn sản phẩm để xóa');
        return;
      }
      
      showConfirm('Bạn có chắc chắn muốn xóa các mục đã chọn?', function() {
        selectedItems.forEach(ch => deleteItem(ch.closest('.cart-item')));
      });
    });
  }

  // Thanh toán
  const checkoutBtn = document.getElementById('checkout-btn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', async () => {
      // Lấy tổng tiền từ UI
      const totalText = document.getElementById('total-amount').textContent;
      
      // Lấy danh sách id đã chọn
      const selectedItems = document.querySelectorAll('.select-item:checked');
      
      if (selectedItems.length === 0) {
        alert('Vui lòng chọn sản phẩm để thanh toán');
        return;
      }
      
      // Hiện confirm
      showConfirm(`Tổng cộng: ${totalText}\nXác nhận tạo đơn hàng?`, async function() {
        // Lấy danh sách id đã chọn
        const ids = Array.from(selectedItems).map(ch => ch.closest('.cart-item').dataset.id);
        
        // Gửi AJAX tạo order
        try {
          const resp = await fetch('checkout.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ids})
          });
          
          const data = await resp.json();
          
          if (data.success) {
            // Sau khi server tạo xong, chuyển sang trang thanh toán
            window.location = `checkout.php?order_id=${data.orderId}`;
          } else {
            alert('Lỗi: ' + (data.error||'Không thể tạo đơn'));
          }
        } catch (err) {
          console.error(err);
          alert('Lỗi kết nối server');
        }
      });
    });
  }

  // Khởi tạo tổng ban đầu
  updateTotal();
});
</script>