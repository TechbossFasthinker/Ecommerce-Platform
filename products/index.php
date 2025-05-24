<?php
require_once '../includes/header.php';

// Get all products or filter by category
$products = [];
$title = 'All Products';
$breadcrumb = 'Products';

if (isset($_GET['category'])) {
    $category_id = (int)$_GET['category'];
    $category = getCategoryById($category_id);
    
    if ($category) {
        $products = getProductsByCategory($category_id);
        $title = $category['name'];
        $breadcrumb = 'Products / ' . $category['name'];
    } else {
        $products = getAllProducts();
    }
} elseif (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $products = searchProducts($search);
    $title = 'Search Results for "' . $search . '"';
    $breadcrumb = 'Products / Search Results';
} else {
    $products = getAllProducts();
}
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ecommerce-platform/index.php">Home</a></li>
            <?php if (isset($_GET['category'])): ?>
                <li class="breadcrumb-item"><a href="/ecommerce-platform/products/index.php">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $category['name'] ?></li>
            <?php elseif (isset($_GET['search'])): ?>
                <li class="breadcrumb-item"><a href="/ecommerce-platform/products/index.php">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search Results</li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            <?php endif; ?>
        </ol>
    </nav>
    
    <h1 class="mb-4"><?= $title ?></h1>
    
    <?php if (isset($_GET['search'])): ?>
        <p>Showing results for: <strong><?= $search ?></strong></p>
    <?php endif; ?>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <!-- Categories -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php
                        $categories = getAllCategories();
                        foreach ($categories as $cat) {
                            $active = isset($_GET['category']) && $_GET['category'] == $cat['id'] ? 'active' : '';
                            echo '<li class="list-group-item ' . $active . '">
                                    <a href="/ecommerce-platform/products/index.php?category=' . $cat['id'] . '" class="text-decoration-none d-block">
                                        ' . $cat['name'] . '
                                    </a>
                                  </li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            
            <!-- Brands -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Brands</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php
                        $brands = getAllBrands();
                        foreach ($brands as $brand) {
                            echo '<li class="list-group-item">
                                    <a href="#" class="text-decoration-none d-block">
                                        ' . $brand['name'] . '
                                    </a>
                                  </li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4 mb-4">
                            <div class="card product-card h-100">
                                <img 
                                    src="<?= $product['image'] ? $product['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                                    class="card-img-top" 
                                    alt="<?= $product['name'] ?>"
                                >
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= $product['name'] ?></h5>
                                    <p class="card-text text-muted small">
                                        <?= $product['category_name'] ? $product['category_name'] : 'Uncategorized' ?>
                                    </p>
                                    <div class="mb-2">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="product-price"><?= formatCurrency($product['discount_price']) ?></span>
                                            <span class="discount-price ms-2"><?= formatCurrency($product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?= formatCurrency($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="/ecommerce-platform/products/view_product.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>