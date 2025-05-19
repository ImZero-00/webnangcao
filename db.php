<?php
// db.php

// Khởi session nếu chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình kết nối MySQL
$host = 'sql201.infinityfree.com';
$db   = 'if0_38425021_facemask';
$user = 'if0_38425021';
$pass = 'Xuandat10022004';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4;port=3306";

try {
    // Tạo kết nối PDO
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Thiết lập múi giờ MySQL về Asia/Ho_Chi_Minh (+07:00)
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Database connection failed.');
}

// Tạo (hoặc cập nhật) bảng users với thêm các cột địa chỉ
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username       VARCHAR(50)  NOT NULL UNIQUE,
            phone          VARCHAR(10)  NOT NULL UNIQUE,
            password       VARCHAR(255) NOT NULL,
            country        VARCHAR(100) NOT NULL,
            province       VARCHAR(100) NOT NULL,
            district       VARCHAR(100) NOT NULL,
            address_detail TEXT         NOT NULL,
            is_admin       TINYINT(1)   NOT NULL DEFAULT 0,
            created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    // Nếu bảng đã có, thêm cột nếu chưa tồn tại
    $pdo->exec("
        ALTER TABLE users
            ADD COLUMN IF NOT EXISTS country VARCHAR(100) NOT NULL AFTER password,
            ADD COLUMN IF NOT EXISTS province VARCHAR(100) NOT NULL AFTER country,
            ADD COLUMN IF NOT EXISTS district VARCHAR(100) NOT NULL AFTER province,
            ADD COLUMN IF NOT EXISTS address_detail TEXT NOT NULL AFTER district,
            ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER address_detail;
    ");
    
    // Thiết lập admin mặc định (user đầu tiên)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("UPDATE users SET is_admin = 1 WHERE id = (SELECT MIN(id) FROM users) LIMIT 1");
    }
} catch (PDOException $e) {
    error_log('Users table migration error: ' . $e->getMessage());
}

// Tạo bảng orders
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT         NOT NULL,
            total   DECIMAL(10,2) NOT NULL,
            status  VARCHAR(20)  NOT NULL DEFAULT 'INIT',
            created_at DATETIME  DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    // Đảm bảo trường status có default
    $pdo->exec("
        ALTER TABLE orders
          MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'INIT';
    ");
} catch (PDOException $e) {
    error_log('Orders table migration error: ' . $e->getMessage());
}

// Tạo bảng order_items
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id   INT         NOT NULL,
            product_id INT         NOT NULL,
            quantity   INT         NOT NULL,
            price      DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {
    error_log('Order_items table migration error: ' . $e->getMessage());
}

// Tạo bảng cart_items
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {
    error_log('Cart_items table migration error: ' . $e->getMessage());
}

// Thêm các cột VNPAY vào bảng orders nếu chưa có
try {
    $pdo->exec("
        ALTER TABLE orders
            ADD COLUMN IF NOT EXISTS vnp_response_code VARCHAR(10)  DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS vnp_bank_code     VARCHAR(20)  DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS vnp_bank_tran_no  VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS vnp_pay_date      VARCHAR(14)  DEFAULT NULL;
    ");
} catch (PDOException $e) {
    error_log('VNPAY columns migration error: ' . $e->getMessage());
}
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            description TEXT,
            specifications TEXT,
            features TEXT,
            color VARCHAR(100),
            storage VARCHAR(100),
            battery VARCHAR(100),
            camera VARCHAR(255),
            display VARCHAR(255),
            processor VARCHAR(255),
            ram VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {
    error_log('Product_details table migration error: ' . $e->getMessage());
}
