<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get order ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /ecommerce-platform/admin/orders.php');
    exit;
}

$order_id = (int)$_GET['id'];
$order = getOrderDetails($order_id);

// Check if order exists
if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header('Location: /ecommerce-platform/admin/orders.php');
    exit;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['update_order_status'])) {
            // Update order status
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
                <h1 class="h2">Order #<?= $order['id'] ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/ecommerce-platform/admin/orders.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Order Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                                    <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                                    <p><strong>Customer:</strong> <?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
                                    <p><strong>Email:</strong> <?= $order['email'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                    <p><strong>Payment Status:</strong> <?= getStatusBadge($order['payment_status'], 'payment') ?></p>
                                    <p><strong>Order Status:</strong> <?= getStatusBadge($order['order_status'], 'order') ?></p>
                                    <p><strong>Total Amount:</strong> <?= formatCurrency($order['total_amount']) ?></p>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Items Ordered</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img 
                                                            src="<?= $item['image'] ? $item['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                                                            class="me-3" 
                                                            alt="<?= $item['name'] ?>"
                                                            style="width: 50px; height: 50px; object-fit: cover;"
                                                        >
                                                        <div>
                                                            <?= $item['name'] ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= formatCurrency($item['price']) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td><?= formatCurrency($item['price'] * $item['quantity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong><?= formatCurrency($order['total_amount']) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Shipping Address</h5>
                        </div>
                        <div class="card-body">
                            <p><?= nl2br($order['shipping_address']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Update Order Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Update Order Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                
                                <div class="mb-3">
                                    <label for="order_status" class="form-label">Order Status</label>
                                    <select class="form-select" id="order_status" name="order_status" required>
                                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_order_status" class="btn btn-primary">
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Update Payment Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Update Payment Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Payment Status</label>
                                    <select class="form-select" id="payment_status" name="payment_status" required>
                                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= $order['payment_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_payment_status" class="btn btn-primary">
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>