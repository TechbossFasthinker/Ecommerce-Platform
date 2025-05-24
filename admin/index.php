<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get statistics
$productCount = getProductCount();
$orderCount = getOrderCount();
$userCount = getUserCount();
$totalRevenue = getTotalRevenue();
$recentOrders = getRecentOrders(5);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar admin-sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/ecommerce-platform/admin/index.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/products.php">
                            <i class="fas fa-box me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/categories.php">
                            <i class="fas fa-tags me-2"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/brands.php">
                            <i class="fas fa-copyright me-2"></i> Brands
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/orders.php">
                            <i class="fas fa-shopping-cart me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/users.php">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/ecommerce-platform/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="/ecommerce-platform/index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-home me-1"></i> View Site
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card h-100 py-2 bg-primary text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <i class="fas fa-box fa-3x"></i>
                                </div>
                                <div class="col-9 text-end">
                                    <div class="h5 mb-0 font-weight-bold"><?= $productCount ?></div>
                                    <div class="text-white-50">Products</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a href="/ecommerce-platform/admin/products.php" class="text-white text-decoration-none">View Details</a>
                            <i class="fas fa-angle-right text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card h-100 py-2 bg-success text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <i class="fas fa-shopping-cart fa-3x"></i>
                                </div>
                                <div class="col-9 text-end">
                                    <div class="h5 mb-0 font-weight-bold"><?= $orderCount ?></div>
                                    <div class="text-white-50">Orders</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a href="/ecommerce-platform/admin/orders.php" class="text-white text-decoration-none">View Details</a>
                            <i class="fas fa-angle-right text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card h-100 py-2 bg-info text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <div class="col-9 text-end">
                                    <div class="h5 mb-0 font-weight-bold"><?= $userCount ?></div>
                                    <div class="text-white-50">Customers</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a href="/ecommerce-platform/admin/users.php" class="text-white text-decoration-none">View Details</a>
                            <i class="fas fa-angle-right text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card h-100 py-2 bg-warning text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <i class="fas fa-dollar-sign fa-3x"></i>
                                </div>
                                <div class="col-9 text-end">
                                    <div class="h5 mb-0 font-weight-bold"><?= formatCurrency($totalRevenue) ?></div>
                                    <div class="text-white-50">Revenue</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a href="/ecommerce-platform/admin/orders.php" class="text-white text-decoration-none">View Details</a>
                            <i class="fas fa-angle-right text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="/ecommerce-platform/admin/orders.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-center py-3 mb-0">No orders found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Order Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td><?= getStatusBadge($order['payment_status'], 'payment') ?></td>
                                            <td><?= getStatusBadge($order['order_status'], 'order') ?></td>
                                            <td>
                                                <a href="/ecommerce-platform/admin/view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <a href="/ecommerce-platform/admin/products.php?action=add" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-plus-circle mb-2 fa-2x"></i>
                                        <div>Add Product</div>
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="/ecommerce-platform/admin/categories.php?action=add" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-folder-plus mb-2 fa-2x"></i>
                                        <div>Add Category</div>
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="/ecommerce-platform/admin/brands.php?action=add" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-tag mb-2 fa-2x"></i>
                                        <div>Add Brand</div>
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="/ecommerce-platform/admin/orders.php" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-clipboard-list mb-2 fa-2x"></i>
                                        <div>View Orders</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">System Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                            <p><strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?></p>
                            <p><strong>Database:</strong> MySQL</p>
                            <p><strong>Server Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                            <p class="mb-0"><strong>Admin:</strong> <?= $_SESSION['user_name'] . ' (' . $_SESSION['user_email'] . ')' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>