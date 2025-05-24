<?php
require_once '../includes/header.php';

// Get product ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /ecommerce-platform/products/index.php');
    exit;
}

$product_id = (int)$_GET['id'];
$product = getProductById($product_id);

// Check if product exists
if (!$product) {
    $_SESSION['error_message'] = "Product not found.";
    header('Location: /ecommerce-platform/products/index.php');
    exit;
}

// Add to cart form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Redirect to login with return URL
        $redirect = urlencode('/ecommerce-platform/products/view_product.php?id=' . $product_id);
        header('Location: /ecommerce-platform/auth/login.php?redirect=' . $redirect);
        exit;
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0 && $quantity <= $product['quantity']) {
            // Add to cart
            if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
                $_SESSION['success_message'] = "Product added to cart.";
                header('Location: /ecommerce-platform/products/view_product.php?id=' . $product_id);
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to add product to cart.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid quantity.";
        }
    }
}

// Related products
$related_products = getProductsByCategory($product['category_id']);
// Remove current product from related products
$related_products = array_filter($related_products, function($item) use ($product_id) {
    return $item['id'] != $product_id;
});
// Limit to 4 products
$related_products = array_slice($related_products, 0, 4);
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ecommerce-platform/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="/ecommerce-platform/products/index.php">Products</a></li>
            <?php if ($product['category_id']): ?>
                <li class="breadcrumb-item">
                    <a href="/ecommerce-platform/products/index.php?category=<?= $product['category_id'] ?>">
                        <?= $product['category_name'] ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= $product['name'] ?></li>
        </ol>
    </nav>
    
    <!-- Product Details -->
    <div class="row mb-5">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <div class="card border-0">
                <img 
                    src="<?= $product['image'] ? $product['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                    class="product-detail-img" 
                    alt="<?= $product['name'] ?>"
                >
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-6">
            <h1 class="mb-3"><?= $product['name'] ?></h1>
            
            <div class="mb-3">
                <?php if ($product['discount_price']): ?>
                    <span class="product-detail-price"><?= formatCurrency($product['discount_price']) ?></span>
                    <span class="discount-price ms-2"><?= formatCurrency($product['price']) ?></span>
                <?php else: ?>
                    <span class="product-detail-price"><?= formatCurrency($product['price']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <p class="text-muted">
                    <strong>Category:</strong> 
                    <?php if ($product['category_id']): ?>
                        <a href="/ecommerce-platform/products/index.php?category=<?= $product['category_id'] ?>" class="text-decoration-none">
                            <?= $product['category_name'] ?>
                        </a>
                    <?php else: ?>
                        Uncategorized
                    <?php endif; ?>
                </p>
                
                <p class="text-muted">
                    <strong>Brand:</strong> 
                    <?= $product['brand_name'] ? $product['brand_name'] : 'N/A' ?>
                </p>
                
                <p class="text-muted">
                    <strong>Availability:</strong> 
                    <?php if ($product['quantity'] > 0): ?>
                        <span class="text-success">In Stock (<?= $product['quantity'] ?> items)</span>
                    <?php else: ?>
                        <span class="text-danger">Out of Stock</span>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?= $product['description'] ? nl2br($product['description']) : 'No description available.' ?></p>
            </div>
            
            <!-- Add to Cart Form -->
            <?php if ($product['quantity'] > 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group" style="width: 150px;">
                            <button type="button" class="btn btn-outline-secondary quantity-minus">-</button>
                            <input type="number" class="form-control text-center quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?= $product['quantity'] ?>">
                            <button type="button" class="btn btn-outline-secondary quantity-plus">+</button>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-ban me-2"></i> Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="mt-5">
            <h3 class="mb-4">Related Products</h3>
            <div class="row">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-6 col-md-3 mb-4">
                        <div class="card product-card h-100">
                            <img 
                                src="<?= $related['image'] ? $related['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                                class="card-img-top" 
                                alt="<?= $related['name'] ?>"
                            >
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $related['name'] ?></h5>
                                <div class="mb-2">
                                    <?php if ($related['discount_price']): ?>
                                        <span class="product-price"><?= formatCurrency($related['discount_price']) ?></span>
                                        <span class="discount-price ms-2"><?= formatCurrency($related['price']) ?></span>
                                    <?php else: ?>
                                        <span class="product-price"><?= formatCurrency($related['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-auto">
                                    <a href="/ecommerce-platform/products/view_product.php?id=<?= $related['id'] ?>" class="btn btn-primary w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>