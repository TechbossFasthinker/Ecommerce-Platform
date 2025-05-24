<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get user information
$user = getUserInfo($_SESSION['user_id']);

// Get recent orders
$orders = getUserOrders($_SESSION['user_id']);
$recentOrders = array_slice($orders, 0, 5);
?>

<div class="container">
    <h1 class="mb-4">My Account</h1>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="list-group">
                <a href="/ecommerce-platform/users/dashboard.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="/ecommerce-platform/users/orders.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-bag me-2"></i> My Orders
                </a>
                <a href="/ecommerce-platform/users/update_profile.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-edit me-2"></i> Edit Profile
                </a>
                <a href="/ecommerce-platform/cart/view_cart.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-cart me-2"></i> My Cart
                </a>
                <a href="/ecommerce-platform/auth/logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Welcome, <?= $user['first_name'] ?>!</h4>
                    <p class="card-text">From your account dashboard, you can view your recent orders, manage your profile, and more.</p>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= $user['first_name'] . ' ' . $user['last_name'] ?></p>
                            <p><strong>Email:</strong> <?= $user['email'] ?></p>
                            <p><strong>Account Type:</strong> <?= ucfirst($user['role']) ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="/ecommerce-platform/users/update_profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="/ecommerce-platform/users/orders.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="text-center py-3">
                            <p class="mb-0">You haven't placed any orders yet.</p>
                            <a href="/ecommerce-platform/products/index.php" class="btn btn-primary mt-3">
                                Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td><?= getStatusBadge($order['order_status'], 'order') ?></td>
                                            <td>
                                                <a href="/ecommerce-platform/users/view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
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
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>