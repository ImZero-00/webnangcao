<?php
// profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Lấy thông tin user hiện tại
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Nếu không tìm thấy user (hiếm khi xảy ra)
    session_destroy();
    header('Location: login.php');
    exit;
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $address_detail = trim($_POST['address_detail']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if (strlen($username) < 3) {
        $error = 'Tên đăng nhập phải ít nhất 3 ký tự';
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = 'Số điện thoại phải đúng 10 chữ số';
    } elseif (empty($country) || empty($province) || empty($district) || empty($address_detail)) {
        $error = 'Vui lòng điền đầy đủ thông tin địa chỉ';
    } else {
        // Kiểm tra nếu username hoặc phone đã tồn tại (của người dùng khác)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR phone = ?) AND id != ?");
        $stmt->execute([$username, $phone, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Tên đăng nhập hoặc Số điện thoại đã tồn tại';
        } else {
            // Nếu muốn đổi mật khẩu
            if (!empty($current_password)) {
                // Kiểm tra mật khẩu hiện tại
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Mật khẩu hiện tại không đúng';
                } elseif (empty($new_password)) {
                    $error = 'Vui lòng nhập mật khẩu mới';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Xác nhận mật khẩu không khớp';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Mật khẩu mới phải ít nhất 6 ký tự';
                }
            }
            
            if (empty($error)) {
                try {
                    // Cập nhật thông tin
                    if (!empty($current_password) && !empty($new_password)) {
                        // Cập nhật cả mật khẩu
                        $hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            UPDATE users SET 
                                username = ?, 
                                phone = ?, 
                                password = ?,
                                country = ?, 
                                province = ?, 
                                district = ?, 
                                address_detail = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $username, 
                            $phone, 
                            $hash, 
                            $country, 
                            $province, 
                            $district, 
                            $address_detail, 
                            $_SESSION['user_id']
                        ]);
                    } else {
                        // Không cập nhật mật khẩu
                        $stmt = $pdo->prepare("
                            UPDATE users SET 
                                username = ?, 
                                phone = ?, 
                                country = ?, 
                                province = ?, 
                                district = ?, 
                                address_detail = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $username, 
                            $phone, 
                            $country, 
                            $province, 
                            $district, 
                            $address_detail, 
                            $_SESSION['user_id']
                        ]);
                    }
                    
                    $success = 'Cập nhật thông tin thành công!';
                    
                    // Lấy lại thông tin user sau khi cập nhật
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error = 'Có lỗi xảy ra: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - S-Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
        }
        .password-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn-primary {
            background-color: #F86338;
            border-color: #F86338;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #e5502a;
            border-color: #e5502a;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h2>Thông tin cá nhân</h2>
                <p class="text-muted">Cập nhật thông tin cá nhân của bạn</p>
            </div>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" action="profile.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required pattern="[0-9]{10}">
                    </div>
                </div>
                
                <h5 class="mt-4 mb-3">Địa chỉ</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="country" class="form-label">Quốc gia</label>
                        <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="province" class="form-label">Tỉnh/Thành phố</label>
                        <input type="text" class="form-control" id="province" name="province" value="<?= htmlspecialchars($user['province']) ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="district" class="form-label">Quận/Huyện</label>
                        <input type="text" class="form-control" id="district" name="district" value="<?= htmlspecialchars($user['district']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="address_detail" class="form-label">Địa chỉ chi tiết</label>
                        <textarea class="form-control" id="address_detail" name="address_detail" rows="2" required><?= htmlspecialchars($user['address_detail']) ?></textarea>
                    </div>
                </div>
                
                <div class="password-section">
                    <h5 class="mb-3">Đổi mật khẩu</h5>
                    <p class="text-muted small">Để trống nếu không muốn thay đổi mật khẩu</p>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="col-md-4">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="col-md-4">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary px-5">Cập nhật thông tin</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 5000);
    </script>
</body>
</html>