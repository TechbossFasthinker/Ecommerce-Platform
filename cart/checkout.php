<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cartItems)) {
    $_SESSION['error_message'] = "Your cart is empty. Add products to your cart before checking out.";
    header('Location: /ecommerce-platform/cart/view_cart.php');
    exit;
}

// Get user information
$user = getUserInfo($_SESSION['user_id']);

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        // Sanitize and validate input
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $city = sanitize($_POST['city']);
        $state = sanitize($_POST['state']);
        $zip_code = sanitize($_POST['zip_code']);
        $payment_method = sanitize($_POST['payment_method']);
        
        $errors = [];
        
        // Basic validation
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($email)) $errors[] = "Email is required";
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($address)) $errors[] = "Address is required";
        if (empty($city)) $errors[] = "City is required";
        if (empty($state)) $errors[] = "State is required";
        if (empty($zip_code)) $errors[] = "ZIP code is required";
        
        // If no errors, create order
        if (empty($errors)) {
            // Format shipping address
            $shipping_address = "$first_name $last_name\n$address\n$city, $state $zip_code\nPhone: $phone";
            
            // Create order
            $orderId = createOrder($_SESSION['user_id'], $cartTotal, $shipping_address, $payment_method);
            
            if ($orderId) {
                // Redirect to confirmation page
                $_SESSION['success_message'] = "Your order has been placed successfully!";
                header("Location: /ecommerce-platform/cart/payment.php?order_id=$orderId");
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to create order. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    }
}
?>

<div class="container">
    <h1 class="mb-4">Checkout</h1>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <div class="row">
            <!-- Customer Information Form -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= $user['first_name'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= $user['last_name'] ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_method_cod" value="cash_on_delivery" checked>
                                <label class="form-check-label" for="payment_method_cod">
                                    Cash on Delivery
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_method_cc" value="credit_card">
                                <label class="form-check-label" for="payment_method_cc">
                                    Credit Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_method_pp" value="paypal">
                                <label class="form-check-label" for="payment_method_pp">
                                    PayPal
                                </label>
                            </div>
                        </div>
                        
                        <div id="credit-card-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="card_name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_expiry" class="form-label">Expiration Date</label>
                                    <input type="text" class="form-control" id="card_expiry" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="card_cvv" placeholder="123">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
                            $subtotal = $price * $item['quantity'];
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= $item['name'] ?> (x<?= $item['quantity'] ?>)</span>
                                <span><?= formatCurrency($subtotal) ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?= formatCurrency($cartTotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold"><?= formatCurrency($cartTotal) ?></span>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Place Order
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/ecommerce-platform/cart/view_cart.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i> Return to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>