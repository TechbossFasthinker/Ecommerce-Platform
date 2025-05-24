<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get order ID from URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: /ecommerce-platform/users/orders.php');
    exit;
}

$order_id = (int)$_GET['order_id'];
$order = getOrderDetails($order_id, $_SESSION['user_id']);

// Check if order exists and belongs to user
if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header('Location: /ecommerce-platform/users/orders.php');
    exit;
}

// Process payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        // Update payment status to completed (simulate payment)
        if (updatePaymentStatus($order_id, 'completed')) {
            $_SESSION['success_message'] = "Payment confirmed successfully!";
            header('Location: /ecommerce-platform/users/orders.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to confirm payment. Please try again.";
        }
    }
}
?>

<div class="container">
    <h1 class="mb-4">Order Confirmation</h1>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Order Placed Successfully!</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        <h4 class="mt-3">Thank you for your order!</h4>
                        <p class="lead">Your order has been placed and is being processed.</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Order Details</h5>
                        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                        <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                        <p><strong>Total Amount:</strong> <?= formatCurrency($order['total_amount']) ?></p>
                        <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                        <p><strong>Status:</strong> <?= getStatusBadge($order['order_status'], 'order') ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Items Ordered</h5>
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
                                            <td><?= $item['name'] ?></td>
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
                    
                    <div class="mb-4">
                        <h5>Shipping Address</h5>
                        <p><?= nl2br($order['shipping_address']) ?></p>
                    </div>
                    
                    <?php if ($order['payment_method'] !== 'cash_on_delivery' && $order['payment_status'] === 'pending'): ?>
                        <div class="mb-4">
                            <h5>Payment Information</h5>
                            <div class="alert alert-warning">
                                Your payment is pending. Please confirm your payment to complete your order.
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <button type="submit" name="confirm_payment" class="btn btn-success">
                                    Confirm Payment
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="/ecommerce-platform/users/orders.php" class="btn btn-primary me-2">
                            View My Orders
                        </a>
                        <a href="/ecommerce-platform/products/index.php" class="btn btn-outline-primary">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>