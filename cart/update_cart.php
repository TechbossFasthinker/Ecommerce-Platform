<?php
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Handle AJAX requests
header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

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
    $conn->close();
    
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
?>