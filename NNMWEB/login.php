<?php
// login.php
require __DIR__ . '/db.php';

// Khởi session nếu chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã login, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Lấy user
    $stmt = $pdo->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Chuyển hướng người dùng đến trang chủ
        header('Location: index.php');
        exit;
    } else {
        $login_error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – S-Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: bold;
            color: #F86338;
        }
        .login-title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }
        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #F86338;
            box-shadow: 0 0 0 0.2rem rgba(248, 99, 56, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
        }
        .btn-login {
            background-color: #F86338;
            border-color: #F86338;
            color: #fff;
            width: 100%;
            padding: 12px;
            font-weight: 500;
            font-size: 16px;
            border-radius: 4px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #e5502a;
            border-color: #e5502a;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .links-container {
            text-align: center;
            margin-top: 20px;
        }
        .links-container a {
            color: #F86338;
            text-decoration: none;
            transition: color 0.3s;
        }
        .links-container a:hover {
            color: #e5502a;
            text-decoration: underline;
        }
        .separator {
            margin: 0 10px;
            color: #ccc;
        }
        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #6c757d;
            text-decoration: none;
        }
        .forgot-password:hover {
            color: #F86338;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">S-Phone</div>
        <h2 class="login-title">Đăng nhập</h2>
        
        <?php if($login_error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($login_error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <div class="form-group">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-login">Đăng nhập</button>
        </form>
        
        <a href="forgot_password.php" class="forgot-password">
            <i class="fas fa-key me-1"></i> Quên mật khẩu?
        </a>
        
        <div class="links-container">
            <p>Bạn mới biết đến S-Phone? <a href="register.php">Đăng ký</a></p>
        </div>
    </div>

    <script>
        // Tự động ẩn thông báo lỗi sau 5 giây
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>