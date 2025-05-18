<?php
session_start();
require_once __DIR__ . '/db.php';

// 1) Xử lý AJAX trả JSON
if (isset($_GET['action']) && $_GET['action'] === 'fetch_transactions') {
    header('Content-Type: application/json');
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success'=>false,'error'=>'Vui lòng đăng nhập']);
        exit;
    }

    $userId   = $_SESSION['user_id'];
    $fromDate = ($_GET['from_date'] ?? date('Y-m-d')) . ' 00:00:00';
    $toDate   = ($_GET['to_date']   ?? date('Y-m-d')) . ' 23:59:59';
    $page     = max(1, (int)($_GET['page']  ?? 1));
    $limit    = max(1, (int)($_GET['limit'] ?? 10));
    $offset   = ($page - 1) * $limit;

    try {
        // Tổng số giao dịch
        $stmtTotal = $pdo->prepare("
          SELECT COUNT(*) FROM orders
          WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmtTotal->execute([$userId, $fromDate, $toDate]);
        $total = (int)$stmtTotal->fetchColumn();

        // Tổng số tiền
        $stmtSum = $pdo->prepare("
          SELECT COALESCE(SUM(total),0) FROM orders
          WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmtSum->execute([$userId, $fromDate, $toDate]);
        $sum = (float)$stmtSum->fetchColumn();

        // Dữ liệu chi tiết, **bắt buộc override status = 'PAID'**
        $stmt = $pdo->prepare("
          SELECT
            id AS orderId,
            DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            total AS amount,
            /* Thay vì lấy status từ DB, ta chọn hằng 'PAID' */
            'PAID' AS status,
            CONCAT('Thanh toán đơn #', id) AS content
          FROM orders
          WHERE user_id = ? AND created_at BETWEEN ? AND ?
          ORDER BY created_at DESC
          LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $fromDate, $toDate, $limit, $offset]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
          'success' => true,
          'data'    => [
            'transactions' => $rows,
            'total'        => $total,
            'sum'          => $sum
          ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

// 2) Hiển thị HTML (giữ nguyên giao diện) :contentReference[oaicite:0]{index=0}&#8203;:contentReference[oaicite:1]{index=1}
header('Content-Type: text/html; charset=UTF-8');
include __DIR__ . '/templates/header.php';
?>

<style>
  .table-responsive.custom-border {
    border: 2px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 16px;
  }
  .table-responsive.custom-border table {
    width: 100%;
    border-collapse: collapse;
  }
  .table-responsive.custom-border th,
  .table-responsive.custom-border td {
    border: 1px solid #ddd;
    padding: 0.75rem 1.5rem;
    vertical-align: middle;
  }
  .table-responsive.custom-border thead th {
    background-color: #f8f9fa;
  }
</style>

<main class="container py-5">
  <h2 class="fw-bold mb-4">Lịch sử giao dịch</h2>

  <!-- Filter ngày -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Từ ngày</label>
          <input type="text" id="fromDate" class="form-control" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Đến ngày</label>
          <input type="text" id="toDate" class="form-control" />
        </div>
        <div class="col-md-4 text-end">
          <button id="searchBtn" class="btn btn-primary mt-2">
            <i class="fas fa-search"></i> Tìm kiếm
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Tổng số giao dịch: <span id="totalCount">0</span></h5>
    <h5>Tổng số tiền: <span id="totalAmount">0</span> đ</h5>
  </div>

  <!-- Bảng giao dịch -->
  <div class="table-responsive custom-border">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>STT</th>
          <th>Mã đơn</th>
          <th>Số tiền</th>
          <th>Nội dung</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
        </tr>
      </thead>
      <tbody id="transactionsTableBody"></tbody>
    </table>
  </div>

  <!-- Phân trang -->
  <div class="d-flex justify-content-center">
    <ul class="pagination mb-0" id="pagination"></ul>
  </div>
</main>

<?php include __DIR__ . '/templates/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1/daterangepicker.min.js"></script>
<script>
$(function(){
  const limit = 10;
  let currentPage = 1;

  // Khởi datepickers
  $('#fromDate, #toDate').daterangepicker({
    singleDatePicker: true,
    locale: { format: 'YYYY-MM-DD' }
  });
  $('#fromDate').data('daterangepicker').setStartDate(moment().subtract(30,'days'));
  $('#toDate').data('daterangepicker').setStartDate(moment());

  // Hàm fetch & render
  function fetchTransactions(page = 1) {
    $.getJSON('transaction_search.php', {
      action: 'fetch_transactions',
      page,
      limit,
      from_date: $('#fromDate').val(),
      to_date:   $('#toDate').val()
    }).done(res => {
      if (!res.success) return alert('Lỗi: ' + res.error);

      const { transactions, total, sum } = res.data;
      $('#totalCount').text(total);
      $('#totalAmount').text(Number(sum).toLocaleString('vi-VN'));

      const tbody = $('#transactionsTableBody').empty();
      transactions.forEach((tx, i) => {
        tbody.append(`
          <tr>
            <td>${i + 1 + (page - 1) * limit}</td>
            <td>#${tx.orderId}</td>
            <td>${Number(tx.amount).toLocaleString('vi-VN')} đ</td>
            <td>${tx.content}</td>
            <td><span class="badge badge-success">${tx.status}</span></td>
            <td>${tx.created_at}</td>
          </tr>
        `);
      });

      // Vẽ phân trang
      const totalPages = Math.ceil(total / limit);
      const $pg = $('#pagination').empty();
      for (let p = 1; p <= totalPages; p++) {
        $pg.append(`
          <li class="page-item ${p===page?'active':''}">
            <a href="#" class="page-link" data-page="${p}">${p}</a>
          </li>
        `);
      }
    }).fail((_,__,err) => {
      alert('Không lấy được dữ liệu: ' + err);
    });
  }

  // Event tìm kiếm & phân trang
  $('#searchBtn').click(e => { e.preventDefault(); currentPage = 1; fetchTransactions(1); });
  $('#pagination').on('click', '.page-link', function(e){
    e.preventDefault();
    const p = +$(this).data('page');
    if (p >= 1) { currentPage = p; fetchTransactions(p); }
  });

  // Load lần đầu
  fetchTransactions();
});
</script>
