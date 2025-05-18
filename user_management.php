<?php
// user_management.php
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

// Xử lý AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Xử lý thêm user
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        try {
            $username = trim($_POST['username']);
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];
            $country = trim($_POST['country']);
            $province = trim($_POST['province']);
            $district = trim($_POST['district']);
            $address_detail = trim($_POST['address_detail']);
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;
            
            // Kiểm tra username và phone đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR phone = ?");
            $stmt->execute([$username, $phone]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Username hoặc số điện thoại đã tồn tại');
            }
            
            // Thêm user mới
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users 
                    (username, phone, password, country, province, district, address_detail, is_admin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $phone, $hash, $country, $province, $district, $address_detail, $is_admin]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Xử lý cập nhật user
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        try {
            $id = (int)$_POST['id'];
            $username = trim($_POST['username']);
            $phone = trim($_POST['phone']);
            $country = trim($_POST['country']);
            $province = trim($_POST['province']);
            $district = trim($_POST['district']);
            $address_detail = trim($_POST['address_detail']);
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;
            
            // Kiểm tra username và phone đã tồn tại chưa (trừ user hiện tại)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR phone = ?) AND id != ?");
            $stmt->execute([$username, $phone, $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Username hoặc số điện thoại đã tồn tại');
            }
            
            // Cập nhật thông tin
            $sql = "
                UPDATE users SET 
                    username = ?, 
                    phone = ?, 
                    country = ?, 
                    province = ?, 
                    district = ?, 
                    address_detail = ?,
                    is_admin = ?
                WHERE id = ?
            ";
            
            // Nếu có cập nhật mật khẩu
            if (!empty($_POST['password'])) {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "
                    UPDATE users SET 
                        username = ?, 
                        phone = ?, 
                        password = ?,
                        country = ?, 
                        province = ?, 
                        district = ?, 
                        address_detail = ?,
                        is_admin = ?
                    WHERE id = ?
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $phone, $hash, $country, $province, $district, $address_detail, $is_admin, $id]);
            } else {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $phone, $country, $province, $district, $address_detail, $is_admin, $id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Xử lý xóa user
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        try {
            $id = (int)$_POST['id'];
            
            // Không cho phép xóa chính mình
            if ($id === (int)$_SESSION['user_id']) {
                throw new Exception('Không thể xóa tài khoản đang đăng nhập');
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Lấy thông tin chi tiết user - PHẦN NÀY ĐÃ ĐƯỢC SỬA
    if (isset($_GET['action']) && $_GET['action'] === 'get_user' && isset($_GET['id'])) {
        try {
            $id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT id, username, phone, country, province, district, address_detail, is_admin FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('Không tìm thấy người dùng');
            }
            
            echo json_encode(['success' => true, 'user' => $user]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Nếu không khớp với bất kỳ hành động nào
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    exit;
}

// Lấy danh sách người dùng
$stmt = $pdo->query("SELECT id, username, phone, country, province, district, address_detail, is_admin, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - S-Phone</title>
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
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container">
        <div class="top-header">
            <h2>Quản lý người dùng</h2>
            <div>
                <button id="addUserBtn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm người dùng mới
                </button>
            </div>
        </div>
        
        <div id="message-container"></div>
        
        <!-- Bảng danh sách người dùng -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Quyền</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td>
                            <?= htmlspecialchars($user['country']) ?>, 
                            <?= htmlspecialchars($user['province']) ?>, 
                            <?= htmlspecialchars($user['district']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($user['address_detail']) ?></small>
                        </td>
                        <td><?= $user['is_admin'] ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-secondary">User</span>' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info edit-user text-white" data-id="<?= $user['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-user" data-id="<?= $user['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Form Modal -->
        <div class="modal fade" id="userModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thông tin người dùng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            <input type="hidden" id="userId" name="id">
                            <input type="hidden" id="actionType" name="action" value="add">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10}">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu <span id="pwdHelp" class="text-muted">(để trống nếu không thay đổi)</span></label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Quốc gia</label>
                                    <input type="text" class="form-control" id="country" name="country" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="province" class="form-label">Tỉnh/Thành phố</label>
                                    <input type="text" class="form-control" id="province" name="province" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="district" class="form-label">Quận/Huyện</label>
                                    <input type="text" class="form-control" id="district" name="district" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="address_detail" class="form-label">Địa chỉ chi tiết</label>
                                    <textarea class="form-control" id="address_detail" name="address_detail" rows="2" required></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin">
                                <label class="form-check-label" for="is_admin">Quyền Admin</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-primary" id="saveUser">Lưu</button>
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
                        <p>Bạn có chắc chắn muốn xóa người dùng này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
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
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let deleteUserId = null;
        
        // Hiển thị thông báo
        function showMessage(message, type = 'success') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            $('#message-container').html(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                $('.alert').alert('close');
            }, 3000);
        }
        
        // Mở modal thêm người dùng mới
        $('#addUserBtn').click(function() {
            resetForm();
            $('#actionType').val('add');
            $('#pwdHelp').hide();
            $('#password').attr('required', true);
            $('.modal-title').text('Thêm người dùng mới');
            userModal.show();
        });
        
        // Mở modal sửa người dùng
        $('.edit-user').click(function() {
            const userId = $(this).data('id');
            resetForm();
            $('#actionType').val('update');
            $('#pwdHelp').show();
            $('#password').removeAttr('required');
            $('.modal-title').text('Cập nhật thông tin người dùng');
            
            // Lấy thông tin chi tiết user
            $.getJSON('user_management.php', {
                action: 'get_user',
                id: userId
            }).done(function(response) {
                if (response.success) {
                    const user = response.user;
                    $('#userId').val(user.id);
                    $('#username').val(user.username);
                    $('#phone').val(user.phone);
                    $('#country').val(user.country);
                    $('#province').val(user.province);
                    $('#district').val(user.district);
                    $('#address_detail').val(user.address_detail);
                    $('#is_admin').prop('checked', user.is_admin == 1);
                    userModal.show();
                } else {
                    showMessage(response.message, 'error');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                showMessage('Có lỗi xảy ra khi lấy thông tin người dùng: ' + errorThrown, 'error');
            });
        });
        
        // Xác nhận xóa người dùng
        $('.delete-user').click(function() {
            deleteUserId = $(this).data('id');
            deleteModal.show();
        });
        
        // Xử lý xóa người dùng
        $('#confirmDelete').click(function() {
            if (!deleteUserId) return;
            
            $.post('user_management.php', {
                action: 'delete',
                id: deleteUserId
            }).done(function(response) {
                if (response.success) {
                    deleteModal.hide();
                    showMessage('Xóa người dùng thành công');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(response.message, 'error');
                }
            }).fail(function() {
                showMessage('Có lỗi xảy ra khi xóa người dùng', 'error');
            });
        });
        
        // Lưu thông tin người dùng
        $('#saveUser').click(function() {
            const form = $('#userForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const formData = $('#userForm').serialize();
            $.post('user_management.php', formData)
                .done(function(response) {
                    if (response.success) {
                        userModal.hide();
                        showMessage(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showMessage(response.message, 'error');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    showMessage('Có lỗi xảy ra khi lưu thông tin: ' + errorThrown, 'error');
                });
        });
        
        // Hàm reset form
        function resetForm() {
            $('#userForm')[0].reset();
            $('#userId').val('');
        }
    });
    </script>
</body>
</html>