<?php include 'templates/header.php'; ?>

<main>
  <!-- Hero Banner Section -->
  <section class="hero-section">
    <div class="hero-background">
      <div class="hero-overlay"></div>
      <div class="container">
        <div class="hero-content">
          <span class="hero-subtitle">Khuyến mãi đặc biệt</span>
          <h1 class="hero-title">Smartphone chính hãng với giá tốt nhất</h1>
          <p class="hero-text">Sở hữu những chiếc điện thoại chất lượng cao với giá cả hợp lý, cùng chế độ bảo hành uy tín tại S-Phone.</p>
          <div class="hero-buttons">
            <a href="products.php" class="btn btn-primary btn-lg">Mua ngay</a>
            <a href="products.php" class="btn btn-outline-light btn-lg">Xem sản phẩm</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="categories-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Danh mục sản phẩm</h2>
        <p class="section-subtitle">Lựa chọn theo nhu cầu của bạn</p>
      </div>
      
      <div class="row g-4">
        <div class="col-6 col-md-3">
          <a href="products.php?category=iphone" class="category-card">
            <div class="category-icon">
              <img src="assets/images/icon-iphone.png" alt="iPhone">
            </div>
            <h3 class="category-title">iPhone</h3>
            <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="products.php?category=samsung" class="category-card">
            <div class="category-icon">
              <img src="assets/images/icon-samsung.png" alt="Samsung">
            </div>
            <h3 class="category-title">Samsung</h3>
            <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="products.php?category=xiaomi" class="category-card">
            <div class="category-icon">
              <img src="assets/images/icon-xiaomi.png" alt="Xiaomi">
            </div>
            <h3 class="category-title">Xiaomi</h3>
            <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="products.php?category=other" class="category-card">
            <div class="category-icon">
              <img src="assets/images/icon-other.png" alt="Khác">
            </div>
            <h3 class="category-title">Thương hiệu khác</h3>
            <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Products Section -->
  <section class="featured-section" id="featured">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Sản phẩm nổi bật</h2>
        <p class="section-subtitle">Được nhiều khách hàng lựa chọn</p>
      </div>
      
      <div class="row g-4" id="featured-products">
        <!-- Sản phẩm nổi bật sẽ được load bằng JavaScript -->
      </div>
      
      <div class="text-center mt-5">
        <a href="products.php" class="btn btn-outline-primary btn-lg">Xem tất cả sản phẩm</a>
      </div>
    </div>
  </section>
</main>

<?php include 'templates/footer.php'; ?>

<style>
/* Base Styles */
:root {
  --primary-color: #F86338;
  --secondary-color: #9A9AB0;
  --dark-color: #1A1A1A;
  --light-color: #F5F5F5;
  --accent-color: #3498db;
  --gray-color: #7A7A7A;
  --white-color: #FFFFFF;
  --heading-font: 'Inter', sans-serif;
  --body-font: 'Inter', sans-serif;
}

body {
  font-family: var(--body-font);
  color: var(--dark-color);
  line-height: 1.6;
  background-color: var(--light-color);
}

h1, h2, h3, h4, h5, h6 {
  font-family: var(--heading-font);
  font-weight: 700;
  color: var(--dark-color);
}

.btn-primary {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  color: var(--white-color);
  font-weight: 500;
  padding: 10px 25px;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.btn-primary:hover,
.btn-primary:focus {
  background-color: #e84d1f;
  border-color: #e84d1f;
  color: var(--white-color);
}

.btn-outline-primary {
  border-color: var(--primary-color);
  color: var(--primary-color);
  font-weight: 500;
  padding: 10px 25px;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover,
.btn-outline-primary:focus {
  background-color: var(--primary-color);
  color: var(--white-color);
}

.btn-outline-light {
  border-color: var(--white-color);
  color: var(--white-color);
  font-weight: 500;
  padding: 10px 25px;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.btn-outline-light:hover,
.btn-outline-light:focus {
  background-color: var(--white-color);
  color: var(--dark-color);
}

/* Section Styles */
section {
  padding: 80px 0;
}

.section-header {
  text-align: center;
  margin-bottom: 50px;
}

.section-title {
  font-size: 32px;
  margin-bottom: 10px;
  position: relative;
}

.section-title::after {
  content: '';
  display: block;
  width: 50px;
  height: 3px;
  background-color: var(--primary-color);
  margin: 20px auto 0;
}

.section-subtitle {
  font-size: 16px;
  color: var(--gray-color);
}

/* Hero Section */
.hero-section {
  position: relative;
  height: 600px; /* Chiều cao cố định cho hero section */
  overflow: hidden;
}

.hero-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url('assets/images/hero-banner.jpg'); /* Đường dẫn tới hình ảnh banner */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to right, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.1) 100%);
}

.hero-content {
  position: relative;
  z-index: 10;
  max-width: 600px;
  padding-top: 120px;
  color: #fff;
}

.hero-subtitle {
  display: inline-block;
  background-color: rgba(248, 99, 56, 0.8);
  color: white;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 20px;
}

.hero-title {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 20px;
  color: #fff;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-text {
  font-size: 18px;
  margin-bottom: 30px;
  max-width: 90%;
  color: rgba(255, 255, 255, 0.9);
}

.hero-buttons {
  display: flex;
  gap: 15px;
}

/* Categories Section */
.categories-section {
  background-color: var(--white-color);
}

.category-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  border-radius: 10px;
  background-color: var(--white-color);
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  text-decoration: none;
  height: 100%;
  position: relative;
}

.category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.category-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #FFECE8;
  border-radius: 50%;
  margin-bottom: 20px;
}

.category-icon img {
  max-width: 50%;
  height: auto;
}

.category-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--dark-color);
  margin-bottom: 0;
  text-align: center;
}

.category-arrow {
  position: absolute;
  bottom: 15px;
  right: 15px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #FFECE8;
  border-radius: 50%;
  color: var(--primary-color);
  transition: all 0.3s ease;
  opacity: 0;
}

.category-card:hover .category-arrow {
  opacity: 1;
}

/* Featured Products Section */
.featured-section {
  background-color: var(--light-color);
}

.product-card {
  background-color: var(--white-color);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.product-image {
  position: relative;
  height: 200px;
  display: block;
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
  background: var(--primary-color);
  color: var(--white-color);
}

.product-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-category {
  font-size: 12px;
  color: var(--gray-color);
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
  color: var(--dark-color);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 38px;
  margin-bottom: 0;
  transition: color 0.3s ease;
}

.product-title-link:hover .product-title {
  color: var(--primary-color);
}

.product-rating {
  color: #FFBD10;
  font-size: 14px;
  margin-bottom: 10px;
}

.product-price {
  font-weight: 700;
  font-size: 18px;
  color: var(--primary-color);
  margin-top: auto;
  margin-bottom: 15px;
}

.btn-add-cart {
  background-color: var(--primary-color);
  color: var(--white-color);
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  font-weight: 500;
  transition: all 0.3s ease;
  width: 100%;
  cursor: pointer;
}

.btn-add-cart:hover {
  background-color: #e84d1f;
}

/* Responsive Styles */
@media (max-width: 991.98px) {
  .hero-section {
    height: 500px;
  }
  
  .hero-content {
    padding-top: 100px;
    text-align: center;
    margin: 0 auto;
  }
  
  .hero-title {
    font-size: 36px;
  }
  
  .hero-text {
    max-width: 100%;
  }
  
  .hero-buttons {
    justify-content: center;
  }
}

@media (max-width: 767.98px) {
  section {
    padding: 50px 0;
  }
  
  .hero-section {
    height: 450px;
  }
  
  .hero-content {
    padding-top: 80px;
  }
  
  .hero-title {
    font-size: 28px;
  }
  
  .hero-text {
    font-size: 16px;
  }
  
  .hero-buttons {
    flex-direction: column;
    gap: 10px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Load featured products
  loadFeaturedProducts();
});

// Tải sản phẩm nổi bật
async function loadFeaturedProducts() {
  try {
    const response = await fetch('products_api.php');
    if (!response.ok) throw new Error('Không thể tải dữ liệu sản phẩm');
    
    const products = await response.json();
    
    // Lấy 8 sản phẩm đầu tiên làm sản phẩm nổi bật
    const featuredProducts = products.slice(0, 8);
    
    // Render sản phẩm nổi bật
    renderFeaturedProducts(featuredProducts);
  } catch (error) {
    console.error('Error loading featured products:', error);
    document.getElementById('featured-products').innerHTML = 
      '<div class="col-12"><div class="alert alert-danger">Không thể tải sản phẩm nổi bật. Vui lòng thử lại sau.</div></div>';
  }
}

// Render danh sách sản phẩm nổi bật
function renderFeaturedProducts(products) {
  const container = document.getElementById('featured-products');
  container.innerHTML = '';
  
  products.forEach(product => {
    // Create category if not present
    const category = product.category || getCategoryFromTitle(product.title);
    
    const productHtml = `
      <div class="col-md-6 col-lg-3">
        <div class="product-card">
          <a href="product_detail.php?id=${product.id}" class="product-image">
            <img src="${product.image}" alt="${product.title}">
            <div class="product-wish">
              <i class="far fa-heart"></i>
            </div>
          </a>
          <div class="product-content">
            <div class="product-category">${category}</div>
            <a href="product_detail.php?id=${product.id}" class="product-title-link">
              <h3 class="product-title">${product.title}</h3>
            </a>
            <div class="product-price">${formatPrice(product.price)}</div>
            <button class="btn-add-cart" data-id="${product.id}">
              <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
            </button>
          </div>
        </div>
      </div>
    `;
    
    container.innerHTML += productHtml;
  });
  
  // Thêm sự kiện click cho nút "Thêm vào giỏ"
  document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', addToCart);
  });
}

// Format giá
function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

// Xác định category dựa vào title
function getCategoryFromTitle(title) {
  title = title.toLowerCase();
  if (title.includes('iphone')) return 'iPhone';
  if (title.includes('samsung')) return 'Samsung';
  if (title.includes('xiaomi')) return 'Xiaomi';
  return 'Smartphone';
}

// Thêm vào giỏ hàng
async function addToCart(e) {
  e.preventDefault();
  e.stopPropagation();
  
  const productId = this.getAttribute('data-id');
  const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
  
  if (!isLoggedIn) {
    window.location.href = 'login.php';
    return;
  }
  
  try {
    const response = await fetch('add_to_cart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({productId})
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Show success message
      alert('Đã thêm sản phẩm vào giỏ hàng');
    } else {
      alert('Lỗi: ' + (result.error || 'Không thể thêm vào giỏ hàng'));
    }
  } catch (error) {
    console.error('Error adding to cart:', error);
    alert('Lỗi kết nối server');
  }
}
</script>