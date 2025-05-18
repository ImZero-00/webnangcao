<?php
// product_management.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Cấu hình đường dẫn lưu ảnh
$image_dir = 'assets/images/products/';
if (!file_exists($image_dir)) {
    mkdir($image_dir, 0777, true);
}

// Đường dẫn file JSON chứa dữ liệu sản phẩm
$products_file = 'products_data.json';

// Đọc dữ liệu sản phẩm từ file
function getProducts() {
    global $products_file;
    if (file_exists($products_file)) {
        $json = file_get_contents($products_file);
        return json_decode($json, true);
    }
    return [];
}

// Lưu dữ liệu sản phẩm vào file
function saveProducts($products) {
    global $products_file;
    $json = json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents($products_file, $json);
}

// Khởi tạo dữ liệu mặc định nếu chưa có
if (!file_exists($products_file)) {
    // Đọc từ products_api.php
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/products_api.php';
    $json = @file_get_contents($api_url);
    $default_products = $json ? json_decode($json, true) : [];
    
    if (empty($default_products)) {
        // Nếu không đọc được từ API, tạo dữ liệu mẫu
        $default_products = [
            ['id'=>1,  'title'=>'iPhone 15 Pro Max 256GB',                'price'=>28990000, 'image'=>'assets/images/phone1.jpg'],
            ['id'=>2,  'title'=>'Samsung Galaxy S24 Ultra 256GB',         'price'=>25990000, 'image'=>'assets/images/phone2.jpg'],
            ['id'=>3,  'title'=>'Xiaomi 14 Pro 512GB',                    'price'=>19990000, 'image'=>'assets/images/phone3.jpg']
        ];
    }
    
    saveProducts($default_products);
}

// Xử lý AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Lấy thông tin chi tiết sản phẩm
    if (isset($_GET['action']) && $_GET['action'] === 'get_product' && isset($_GET['id'])) {
        try {
            $id = (int)$_GET['id'];
            $products = getProducts();
            
            $product = null;
            foreach ($products as $p) {
                if ($p['id'] == $id) {
                    $product = $p;
                    break;
                }
            }
            
            if (!$product) {
                throw new Exception('Không tìm thấy sản phẩm');
            }
            
            echo json_encode(['success' => true, 'product' => $product]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Nếu không khớp với bất kỳ hành động GET nào
    if (isset($_GET['action'])) {
        echo json_encode(['success' => false, 'message' => 'Hành động GET không hợp lệ']);
        exit;
    }
}

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = trim($_POST['title']);
        $price = (int)$_POST['price'];
        
        $products = getProducts();
        
        // Validate
        if (empty($title)) {
            $error = 'Tên sản phẩm không được để trống';
        } elseif ($price <= 0) {
            $error = 'Giá sản phẩm phải lớn hơn 0';
        } else {
            // Xử lý upload hình ảnh
            $image_path = '';
            if ($_POST['action'] === 'update' && empty($_FILES['image']['name'])) {
                // Giữ ảnh cũ khi cập nhật mà không thay đổi ảnh
                foreach ($products as $p) {
                    if ($p['id'] == $id) {
                        $image_path = $p['image'];
                        break;
                    }
                }
            } else {
                if (empty($_FILES['image']['name'])) {
                    $error = 'Vui lòng chọn hình ảnh sản phẩm';
                } else {
                    $file_name = basename($_FILES['image']['name']);
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = date('YmdHis') . '_' . uniqid() . '.' . $file_ext;
                    $target_file = $image_dir . $new_file_name;
                    
                    // Kiểm tra định dạng file
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($file_ext, $allowed_ext)) {
                        $error = 'Chỉ cho phép upload hình ảnh (jpg, jpeg, png, gif)';
                    } elseif ($_FILES['image']['size'] > 5000000) { // 5MB
                        $error = 'Kích thước file tối đa là 5MB';
                    } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $error = 'Không thể upload file';
                    } else {
                        $image_path = $target_file;
                    }
                }
            }
            
            if (empty($error)) {
                if ($_POST['action'] === 'add') {
                    // Tìm ID lớn nhất hiện tại
                    $max_id = 0;
                    foreach ($products as $p) {
                        if ($p['id'] > $max_id) {
                            $max_id = $p['id'];
                        }
                    }
                    
                    // Thêm sản phẩm mới
                    $products[] = [
                        'id' => $max_id + 1,
                        'title' => $title,
                        'price' => $price,
                        'image' => $image_path
                    ];
                    
                    $success = 'Thêm sản phẩm thành công';
                } else {
                    // Cập nhật sản phẩm
                    $updated = false;
                    foreach ($products as &$p) {
                        if ($p['id'] == $id) {
                            $p['title'] = $title;
                            $p['price'] = $price;
                            if (!empty($image_path)) {
                                // Xóa ảnh cũ nếu không phải ảnh mặc định
                                if (strpos($p['image'], 'assets/images/products/') === 0 && file_exists($p['image'])) {
                                    @unlink($p['image']);
                                }
                                $p['image'] = $image_path;
                            }
                            $updated = true;
                            break;
                        }
                    }
                    
                    if (!$updated) {
                        $error = 'Không tìm thấy sản phẩm cần cập nhật';
                    } else {
                        $success = 'Cập nhật sản phẩm thành công';
                    }
                }
                
                // Lưu dữ liệu
                saveProducts($products);
                
                // Cập nhật file products_api.php
                file_put_contents('products_api.php', '<?php
// products_api.php
header(\'Content-Type: application/json\');

$products = ' . var_export($products, true) . ';

echo json_encode($products, JSON_UNESCAPED_UNICODE);
');
            }
        }
    }
    
    // Xử lý xóa sản phẩm
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $products = getProducts();
        
        $found = false;
        foreach ($products as $key => $p) {
            if ($p['id'] == $id) {
                // Xóa ảnh nếu không phải ảnh mặc định
                if (strpos($p['image'], 'assets/images/products/') === 0 && file_exists($p['image'])) {
                    @unlink($p['image']);
                }
                
                // Xóa sản phẩm khỏi mảng
                unset($products[$key]);
                $products = array_values($products); // Reset keys
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $error = 'Không tìm thấy sản phẩm cần xóa';
        } else {
            saveProducts($products);
            
            // Cập nhật file products_api.php
            file_put_contents('products_api.php', '<?php
// products_api.php
header(\'Content-Type: application/json\');

$products = ' . var_export($products, true) . ';

echo json_encode($products, JSON_UNESCAPED_UNICODE);
');
            
            $success = 'Xóa sản phẩm thành công';
        }
    }
}

// Lấy danh sách sản phẩm
$products = getProducts();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - S-Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        .table-responsive {
            margin-top: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #F86338;
            border-color: #F86338;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #e5502a;
            border-color: #e5502a;
        }
        .modal-header {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        .top-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            margin-top: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container">
        <div class="top-header">
            <h2>Quản lý sản phẩm</h2>
            <div>
                <button id="addProductBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </button>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Bảng danh sách sản phẩm -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['id']) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
                        </td>
                        <td><?= htmlspecialchars($product['title']) ?></td>
                        <td><?= number_format($product['price'], 0, ',', '.') ?> đ</td>
                        <td>
                            <button class="btn btn-sm btn-info text-white edit-product" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-product" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Form Modal -->
        <div class="modal fade" id="productModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thông tin sản phẩm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="productForm" method="post" enctype="multipart/form-data" action="product_management.php">
                            <input type="hidden" id="productId" name="id">
                            <input type="hidden" id="actionType" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Tên sản phẩm</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Giá (VNĐ)</label>
                                <input type="number" class="form-control" id="price" name="price" min="1000" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div id="imagePreview"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-primary" id="saveProduct">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Confirm Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa sản phẩm này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <form method="post" action="product_management.php">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" id="deleteProductId">
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <!-- Bootstrap JS và jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {
        // Khởi tạo các modal
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        // Mở modal thêm sản phẩm mới
        $('#addProductBtn').click(function() {
            resetForm();
            $('#actionType').val('add');
            $('.modal-title').text('Thêm sản phẩm mới');
            $('#image').attr('required', true);
            productModal.show();
        });
        
        // Mở modal sửa sản phẩm
        $('.edit-product').click(function() {
            const productId = $(this).data('id');
            resetForm();
            $('#actionType').val('update');
            $('.modal-title').text('Cập nhật thông tin sản phẩm');
            $('#image').removeAttr('required'); // Cho phép không thay đổi ảnh
            
            // Lấy thông tin chi tiết sản phẩm
            $.getJSON('product_management.php', {
                action: 'get_product',
                id: productId
            }).done(function(response) {
                if (response.success) {
                    const product = response.product;
                    $('#productId').val(product.id);
                    $('#title').val(product.title);
                    $('#price').val(product.price);
                    
                    // Hiển thị ảnh preview
                    $('#imagePreview').html(`<img src="${product.image}" alt="Preview" class="preview-image mt-2">`);
                    
                    productModal.show();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                alert('Có lỗi xảy ra khi lấy thông tin sản phẩm: ' + errorThrown);
            });
        });
        
        // Xác nhận xóa sản phẩm
        $('.delete-product').click(function() {
            const productId = $(this).data('id');
            $('#deleteProductId').val(productId);
            deleteModal.show();
        });
        
        // Lưu sản phẩm
        $('#saveProduct').click(function() {
            $('#productForm').submit();
        });
        
        // Preview ảnh khi chọn file
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').html(`<img src="${e.target.result}" alt="Preview" class="preview-image">`);
                }
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').html('');
            }
        });
        
        // Hàm reset form
        function resetForm() {
            $('#productForm')[0].reset();
            $('#productId').val('');
            $('#imagePreview').html('');
        }
        
        // Tự động ẩn thông báo sau 3 giây
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    });
    </script>
</body>
</html>