<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

// Process remove item action
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    // Validate CSRF token
    if (isset($_GET['csrf_token']) && validateCSRFToken($_GET['csrf_token'])) {
        $cart_id = (int)$_GET['remove'];
        
        if (removeFromCart($cart_id, $_SESSION['user_id'])) {
            $_SESSION['success_message'] = "Item removed from cart.";
        } else {
            $_SESSION['error_message'] = "Failed to remove item from cart.";
        }
        
        // Redirect to clear GET parameters
        header('Location: /ecommerce-platform/cart/view_cart.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    }
}

// Process update quantity action via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update_cart'])) {
    header('Content-Type: application/json');
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Security validation failed']);
        exit;
    }
    
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
        exit;
    }
    
    if (updateCartQuantity($cart_id, $_SESSION['user_id'], $quantity)) {
        // Get updated item and total
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT c.quantity, p.price, p.discount_price 
                               FROM cart c 
                               JOIN products p ON c.product_id = p.id 
                               WHERE c.id = ? AND c.user_id = ?");
        $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        
        // Calculate subtotal
        $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
        $subtotal = $price * $item['quantity'];
        
        // Get new cart total
        $total = getCartTotal($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true, 
            'subtotal' => formatCurrency($subtotal),
            'total' => formatCurrency($total)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
    exit;
}
?>

<div class="container">
    <h1 class="mb-4">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="/ecommerce-platform/products/index.php" class="alert-link">Continue shopping</a>.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Cart Items (<?= count($cartItems) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <form action="/ecommerce-platform/cart/update_cart.php" method="POST" id="cart-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <?php foreach ($cartItems as $item): ?>
                                <?php
                                $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
                                $subtotal = $price * $item['quantity'];
                                ?>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-2">
                                        <img 
                                            src="<?= $item['image'] ? $item['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                                            class="cart-item-img" 
                                            alt="<?= $item['name'] ?>"
                                        >
                                    </div>
                                    <div class="col-4">
                                        <h5 class="mb-0">
                                            <a href="/ecommerce-platform/products/view_product.php?id=<?= $item['product_id'] ?>" class="text-decoration-none">
                                                <?= $item['name'] ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <?php if ($item['discount_price']): ?>
                                                <span><?= formatCurrency($item['discount_price']) ?></span>
                                                <span class="discount-price ms-2"><?= formatCurrency($item['price']) ?></span>
                                            <?php else: ?>
                                                <span><?= formatCurrency($item['price']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary quantity-minus">-</button>
                                            <input 
                                                type="number" 
                                                class="form-control text-center quantity-input" 
                                                name="quantity[<?= $item['id'] ?>]" 
                                                value="<?= $item['quantity'] ?>" 
                                                min="1"
                                                data-cart-id="<?= $item['id'] ?>"
                                            >
                                            <button type="button" class="btn btn-outline-secondary quantity-plus">+</button>
                                        </div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <span class="fw-bold" id="subtotal-<?= $item['id'] ?>"><?= formatCurrency($subtotal) ?></span>
                                    </div>
                                    <div class="col-1 text-end">
                                        <a 
                                            href="/ecommerce-platform/cart/view_cart.php?remove=<?= $item['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" 
                                            class="text-danger" 
                                            onclick="return confirm('Are you sure you want to remove this item?');"
                                        >
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php if (!$loop->last): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span id="cart-total"><?= formatCurrency($cartTotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold" id="cart-total-with-shipping"><?= formatCurrency($cartTotal) ?></span>
                        </div>
                        <div class="d-grid">
                            <a href="/ecommerce-platform/cart/checkout.php" class="btn btn-primary btn-lg">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="/ecommerce-platform/products/index.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>