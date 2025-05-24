<?php
// Start output buffering to prevent header issues
ob_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/ecommerce-platform/includes/functions.php';

// Get categories for dropdown (only if needed)
$categories = [];
if (isLoggedIn() || true) { // Always load categories for navigation
    $categories = getAllCategories();
}

// Get cart count for logged-in users
$cartCount = 0;
if (isLoggedIn()) {
    $cartItems = getCartItems($_SESSION['user_id']);
    $cartCount = count($cartItems);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Platform</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/ecommerce-platform/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="/ecommerce-platform/index.php">
                    <i class="fas fa-shopping-cart me-2"></i>ShopHub
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/ecommerce-platform/index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <li><a class="dropdown-item" href="/ecommerce-platform/products/index.php?category=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><span class="dropdown-item text-muted">No categories available</span></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex me-3" action="/ecommerce-platform/products/index.php" method="GET">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                    </form>
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/ecommerce-platform/cart/view_cart.php">
                                    <i class="fas fa-shopping-cart"></i> Cart
                                    <?php if ($cartCount > 0): ?>
                                        <span class="badge bg-danger"><?= $cartCount ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle"></i> 
                                    <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User' ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <?php if (isAdmin()): ?>
                                        <li><a class="dropdown-item" href="/ecommerce-platform/admin/index.php">Admin Dashboard</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item" href="/ecommerce-platform/users/dashboard.php">My Account</a></li>
                                        <li><a class="dropdown-item" href="/ecommerce-platform/users/orders.php">My Orders</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="/ecommerce-platform/auth/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/ecommerce-platform/auth/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/ecommerce-platform/auth/register.php">Register</a>
            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <!-- Main Content -->
    <main class="container my-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
<?php
// End output buffering and flush
ob_end_flush();
?>