<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get order ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /ecommerce-platform/users/orders.php');
    exit;
}

$order_id = (int)$_GET['id'];
$order = getOrderDetails($order_id, $_SESSION['user_id']);

// Check if order exists and belongs to user
if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header('Location: /ecommerce-platform/users/orders.php');
    exit;
}
?>

<div class="container">
    <h1 class="mb-4">Order #<?= $order['id'] ?></h1>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="list-group">
                <a href="/ecommerce-platform/users/dashboard.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="/ecommerce-platform/users/orders.php" class="list-group-item list-group-item-action active">
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
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                            <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                            <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                        </div>
                        <div class="col-md-6">
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
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br($order['shipping_address']) ?></p>
                </div>
            </div>
            
            <?php if ($order['payment_method'] !== 'cash_on_delivery' && $order['payment_status'] === 'pending'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Payment Pending</h5>
                    </div>
                    <div class="card-body">
                        <p>Your payment is pending. Please complete your payment to process your order.</p>
                        <a href="/ecommerce-platform/cart/payment.php?order_id=<?= $order['id'] ?>" class="btn btn-warning">
                            Complete Payment
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-end">
                <a href="/ecommerce-platform/users/orders.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>