<?php
// forgot_password.php
require __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã login, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$step = 1; // Bước 1: Xác thực thông tin, Bước 2: Đổi mật khẩu
$user_data = null;

// Xử lý Bước 1: Xác thực thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $address_detail = trim($_POST['address_detail']);
    
    // Kiểm tra thông tin trong database
    $stmt = $pdo->prepare("
        SELECT id, username 
        FROM users 
        WHERE username = ? AND phone = ? AND country = ? AND province = ? AND district = ? AND address_detail = ?
    ");
    $stmt->execute([$username, $phone, $country, $province, $district, $address_detail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Nếu thông tin đúng, chuyển sang bước 2
        $step = 2;
        $user_data = $user;
        // Lưu ID người dùng vào session tạm thời
        $_SESSION['reset_user_id'] = $user['id'];
    } else {
        $error = 'Thông tin không chính xác. Vui lòng kiểm tra lại.';
    }
}

// Xử lý Bước 2: Đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['reset_user_id'] ?? 0;
    
    if (empty($new_password)) {
        $error = 'Vui lòng nhập mật khẩu mới';
        $step = 2;
    } elseif ($new_password !== $confirm_password) {
        $error = 'Xác nhận mật khẩu không khớp';
        $step = 2;
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
        $step = 2;
    } elseif ($user_id <= 0) {
        $error = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
        $step = 1;
    } else {
        // Cập nhật mật khẩu mới
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hash, $user_id]);
        
        if ($result) {
            // Xóa session tạm thời
            unset($_SESSION['reset_user_id']);
            $success = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.';
            // Tự động chuyển về trang đăng nhập sau 3 giây
            header('Refresh: 3; URL=login.php');
        } else {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
            $step = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - S-Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Inter', sans-serif;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }
        .form-control {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #F86338;
            box-shadow: 0 0 0 0.2rem rgba(248, 99, 56, 0.25);
        }
        .btn-primary {
            background-color: #F86338;
            border-color: #F86338;
            padding: 10px 20px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #e5502a;
            border-color: #e5502a;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 10px 20px;
            font-weight: 500;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #F86338;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step-number.active {
            background-color: #F86338;
            color: white;
        }
        .step-text {
            font-size: 12px;
            color: #6c757d;
        }
        .step-text.active {
            color: #F86338;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Quên mật khẩu</h2>
            
            <!-- Hiển thị các bước -->
            <div class="steps">
                <div class="step">
                    <div class="step-number <?= $step == 1 ? 'active' : '' ?>">1</div>
                    <div class="step-text <?= $step == 1 ? 'active' : '' ?>">Xác thực thông tin</div>
                </div>
                <div class="step">
                    <div class="step-number <?= $step == 2 ? 'active' : '' ?>">2</div>
                    <div class="step-text <?= $step == 2 ? 'active' : '' ?>">Đặt lại mật khẩu</div>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($step == 1 && empty($success)): ?>
                <!-- Bước 1: Form xác thực thông tin -->
                <p class="form-subtitle">Vui lòng nhập thông tin tài khoản để xác thực</p>
                <form method="post" action="forgot_password.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required pattern="[0-9]{10}">
                    </div>
                    
                    <div class="form-group">
                        <label for="country" class="form-label">Quốc gia</label>
                        <input type="text" id="country" name="country" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="province" class="form-label">Tỉnh/Thành phố</label>
                        <input type="text" id="province" name="province" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="district" class="form-label">Quận/Huyện</label>
                        <input type="text" id="district" name="district" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address_detail" class="form-label">Địa chỉ chi tiết</label>
                        <textarea id="address_detail" name="address_detail" class="form-control" rows="2" required></textarea>
                    </div>
                    
                    <button type="submit" name="verify" class="btn btn-primary">Tiếp tục</button>
                </form>
            <?php elseif ($step == 2 && empty($success)): ?>
                <!-- Bước 2: Form đổi mật khẩu -->
                <p class="form-subtitle">Thông tin xác thực chính xác. Vui lòng nhập mật khẩu mới.</p>
                <form method="post" action="forgot_password.php">
                    <div class="form-group">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    
                    <button type="submit" name="reset" class="btn btn-primary">Đặt lại mật khẩu</button>
                </form>
            <?php endif; ?>
            
            <div class="login-link">
                <a href="login.php">Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
    
    <script>
        // Tự động ẩn thông báo sau 5 giây
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-danger')) {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    </script>
</body>
</html>