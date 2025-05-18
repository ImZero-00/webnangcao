<?php 
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>S-Phone</title>
  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
  <!-- Bootstrap CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
  />
  <!-- CSS chính của bạn -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }
    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #F86338;
      text-decoration: none;
    }
    .nav-menu {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
      gap: 10px;
    }
    .nav-menu li {
      position: relative;
    }
    .nav-menu a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
      padding: 8px 15px;
      border-radius: 4px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .nav-menu a:hover {
      background-color: #f8f9fa;
      color: #F86338;
    }
    .nav-menu a.active {
      background-color: #F86338;
      color: white;
    }
    .dropdown-menu {
      position: absolute;
      top: 100%;
      left: 0;
      z-index: 1000;
      display: none;
      min-width: 10rem;
      padding: 0.5rem 0;
      margin: 0.125rem 0 0;
      background-color: #fff;
      background-clip: padding-box;
      border: 1px solid rgba(0,0,0,.15);
      border-radius: 0.25rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .dropdown-item {
      display: block;
      width: 100%;
      padding: 0.5rem 1.5rem;
      clear: both;
      font-weight: 400;
      color: #212529;
      text-align: inherit;
      text-decoration: none;
      white-space: nowrap;
      background-color: transparent;
      border: 0;
    }
    .dropdown-item:hover {
      color: #F86338;
      background-color: #f8f9fa;
    }
    .dropdown-divider {
      height: 0;
      margin: 0.5rem 0;
      overflow: hidden;
      border-top: 1px solid #e9ecef;
    }
    .dropdown:hover .dropdown-menu {
      display: block;
    }
    .dropdown-toggle::after {
      display: inline-block;
      margin-left: 0.255em;
      vertical-align: 0.255em;
      content: "";
      border-top: 0.3em solid;
      border-right: 0.3em solid transparent;
      border-bottom: 0;
      border-left: 0.3em solid transparent;
    }
    .cart-badge {
      position: relative;
      top: -8px;
      right: 5px;
      padding: 2px 5px;
      border-radius: 50%;
      background-color: #F86338;
      color: white;
      font-size: 10px;
    }
    .btn-logout {
      background-color: #f8f9fa;
      color: #dc3545;
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .btn-logout:hover {
      background-color: #dc3545;
      color: white;
    }
  </style>
</head>
<body>
  <header>
    <div class="container header-container">
      <a href="index.php" class="logo">S-Phone</a>
      <nav>
        <ul class="nav-menu">
          <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
          <li><a href="products.php"><i class="fas fa-mobile-alt"></i> Sản phẩm</a></li>
          
          <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Menu người dùng -->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle">
                <i class="fas fa-user"></i> Tài khoản
              </a>
              <div class="dropdown-menu">
                <?php if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1): ?>
                  <!-- Chỉ hiển thị cho user thông thường, không hiển thị cho admin -->
                  <a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user-edit"></i> Thông tin cá nhân
                  </a>
                <?php endif; ?>
                <a class="dropdown-item" href="cart.php">
                  <i class="fas fa-shopping-cart"></i> Giỏ hàng
                </a>
                <a class="dropdown-item" href="transaction_search.php">
                  <i class="fas fa-history"></i> Lịch sử giao dịch
                </a>
              </div>
            </li>
            
            <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
              <!-- Menu Admin -->
              <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                  <i class="fas fa-cogs"></i> Quản lý
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="user_management.php">
                    <i class="fas fa-users-cog"></i> Quản lý người dùng
                  </a>
                  <a class="dropdown-item" href="product_management.php">
                    <i class="fas fa-boxes"></i> Quản lý sản phẩm
                  </a>
                </div>
              </li>
            <?php endif; ?>
            
            <!-- Đăng xuất -->
            <li>
              <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
              </a>
            </li>
          <?php else: ?>
            <li>
              <a href="login.php">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>