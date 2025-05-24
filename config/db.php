<?php
// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce_db');

// Establish database connection
function connectDB() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create database if it doesn't exist
function createDatabaseIfNotExists() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === TRUE) {
        echo "<br>";
    } else {
        echo "Error creating database: " . $conn->error . "<br>";
    }
    
    $conn->close();
}

// Initialize database tables
function initializeTables() {
    $conn = connectDB();
    
    // SQL to create users table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // SQL to create categories table
    $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // SQL to create brands table
    $sql_brands = "CREATE TABLE IF NOT EXISTS brands (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // SQL to create products table
    $sql_products = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        discount_price DECIMAL(10,2),
        quantity INT(11) NOT NULL DEFAULT 0,
        category_id INT(11) UNSIGNED,
        brand_id INT(11) UNSIGNED,
        image VARCHAR(255),
        featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
    )";
    
    // SQL to create cart table
    $sql_cart = "CREATE TABLE IF NOT EXISTS cart (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    
    // SQL to create orders table
    $sql_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        order_status ENUM('processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'processing',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // SQL to create order_items table
    $sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    
    // Execute table creation queries
    $conn->query($sql_users);
    $conn->query($sql_categories);
    $conn->query($sql_brands);
    $conn->query($sql_products);
    $conn->query($sql_cart);
    $conn->query($sql_orders);
    $conn->query($sql_order_items);
    
    // Insert default admin user
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $check_admin = "SELECT * FROM users WHERE email = 'admin@example.com'";
    $result = $conn->query($check_admin);
    
    if ($result->num_rows == 0) {
        $insert_admin = "INSERT INTO users (first_name, last_name, email, password, role) 
                         VALUES ('Admin', 'User', 'admin@example.com', '$admin_password', 'admin')";
        $conn->query($insert_admin);
    }
    
    // Insert sample categories
    $categories = [
        ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets'],
        ['name' => 'Clothing', 'description' => 'Fashion items and apparel'],
        ['name' => 'Home & Kitchen', 'description' => 'Home appliances and kitchenware']
    ];
    
    foreach ($categories as $category) {
        $check_category = "SELECT * FROM categories WHERE name = '{$category['name']}'";
        $result = $conn->query($check_category);
        
        if ($result->num_rows == 0) {
            $insert_category = "INSERT INTO categories (name, description) 
                               VALUES ('{$category['name']}', '{$category['description']}')";
            $conn->query($insert_category);
        }
    }
    
    // Insert sample brands
    $brands = [
        ['name' => 'Apple', 'description' => 'Premium technology products'],
        ['name' => 'Samsung', 'description' => 'Consumer electronics and appliances'],
        ['name' => 'Nike', 'description' => 'Athletic footwear and apparel']
    ];
    
    foreach ($brands as $brand) {
        $check_brand = "SELECT * FROM brands WHERE name = '{$brand['name']}'";
        $result = $conn->query($check_brand);
        
        if ($result->num_rows == 0) {
            $insert_brand = "INSERT INTO brands (name, description) 
                            VALUES ('{$brand['name']}', '{$brand['description']}')";
            $conn->query($insert_brand);
        }
    }
    
    $conn->close();
}

// Function to initialize the database
function initializeDatabase() {
    createDatabaseIfNotExists();
    initializeTables();
}
?>