<?php
// product_detail.php
require __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy product_id từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin sản phẩm từ API
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/products_api.php';
$products_json = @file_get_contents($apiUrl);
$all_products = json_decode($products_json, true) ?: [];

// Tìm sản phẩm theo ID
$product = null;
foreach ($all_products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

// Nếu không tìm thấy sản phẩm, chuyển hướng về trang sản phẩm
if (!$product) {
    header('Location: products.php');
    exit;
}

// Tạo mô tả tính năng nổi bật dựa vào loại sản phẩm
$features = [];
$title = strtolower($product['title']);

if (strpos($title, 'iphone') !== false) {
    $features = [
        'Màn hình Super Retina XDR OLED 6.7 inch - Hiển thị sắc nét với độ sáng cao',
        'Chip A17 Pro - Hiệu năng vượt trội, xử lý tác vụ nhanh chóng',
        'Hệ thống camera Pro 48MP - Chụp ảnh sắc nét, quay video 4K Dolby Vision',
        'Pin lớn, sạc nhanh 25W - Thời lượng sử dụng lâu dài cả ngày'
    ];
} elseif (strpos($title, 'samsung') !== false) {
    $features = [
        'Màn hình Dynamic AMOLED 2X 6.9 inch 120Hz – Hiển thị sắc nét, mượt mà, tiết kiệm pin',
        'Bộ nhớ ' . (isset($product['storage']) ? $product['storage'] : '512GB') . ' + RAM LPDDR5X – Tốc độ lưu trữ nhanh, đa nhiệm mượt mà',
        'Camera 200MP + Zoom 100X – Cảm biến lớn, chụp đêm ấn tượng, zoom siêu xa',
        'Pin 5000mAh, sạc nhanh 45W – Thời lượng pin dài, hỗ trợ sạc không dây'
    ];
} elseif (strpos($title, 'xiaomi') !== false) {
    $features = [
        'Tản nhiệt thông minh, tăng hiệu quả tản nhiệt lên 12,5% so với thế hệ trước, bảo đảm hoạt động ổn định',
        'Pin lớn 5200 mAh thiết kế mỏng nhẹ, độ bền cao với công nghệ xả chậm, cho thời gian sử dụng lâu dài',
        'Hiệu năng mạnh mẽ, chip Mediatek Helio G100 kết hợp với RAM 8GB và ROM 128GB, đáp ứng mọi nhu cầu sử dụng',
        'Camera chất lượng cao, camera sau 50MP, 8MP góc rộng và camera trước 32MP, mang lại hình ảnh sắc nét và sống động'
    ];
} else {
    $features = [
        'Màn hình lớn, độ phân giải cao - Trải nghiệm xem tuyệt vời',
        'Hiệu năng mạnh mẽ - Xử lý tốt mọi tác vụ, chơi game mượt mà',
        'Hệ thống camera đa dạng - Chụp ảnh chất lượng trong mọi điều kiện',
        'Pin dung lượng cao - Sử dụng lâu dài cả ngày'
    ];
}

include 'templates/header.php';
?>

<div class="container py-4">
    <div class="product-detail-container">
        <div class="product-detail-card">
            <div class="row">
                <!-- Hình ảnh sản phẩm -->
                <div class="col-md-5">
                    <div class="product-image-container">
                        <div class="favorite-icon">
                            <i class="far fa-heart"></i>
                        </div>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-detail-image">
                    </div>
                </div>
                
                <!-- Thông tin sản phẩm -->
                <div class="col-md-7">
                    <div class="product-features">
                        <h2 class="features-title">TÍNH NĂNG NỔI BẬT</h2>
                        <ul class="features-list">
                            <?php foreach ($features as $feature): ?>
                                <li class="feature-item">
                                    <i class="fas fa-circle feature-dot"></i>
                                    <?= htmlspecialchars($feature) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="product-price-section mt-4">
                            <div class="product-price">
                                <?= number_format($product['price'], 0, ',', '.') ?> đ
                            </div>
                            
                            <button class="btn-add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-detail-container {
    margin: 20px 0;
}

.product-detail-card {
    background: linear-gradient(90deg, #e66465, #f8b195);
    border-radius: 15px;
    padding: 20px;
    color: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-image-container {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    position: relative;
    margin-bottom: 20px;
}

.favorite-icon {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 30px;
    height: 30px;
    background-color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    color: #e66465;
    cursor: pointer;
}

.product-detail-image {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    max-height: 400px;
    object-fit: contain;
}

.features-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-item {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
}

.feature-dot {
    color: #fff;
    font-size: 8px;
    margin-right: 10px;
    margin-top: 8px;
}

.product-price-section {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.product-price {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
}

.btn-add-to-cart {
    background-color: #ffffff;
    color: #e66465;
    border: none;
    padding: 12px 25px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    max-width: 250px;
}

.btn-add-to-cart:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
}

@media (max-width: 767px) {
    .product-detail-card {
        padding: 15px;
    }
    
    .features-title {
        font-size: 22px;
        margin-top: 20px;
    }
    
    .product-price-section {
        align-items: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý nút thêm vào giỏ hàng
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', async function() {
            const productId = this.dataset.id;
            const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
            
            if (!isLoggedIn) {
                window.location.href = 'login.php';
                return;
            }
            
            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({productId})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Đã thêm sản phẩm vào giỏ hàng');
                    // Có thể chuyển hướng đến trang giỏ hàng
                    // window.location.href = 'cart.php';
                } else {
                    alert('Lỗi: ' + (data.error || 'Không thể thêm vào giỏ hàng'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi thêm vào giỏ hàng');
            }
        });
    }
    
    // Xử lý nút yêu thích
    const favoriteIcon = document.querySelector('.favorite-icon');
    if (favoriteIcon) {
        favoriteIcon.addEventListener('click', function() {
            const heartIcon = this.querySelector('i');
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            heartIcon.classList.toggle('text-danger');
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>