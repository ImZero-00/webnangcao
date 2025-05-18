<?php
// register.php
require __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã login, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$reg_error   = '';
$reg_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username       = trim($_POST['username']);
    $phone          = trim($_POST['phone']);
    $password       = $_POST['password'];
    $country        = trim($_POST['country'] ?? '');
    $province       = trim($_POST['province'] ?? '');
    $district       = trim($_POST['district'] ?? '');
    $address_detail = trim($_POST['address_detail'] ?? '');

    // Validate
    if (strlen($username) < 3) {
        $reg_error = 'Tên đăng nhập phải ít nhất 3 ký tự.';
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $reg_error = 'Số điện thoại phải đúng 10 chữ số.';
    } elseif (strlen($password) < 6) {
        $reg_error = 'Mật khẩu phải ít nhất 6 ký tự.';
    } elseif (empty($country) || empty($province) || empty($district) || empty($address_detail)) {
        $reg_error = 'Vui lòng điền đầy đủ thông tin địa chỉ.';
    } else {
        // Kiểm tra trùng username hoặc phone
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR phone = ?");
        $stmt->execute([$username, $phone]);
        if ($stmt->fetchColumn() > 0) {
            $reg_error = 'Username hoặc Số điện thoại đã tồn tại.';
        } else {
            // Chèn vào DB
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("
                INSERT INTO users 
                  (username, phone, password, country, province, district, address_detail)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $ok = $ins->execute([
                $username,
                $phone,
                $hash,
                $country,
                $province,
                $district,
                $address_detail
            ]);
            if ($ok) {
                $reg_success = 'Đăng ký thành công! Bạn có thể <a href="login.php">đăng nhập ngay</a>.';
            } else {
                $reg_error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Đăng ký – S-Phone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .auth-container {
      max-width: 500px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .auth-container h2 {
      margin-bottom: 20px;
      font-size: 24px;
      color: #333;
      text-align: center;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: #555;
    }
    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .btn-auth {
      width: 100%;
      padding: 12px;
      background-color: #F86338;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }
    .btn-auth:hover {
      background-color: #e5502a;
    }
    .error {
      color: #e74c3c;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #ffebee;
      border-radius: 4px;
    }
    .success {
      color: #2ecc71;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #e8f5e9;
      border-radius: 4px;
    }
    hr {
      margin: 20px 0;
      border-color: #eee;
    }
  </style>
</head>
<body style="background-color: #f5f5f5;">
  <div class="auth-container">
    <h2>Đăng ký</h2>

    <?php if ($reg_error): ?>
      <div class="error"><?= $reg_error ?></div>
    <?php elseif ($reg_success): ?>
      <div class="success"><?= $reg_success ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" class="form-control" required value="<?= htmlentities($username ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="phone">Số điện thoại</label>
        <input type="tel" id="phone" name="phone" class="form-control" required value="<?= htmlentities($phone ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <hr>

      <div class="form-group">
        <label for="country">Quốc gia</label>
        <input type="text" id="country" name="country" class="form-control" required value="<?= htmlentities($country ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="province">Tỉnh / Thành phố</label>
        <input type="text" id="province" name="province" class="form-control" required value="<?= htmlentities($province ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="district">Quận / Huyện</label>
        <input type="text" id="district" name="district" class="form-control" required value="<?= htmlentities($district ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="address_detail">Địa chỉ chi tiết</label>
        <textarea id="address_detail" name="address_detail" rows="3" class="form-control" required><?= htmlentities($address_detail ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-auth">Đăng ký</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
      Đã có tài khoản? <a href="login.php" style="color: #F86338; text-decoration: none;">Đăng nhập</a>
    </p>
  </div>
</body>
</html>