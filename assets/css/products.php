<?php
// products.php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

require __DIR__ . '/db.php';
include __DIR__ . '/templates/header.php';

// Phân trang client-side
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;

// Lấy danh mục từ query string (nếu có)
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';
?>

<main class="container py-5">
  <!-- Tiêu đề và breadcrumb -->
  <div class="shop-header mb-4">
    <h1 class="shop-title">Sản phẩm</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
      </ol>
    </nav>
  </div>

  <div class="row">
    <!-- Sidebar lọc sản phẩm -->
    <div class="col-lg-3 filter-sidebar">
      <div class="filter-card">
        <h3 class="filter-title">Danh mục</h3>
        <div class="filter-group">
          <div class="form-check">
            <input class="form-check-input category-filter" type="radio" name="category" id="cat-all" value="" checked>
            <label class="form-check-label" for="cat-all">Tất cả</label>
          </div>
          <div class="form-check">
            <input class="form-check-input category-filter" type="radio" name="category" id="cat-iphone" value="iphone">
            <label class="form-check-label" for="cat-iphone">iPhone</label>
          </div>
          <div class="form-check">
            <input class="form-check-input category-filter" type="radio" name="category" id="cat-samsung" value="samsung">
            <label class="form-check-label" for="cat-samsung">Samsung</label>
          </div>
          <div class="form-check">
            <input class="form-check-input category-filter" type="radio" name="category" id="cat-xiaomi" value="xiaomi">
            <label class="form-check-label" for="cat-xiaomi">Xiaomi</label>
          </div>
          <div class="form-check">
            <input class="form-check-input category-filter" type="radio" name="category" id="cat-other" value="other">
            <label class="form-check-label" for="cat-other">Khác</label>
          </div>
        </div>
      </div>

      <div class="filter-card">
        <h3 class="filter-title">Khoảng giá</h3>
        <div class="filter-group">
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-all" value="" checked>
            <label class="form-check-label" for="price-all">Tất cả</label>
          </div>
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-1" value="0-5000000">
            <label class="form-check-label" for="price-1">Dưới 5 triệu</label>
          </div>
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-2" value="5000000-10000000">
            <label class="form-check-label" for="price-2">5 - 10 triệu</label>
          </div>
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-3" value="10000000-15000000">
            <label class="form-check-label" for="price-3">10 - 15 triệu</label>
          </div>
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-4" value="15000000-20000000">
            <label class="form-check-label" for="price-4">15 - 20 triệu</label>
          </div>
          <div class="form-check">
            <input class="form-check-input price-filter" type="radio" name="price" id="price-5" value="20000000-999999999">
            <label class="form-check-label" for="price-5">Trên 20 triệu</label>
          </div>
        </div>
      </div>

      <div class="filter-card">
        <h3 class="filter-title">Sắp xếp theo</h3>
        <div class="filter-group">
          <div class="form-check">
            <input class="form-check-input sort-filter" type="radio" name="sort" id="sort-default" value="default" checked>
            <label class="form-check-label" for="sort-default">Mặc định</label>
          </div>
          <div class="form-check">
            <input class="form-check-input sort-filter" type="radio" name="sort" id="sort-price-asc" value="price-asc">
            <label class="form-check-label" for="sort-price-asc">Giá: Thấp đến cao</label>
          </div>
          <div class="form-check">
            <input class="form-check-input sort-filter" type="radio" name="sort" id="sort-price-desc" value="price-desc">
            <label class="form-check-label" for="sort-price-desc">Giá: Cao đến thấp</label>
          </div>
          <div class="form-check">
            <input class="form-check-input sort-filter" type="radio" name="sort" id="sort-name-asc" value="name-asc">
            <label class="form-check-label" for="sort-name-asc">Tên: A-Z</label>
          </div>
          <div class="form-check">
            <input class="form-check-input sort-filter" type="radio" name="sort" id="sort-name-desc" value="name-desc">
            <label class="form-check-label" for="sort-name-desc">Tên: Z-A</label>
          </div>
        </div>
      </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="col-lg-9">
      <div class="products-header d-flex justify-content-between align-items-center mb-4">
        <div class="showing-products">
          Hiển thị <span id="products-count">0</span> sản phẩm
        </div>
        <div class="search-box me-3">
          <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm sản phẩm...">
        </div>
        <div class="view-options d-flex">
          <button class="btn btn-sm btn-outline-secondary me-2 view-grid active">
            <i class="fas fa-th"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary view-list">
            <i class="fas fa-list"></i>
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="products-container row g-4" id="products-container">
        <!-- Products will be loaded dynamically -->
      </div>

      <!-- Pagination -->
      <div class="pagination-container mt-5 d-flex justify-content-center" id="pagination-container">
        <!-- Pagination will be generated dynamically -->
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/templates/footer.php'; ?>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-success text-white">
      <i class="fas fa-check-circle me-2"></i>
      <strong class="me-auto">Thành công</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Đã thêm sản phẩm vào giỏ hàng.
    </div>
  </div>
</div>

<style>
  /* Styles for product page */
  .shop-title {
    font-size: 32px;
    font-weight: 700;
    color: #1A1A1A;
    margin-bottom: 10px;
  }
  
  .breadcrumb {
    font-size: 14px;
  }
  
  .breadcrumb-item a {
    color: #7A7A7A;
    text-decoration: none;
  }
  
  .breadcrumb-item.active {
    color: #F86338;
  }
  
  .filter-sidebar {
    margin-bottom: 30px;
  }
  
  .filter-card {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  }
  
  .filter-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1A1A1A;
    position: relative;
  }
  
  .filter-title::after {
    content: '';
    display: block;
    width: 30px;
    height: 2px;
    background-color: #F86338;
    margin-top: 8px;
  }
  
  .filter-group {
    margin-bottom: 10px;
  }
  
  .form-check {
    margin-bottom: 8px;
  }
  
  .form-check-input:checked {
    background-color: #F86338;
    border-color: #F86338;
  }
  
  .showing-products {
    font-size: 14px;
    color: #7A7A7A;
  }
  
  .products-container {
    min-height: 400px;
  }
  
  /* Product Card Styles */
  .product-card {
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  
  .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .product-image {
    position: relative;
    height: 200px;
    width: 100%;
    overflow: hidden;
    background-color: #f9f9f9;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    text-decoration: none;
  }
  
  .product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.5s ease;
  }
  
  .product-card:hover .product-image img {
    transform: scale(1.05);
  }
  
  .product-wish {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 30px;
    height: 30px;
    background: rgba(255,255,255,0.8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .product-wish:hover {
    background: #F86338;
    color: white;
  }
  
  .product-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }
  
  .product-category {
    font-size: 12px;
    color: #7A7A7A;
    margin-bottom: 5px;
    text-transform: uppercase;
  }
  
  .product-title-link {
    text-decoration: none;
    color: inherit;
    margin-bottom: 8px;
    display: block;
  }

  .product-title {
    font-size: 16px;
    font-weight: 600;
    color: #1A1A1A;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 38px;
    margin-bottom: 0;
    transition: color 0.3s ease;
  }

  .product-title-link:hover .product-title {
    color: #F86338;
  }
  
  .product-rating {
    color: #FFBD10;
    font-size: 14px;
    margin-bottom: 10px;
  }
  
  .product-price {
    font-weight: 700;
    font-size: 18px;
    color: #F86338;
    margin-top: auto;
    margin-bottom: 15px;
  }
  
  .btn-add-cart {
    background-color: #F86338;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 500;
    transition: all 0.3s ease;
    width: 100%;
  }
  
  .btn-add-cart:hover {
    background-color: #e84d1f;
  }
  
  /* List View */
  .products-container.list-view .product-card {
    flex-direction: row;
    max-height: 200px;
  }
  
  .products-container.list-view .product-image {
    width: 200px;
    height: 200px;
    flex-shrink: 0;
    padding: 10px;
  }
  
  .products-container.list-view .product-content {
    width: calc(100% - 200px);
  }
  
  /* Pagination */
  .page-link {
    color: #1A1A1A;
    padding: 8px 16px;
    border: 1px solid #e9ecef;
  }
  
  .page-item.active .page-link {
    background-color: #F86338;
    border-color: #F86338;
  }
  
  .page-link:hover {
    color: #F86338;
    background-color: #f8f9fa;
  }
  
  /* Toast */
  .toast {
    border-radius: 10px;
  }
  
  /* Responsive */
  @media (max-width: 991.98px) {
    .filter-sidebar {
      margin-bottom: 30px;
    }
  }
  
  @media (max-width: 767.98px) {
    .products-container.list-view .product-card {
      flex-direction: column;
      max-height: none;
    }
    
    .products-container.list-view .product-image,
    .products-container.list-view .product-content {
      width: 100%;
    }
  }
</style>

<script>
// Cấu hình
const API_URL = 'products_api.php';
const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false'; ?>;
let allProducts = [];
let filteredProducts = [];
let currentPage = <?= $page ?>;
const itemsPerPage = <?= $itemsPerPage ?>;
let totalPages = 0;
let currentCategory = '<?= $category ?>';
let currentSort = '<?= $sortBy ?>';
let currentPriceRange = '<?= $priceRange ?>';
let viewMode = 'grid';
let searchQuery = '';

// 1. Load dữ liệu từ API
async function loadProducts() {
  try {
    const res = await fetch(API_URL);
    if (!res.ok) throw new Error('Không lấy được dữ liệu sản phẩm');
    allProducts = await res.json();
    
    // Thêm category nếu chưa có
    allProducts = allProducts.map(p => {
      if (!p.category) {
        if (p.title.toLowerCase().includes('iphone')) {
          p.category = 'iphone';
        } else if (p.title.toLowerCase().includes('samsung')) {
          p.category = 'samsung';
        } else if (p.title.toLowerCase().includes('xiaomi')) {
          p.category = 'xiaomi';
        } else {
          p.category = 'other';
        }
      }
      // Thêm rating ngẫu nhiên nếu chưa có
      if (!p.rating) {
        p.rating = (Math.floor(Math.random() * 5) + 1);
      }
      return p;
    });
    
    // Lọc và render
    applyFilters();
    
    // Set active filters từ URL
    setActiveFilters();
  } catch (err) {
    console.error(err);
    document.getElementById('products-container').innerHTML =
      '<div class="col-12"><div class="alert alert-danger">Có lỗi khi tải sản phẩm. Vui lòng thử lại sau.</div></div>';
  }
}

// 2. Áp dụng bộ lọc
function applyFilters() {
  // Lọc theo category
  filteredProducts = allProducts.filter(p => {
    if (!currentCategory) return true;
    return p.category.toLowerCase() === currentCategory.toLowerCase();
  });
  
  // Lọc theo price range
  if (currentPriceRange) {
    const [min, max] = currentPriceRange.split('-').map(Number);
    filteredProducts = filteredProducts.filter(p => {
      const price = parseInt(p.price);
      return price >= min && price <= max;
    });
  }

  // Lọc theo từ khóa tìm kiếm
  if (searchQuery) {
    filteredProducts = filteredProducts.filter(p => 
      p.title.toLowerCase().includes(searchQuery.toLowerCase())
    );
  }
  
  // Sắp xếp
  if (currentSort === 'price-asc') {
    filteredProducts.sort((a, b) => parseInt(a.price) - parseInt(b.price));
  } else if (currentSort === 'price-desc') {
    filteredProducts.sort((a, b) => parseInt(b.price) - parseInt(a.price));
  } else if (currentSort === 'name-asc') {
    filteredProducts.sort((a, b) => a.title.localeCompare(b.title));
  } else if (currentSort === 'name-desc') {
    filteredProducts.sort((a, b) => b.title.localeCompare(a.title));
  }
  
  // Cập nhật tổng số và render
  totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  document.getElementById('products-count').textContent = filteredProducts.length;
  
  // Đặt lại trang hiện tại nếu vượt quá tổng số trang
  if (currentPage > totalPages) {
    currentPage = 1;
  }
  
  renderPage(currentPage);
  renderPagination();
}

// 3. Render trang hiện tại
function renderPage(page) {
  const start = (page - 1) * itemsPerPage;
  const pageItems = filteredProducts.slice(start, start + itemsPerPage);
  renderProducts(pageItems);
}

// 4. Render danh sách sản phẩm
function renderProducts(products) {
  const container = document.getElementById('products-container');
  container.innerHTML = '';
  
  if (products.length === 0) {
    container.innerHTML = '<div class="col-12"><div class="alert alert-info">Không tìm thấy sản phẩm phù hợp</div></div>';
    return;
  }
  
  products.forEach(product => {
    const card = document.createElement('div');
    
    if (viewMode === 'grid') {
      card.className = 'col-md-6 col-lg-4';
    } else {
      card.className = 'col-12 mb-3';
    }
    
    card.innerHTML = `
      <div class="product-card" data-id="${product.id}">
        <a href="product_detail.php?id=${product.id}" class="product-image">
          <img src="${product.image}" alt="${product.title}">
          <div class="product-wish">
            <i class="far fa-heart"></i>
          </div>
        </a>
        <div class="product-content">
          <div class="product-category">${product.category || 'Smartphone'}</div>
          <a href="product_detail.php?id=${product.id}" class="product-title-link">
            <h3 class="product-title">${product.title}</h3>
          </a>
          <div class="product-price">${formatPrice(product.price)}</div>
          <button class="btn-add-cart">
            <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
          </button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
  
  // Cập nhật view mode
  if (viewMode === 'list') {
    container.classList.add('list-view');
  } else {
    container.classList.remove('list-view');
  }
}

// 5. Format giá
function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

// 6. Render phân trang
function renderPagination() {
  const paginationContainer = document.getElementById('pagination-container');
  paginationContainer.innerHTML = '';
  
  if (totalPages <= 1) return;
  
  const ul = document.createElement('ul');
  ul.className = 'pagination';
  
  // Nút Previous
  const prevLi = document.createElement('li');
  prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
  prevLi.innerHTML = `<a class="page-link" href="#" ${currentPage === 1 ? 'tabindex="-1" aria-disabled="true"' : ''}>
    <i class="fas fa-chevron-left"></i>
  </a>`;
  ul.appendChild(prevLi);
  
  // Các trang
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);
  
  if (endPage - startPage < 4) {
    startPage = Math.max(1, endPage - 4);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const li = document.createElement('li');
    li.className = `page-item ${i === currentPage ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
    ul.appendChild(li);
  }
  
  // Nút Next
  const nextLi = document.createElement('li');
  nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
  nextLi.innerHTML = `<a class="page-link" href="#" ${currentPage === totalPages ? 'tabindex="-1" aria-disabled="true"' : ''}>
    <i class="fas fa-chevron-right"></i>
  </a>`;
  ul.appendChild(nextLi);
  
  paginationContainer.appendChild(ul);
}

// 8. Xử lý các event listeners
function setupEventListeners() {
  // Xử lý tìm kiếm
  const searchInput = document.getElementById('searchInput');
  let searchTimeout;
  
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      searchQuery = this.value.trim();
      currentPage = 1;
      updateUrlParams();
      applyFilters();
    }, 300); // Delay 300ms để tránh gọi quá nhiều lần
  });

  // Xử lý click phân trang
  document.getElementById('pagination-container').addEventListener('click', function(e) {
    e.preventDefault();
    if (e.target.tagName === 'A' || e.target.parentElement.tagName === 'A') {
      const pageItem = e.target.closest('.page-item');
      if (pageItem.classList.contains('disabled')) return;
      
      if (pageItem.textContent.trim() === '') {
        // Previous or Next button
        if (e.target.classList.contains('fa-chevron-left') || e.target.closest('a').querySelector('.fa-chevron-left')) {
          currentPage--;
        } else {
          currentPage++;
        }
      } else {
        currentPage = parseInt(pageItem.textContent);
      }
      
      renderPage(currentPage);
      renderPagination();
      window.scrollTo(0, 0);
    }
  });
  
  // Xử lý filter category
  document.querySelectorAll('.category-filter').forEach(radio => {
    radio.addEventListener('change', function() {
      currentCategory = this.value;
      currentPage = 1;
      updateUrlParams();
      applyFilters();
    });
  });
  
  // Xử lý filter price
  document.querySelectorAll('.price-filter').forEach(radio => {
    radio.addEventListener('change', function() {
      currentPriceRange = this.value;
      currentPage = 1;
      updateUrlParams();
      applyFilters();
    });
  });
  
  // Xử lý sort
  document.querySelectorAll('.sort-filter').forEach(radio => {
    radio.addEventListener('change', function() {
      currentSort = this.value;
      currentPage = 1;
      updateUrlParams();
      applyFilters();
    });
  });
  
  // Xử lý view mode
  document.querySelector('.view-grid').addEventListener('click', function() {
    viewMode = 'grid';
    document.querySelector('.view-list').classList.remove('active');
    this.classList.add('active');
    renderPage(currentPage);
  });
  
  document.querySelector('.view-list').addEventListener('click', function() {
    viewMode = 'list';
    document.querySelector('.view-grid').classList.remove('active');
    this.classList.add('active');
    renderPage(currentPage);
  });
  
  // Xử lý thêm vào giỏ hàng
  document.getElementById('products-container').addEventListener('click', async function(e) {
    if (e.target.classList.contains('btn-add-cart') || e.target.closest('.btn-add-cart')) {
      e.preventDefault();
      e.stopPropagation();
      
      if (!isLoggedIn) {
        window.location.href = 'login.php';
        return;
      }
      
      const productCard = e.target.closest('.product-card');
      const productId = productCard.dataset.id;
      
      try {
        const response = await fetch('add_to_cart.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({productId})
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Hiển thị toast thông báo
          const toast = new bootstrap.Toast(document.getElementById('cartToast'));
          toast.show();
        } else {
          alert('Lỗi: ' + (data.error || 'Không thể thêm vào giỏ hàng'));
        }
      } catch (err) {
        console.error(err);
        alert('Lỗi kết nối server');
      }
    }
  });
}

// 9. Cập nhật URL params
function updateUrlParams() {
  const params = new URLSearchParams(window.location.search);
  
  if (currentCategory) {
    params.set('category', currentCategory);
  } else {
    params.delete('category');
  }
  
  if (currentPriceRange) {
    params.set('price', currentPriceRange);
  } else {
    params.delete('price');
  }
  
  if (currentSort !== 'default') {
    params.set('sort', currentSort);
  } else {
    params.delete('sort');
  }

  if (searchQuery) {
    params.set('search', searchQuery);
  } else {
    params.delete('search');
  }
  
  params.set('page', currentPage);
  
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  history.pushState({}, '', newUrl);
}

// 10. Set active filters from URL
function setActiveFilters() {
  // Set search query from URL
  const urlParams = new URLSearchParams(window.location.search);
  const searchParam = urlParams.get('search');
  if (searchParam) {
    searchQuery = searchParam;
    document.getElementById('searchInput').value = searchParam;
  }

  // Set category filter
  if (currentCategory) {
    const catRadio = document.getElementById(`cat-${currentCategory}`);
    if (catRadio) {
      catRadio.checked = true;
    }
  }
  
  // Set price filter
  if (currentPriceRange) {
    const priceInputs = document.querySelectorAll('.price-filter');
    for (const input of priceInputs) {
      if (input.value === currentPriceRange) {
        input.checked = true;
        break;
      }
    }
  }
  
  // Set sort filter
  if (currentSort) {
    const sortRadio = document.getElementById(`sort-${currentSort}`);
    if (sortRadio) {
      sortRadio.checked = true;
    }
  }
}

// Khởi chạy
document.addEventListener('DOMContentLoaded', function() {
  loadProducts();
  setupEventListeners();
});
</script>