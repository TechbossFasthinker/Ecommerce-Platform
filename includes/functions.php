<?php

if (!defined('INCLUDED_FUNCTIONS')) {
    define('INCLUDED_FUNCTIONS', true);

        session_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/ecommerce-platform/config/db.php';

    // Sanitize input data
    function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Generate CSRF token
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validate CSRF token
    function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    // Check if user is logged in
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    // Check if user is admin
    function isAdmin() {
        if (!isLoggedIn()) {
            return false;
        }
        
        // Check if user_role is set in session
        if (!isset($_SESSION['user_role'])) {
            // If not set, try to get it from database
            $userInfo = getUserInfo($_SESSION['user_id']);
            if ($userInfo) {
                $_SESSION['user_role'] = $userInfo['role'];
            } else {
                return false;
            }
        }
        
        return $_SESSION['user_role'] === 'admin';
    }

    // Redirect to login page if not logged in
    function requireLogin() {
        if (!isLoggedIn()) {
            // Store the current page URL to redirect back after login
            if (!isset($_SESSION['redirect_after_login'])) {
                $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            }
            header('Location: /ecommerce-platform/auth/login.php');
            exit;
        }
    }

    // Redirect to home page if not admin
    function requireAdmin() {
        requireLogin(); // First ensure user is logged in
        
        if (!isAdmin()) {
            // Log unauthorized access attempt
            error_log("Unauthorized admin access attempt by user ID: " . ($_SESSION['user_id'] ?? 'unknown'));
            header('Location: /ecommerce-platform/index.php?error=unauthorized');
            exit;
        }
    }

    // Get user information
    function getUserInfo($userId) {
        if (empty($userId)) {
            return null;
        }
        
        $conn = connectDB();
        
        if (!$conn) {
            error_log("Database connection failed in getUserInfo()");
            return null;
        }
        
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = ?");
        
        if (!$stmt) {
            error_log("Prepare failed in getUserInfo(): " . $conn->error);
            $conn->close();
            return null;
        }
        
        $stmt->bind_param("i", $userId);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getUserInfo(): " . $stmt->error);
            $stmt->close();
            $conn->close();
            return null;
        }
        
        $result = $stmt->get_result();
        $user = null;
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
        
        $stmt->close();
        $conn->close();
        return $user;
    }

    function setUserSession($userId, $userData = null) {
        $_SESSION['user_id'] = $userId;
        
        // If user data not provided, get it from database
        if (!$userData) {
            $userData = getUserInfo($userId);
        }
        
        if ($userData) {
            $_SESSION['user_role'] = $userData['role'];
            $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
            $_SESSION['user_email'] = $userData['email'];
        }
    }

    function clearUserSession() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['redirect_after_login']);
    }
    // Get all products
    function getAllProducts($limit = null, $featured = false) {
        $conn = connectDB();
        
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id";
        
        if ($featured) {
            $sql .= " WHERE p.featured = 1";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $result = $conn->query($sql);
        $products = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        $conn->close();
        return $products;
    }

    // Get product by ID
    function getProductById($id) {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name, b.name as brand_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN brands b ON p.brand_id = b.id 
                            WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $product;
    }

    // Get all categories
    function getAllCategories() {
        $conn = connectDB();
        $result = $conn->query("SELECT * FROM categories ORDER BY name");
        $categories = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        $conn->close();
        return $categories;
    }

    // Get all brands
    function getAllBrands() {
        $conn = connectDB();
        $result = $conn->query("SELECT * FROM brands ORDER BY name");
        $brands = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $brands[] = $row;
            }
        }
        
        $conn->close();
        return $brands;
    }

    // Add product to cart
    function addToCart($userId, $productId, $quantity) {
        $conn = connectDB();
        
        if (!$conn) {
            error_log("Database connection failed in addToCart()");
            return false;
        }
        
        // Check if product already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        
        if (!$stmt) {
            error_log("Prepare failed in addToCart() - select: " . $conn->error);
            $conn->close();
            return false;
        }
        
        $stmt->bind_param("ii", $userId, $productId);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in addToCart() - select: " . $stmt->error);
            $stmt->close();
            $conn->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $success = false;
        
        if ($result && $result->num_rows > 0) {
            // Update quantity
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            $stmt->close();
            
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("ii", $newQuantity, $row['id']);
                $success = $updateStmt->execute();
                $updateStmt->close();
            }
        } else {
            // Add new cart item
            $stmt->close();
            
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            if ($insertStmt) {
                $insertStmt->bind_param("iii", $userId, $productId, $quantity);
                $success = $insertStmt->execute();
                $insertStmt->close();
            }
        }
        
        $conn->close();
        return $success;
    }

    function getCartItems($userId) {
        $conn = connectDB();
        
        // Check if connection is successful
        if (!$conn) {
            error_log("Database connection failed in getCartItems()");
            return [];
        }
        
        $sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.discount_price, p.image 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        
        $stmt = $conn->prepare($sql);
        
        // Check if prepare was successful
        if (!$stmt) {
            error_log("Prepare failed in getCartItems(): " . $conn->error);
            $conn->close();
            return [];
        }
        
        $stmt->bind_param("i", $userId);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getCartItems(): " . $stmt->error);
            $stmt->close();
            $conn->close();
            return [];
        }
        
        $result = $stmt->get_result();
        $cartItems = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row;
            }
        }
        
        $stmt->close();
        $conn->close();
        return $cartItems;
    }

    // Calculate cart total
    function getCartTotal($userId) {
        $cartItems = getCartItems($userId);
        $total = 0;
        
        foreach ($cartItems as $item) {
            $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
            $total += $price * $item['quantity'];
        }
        
        return $total;
    }

    // Remove item from cart
    function removeFromCart($cartId, $userId) {
        $conn = connectDB();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Update cart item quantity
    function updateCartQuantity($cartId, $userId, $quantity) {
        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Create new order
    function createOrder($userId, $totalAmount, $shippingAddress, $paymentMethod) {
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) 
                            VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $userId, $totalAmount, $shippingAddress, $paymentMethod);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();
        
        // Add cart items to order_items
        $cartItems = getCartItems($userId);
        foreach ($cartItems as $item) {
            $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $price);
            $stmt->execute();
            $stmt->close();
            
            // Update product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Clear the cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        $conn->close();
        return $orderId;
    }

    // Get orders for user
    function getUserOrders($userId) {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        $stmt->close();
        $conn->close();
        return $orders;
    }

    // Get order details
    function getOrderDetails($orderId, $userId = null) {
        $conn = connectDB();
        
        if ($userId) {
            // If userId provided, ensure order belongs to user
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $orderId, $userId);
        } else {
            // Admin can view any order
            $stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.id 
                                WHERE o.id = ?");
            $stmt->bind_param("i", $orderId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            // Get order items
            $stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                                FROM order_items oi 
                                JOIN products p ON oi.product_id = p.id 
                                WHERE oi.order_id = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            $order['items'] = $items;
            $stmt->close();
        }
        
        $conn->close();
        return $order;
    }

    // Get all orders (admin)
    function getAllOrders() {
        $conn = connectDB();
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
        $result = $conn->query($sql);
        $orders = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        $conn->close();
        return $orders;
    }

    // Update order status
    function updateOrderStatus($orderId, $status) {
        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Update payment status
    function updatePaymentStatus($orderId, $status) {
        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    // Format currency
    function formatCurrency($amount) {
        return '$' . number_format($amount, 2);
    }

    // Display status badge
    function getStatusBadge($status, $type = 'order') {
        $badgeClass = '';
        
        if ($type === 'order') {
            switch ($status) {
                case 'processing':
                    $badgeClass = 'bg-warning';
                    break;
                case 'shipped':
                    $badgeClass = 'bg-info';
                    break;
                case 'delivered':
                    $badgeClass = 'bg-success';
                    break;
                case 'cancelled':
                    $badgeClass = 'bg-danger';
                    break;
            }
        } else if ($type === 'payment') {
            switch ($status) {
                case 'pending':
                    $badgeClass = 'bg-warning';
                    break;
                case 'completed':
                    $badgeClass = 'bg-success';
                    break;
                case 'failed':
                    $badgeClass = 'bg-danger';
                    break;
            }
        }
        
        return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
    }

    // Get product count
    function getProductCount() {
        $conn = connectDB();
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['count'];
    }

    // Get order count
    function getOrderCount() {
        $conn = connectDB();
        $result = $conn->query("SELECT COUNT(*) as count FROM orders");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['count'];
    }

    // Get user count
    function getUserCount() {
        $conn = connectDB();
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['count'];
    }

    // Get total revenue
    function getTotalRevenue() {
        $conn = connectDB();
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['total'] ? $row['total'] : 0;
    }

    // Get recent orders
    function getRecentOrders($limit = 5) {
        $conn = connectDB();
        $sql = "SELECT o.*, u.first_name, u.last_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT " . (int)$limit;
        $result = $conn->query($sql);
        $orders = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        $conn->close();
        return $orders;
    }

    // Search products
    function searchProducts($keyword) {
        $conn = connectDB();
        $keyword = "%" . $keyword . "%";
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name, b.name as brand_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN brands b ON p.brand_id = b.id 
                            WHERE p.name LIKE ? OR p.description LIKE ? 
                            ORDER BY p.created_at DESC");
        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        $stmt->close();
        $conn->close();
        return $products;
    }

    // Filter products by category
    function getProductsByCategory($categoryId) {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name, b.name as brand_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN brands b ON p.brand_id = b.id 
                            WHERE p.category_id = ? 
                            ORDER BY p.created_at DESC");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        $stmt->close();
        $conn->close();
        return $products;
    }

    // Get category by ID
    function getCategoryById($id) {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $category;
    }

    // Get brand by ID
    function getBrandById($id) {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $brand = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $brand;
    }
}
?>
