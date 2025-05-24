<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get action from URL
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['update_order_status'])) {
            // Update order status
            $order_id = (int)$_POST['order_id'];
            $order_status = sanitize($_POST['order_status']);
            
            if (updateOrderStatus($order_id, $order_status)) {
                $_SESSION['success_message'] = "Order status updated successfully.";
                header('Location: /ecommerce-platform/admin/view_order.php?id=' . $order_id);
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update order status.";
            }
        } elseif (isset($_POST['update_payment_status'])) {
            // Update payment status
            $order_id = (int)$_POST['order_id'];
            $payment_status = sanitize($_POST['payment_status']);
            
            if (updatePaymentStatus($order_id, $payment_status)) {
                $_SESSION['success_message'] = "Payment status updated successfully.";
                header('Location: /ecommerce-platform/admin/view_order.php?id=' . $order_id);
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update payment status.";
            }
        }
    }
}

// Get all orders for listing
$orders = getAllOrders();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar admin-sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce-platform/admin/index.php">
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
                        <a class="nav-link active" href="/ecommerce-platform/admin/orders.php">
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
                <h1 class="h2">Manage Orders</h1>
            </div>
            
            <!-- Orders List -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">All Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
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
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Order Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= formatCurrency($order['total_amount']) ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
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
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>