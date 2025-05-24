<?php
require_once 'includes/header.php';

// Initialize database if needed
initializeDatabase();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Welcome to ShopHub</h1>
        <p class="lead mb-4">Discover amazing products at affordable prices. Shop now!</p>
        <a href="products/index.php" class="btn btn-light btn-lg px-4 me-md-2">Browse Products</a>
    </div>
</section>

<!-- Featured Products -->
<section class="mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Featured Products</h2>
            <a href="products/index.php" class="btn btn-outline-primary">View All</a>
        </div>
        <div class="row">
            <?php
            $featuredProducts = getAllProducts(8, true);
            
            if (empty($featuredProducts)) {
                $featuredProducts = getAllProducts(8);
            }
            
            foreach ($featuredProducts as $product) {
                ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <div class="card product-card h-100">
                        <img 
                            src="<?= $product['image'] ? $product['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                            class="card-img-top" 
                            alt="<?= $product['name'] ?>"
                        >
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <div class="mb-2">
                                <?php if ($product['discount_price']): ?>
                                    <span class="product-price"><?= formatCurrency($product['discount_price']) ?></span>
                                    <span class="discount-price ms-2"><?= formatCurrency($product['price']) ?></span>
                                <?php else: ?>
                                    <span class="product-price"><?= formatCurrency($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto">
                                <a href="products/view_product.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="mb-5">
    <div class="container">
        <h2 class="section-title mb-4">Shop by Category</h2>
        <div class="row">
            <?php
            $categories = getAllCategories();
            foreach ($categories as $category) {
                $categoryImage = 'images/categories/' . strtolower(str_replace(' & ', '-', str_replace(' ', '-', $category['name']))) . '.jpg';
                $defaultImage = 'https://images.pexels.com/photos/5632402/pexels-photo-5632402.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1';
                
                // Check if category image exists
                $imagePath = __DIR__ . '/' . $categoryImage;
                $imageUrl = file_exists($imagePath) ? $categoryImage : $defaultImage;
                ?>
                <div class="col-6 col-md-4">
                    <a href="products/index.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                        <div class="category-card">
                            <img src="<?= $imageUrl ?>" alt="<?= $category['name'] ?>">
                            <div class="overlay">
                                <div class="category-name"><?= $category['name'] ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="mb-5 py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Why Choose Us</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon text-primary mb-3">
                            <i class="fas fa-shipping-fast fa-3x"></i>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p class="text-muted">Quick and reliable shipping to your doorstep.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon text-primary mb-3">
                            <i class="fas fa-medal fa-3x"></i>
                        </div>
                        <h4>Quality Products</h4>
                        <p class="text-muted">All products are carefully sourced and checked for quality.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon text-primary mb-3">
                            <i class="fas fa-headset fa-3x"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Our customer service team is always ready to help.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5 text-center">
                        <h3>Subscribe to Our Newsletter</h3>
                        <p class="text-muted mb-4">Get updates on new products and special offers.</p>
                        <form class="row g-3 justify-content-center">
                            <div class="col-md-8">
                                <input type="email" class="form-control" placeholder="Your Email Address" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>