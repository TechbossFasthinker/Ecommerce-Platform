<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get action from URL
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
            // Sanitize input
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = (float)$_POST['price'];
            $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
            $quantity = (int)$_POST['quantity'];
            $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
            $image = sanitize($_POST['image']);
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Product name is required";
            }
            
            if ($price <= 0) {
                $errors[] = "Price must be greater than zero";
            }
            
            if ($discount_price !== null && $discount_price >= $price) {
                $errors[] = "Discount price must be less than regular price";
            }
            
            if ($quantity < 0) {
                $errors[] = "Quantity cannot be negative";
            }
            
            // Process if no errors
            if (empty($errors)) {
                $conn = connectDB();
                
                if (isset($_POST['add_product'])) {
                    // Add new product
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, discount_price, quantity, category_id, brand_id, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssddiiisi", $name, $description, $price, $discount_price, $quantity, $category_id, $brand_id, $image, $featured);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Product added successfully.";
                        header('Location: /ecommerce-platform/admin/products.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Failed to add product. " . $stmt->error;
                    }
                } else {
                    // Update existing product
                    $product_id = (int)$_POST['product_id'];
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount_price = ?, quantity = ?, category_id = ?, brand_id = ?, image = ?, featured = ? WHERE id = ?");
                    $stmt->bind_param("ssddiiisii", $name, $description, $price, $discount_price, $quantity, $category_id, $brand_id, $image, $featured, $product_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Product updated successfully.";
                        header('Location: /ecommerce-platform/admin/products.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Failed to update product. " . $stmt->error;
                    }
                }
                
                $stmt->close();
                $conn->close();
            } else {
                $_SESSION['error_message'] = implode("<br>", $errors);
            }
        } elseif (isset($_POST['delete_product'])) {
            // Delete product
            $product_id = (int)$_POST['product_id'];
            $conn = connectDB();
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Product deleted successfully.";
                header('Location: /ecommerce-platform/admin/products.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to delete product. " . $stmt->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// Get product data for edit
$product = null;
if (($action === 'edit' || $action === 'delete') && $product_id > 0) {
    $product = getProductById($product_id);
    
    if (!$product) {
        $_SESSION['error_message'] = "Product not found.";
        header('Location: /ecommerce-platform/admin/products.php');
        exit;
    }
}

// Get all products for listing
$products = getAllProducts();

// Get categories and brands for dropdowns
$categories = getAllCategories();
$brands = getAllBrands();
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
                        <a class="nav-link active" href="/ecommerce-platform/admin/products.php">
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
                        <a class="nav-link" href="/ecommerce-platform/admin/orders.php">
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
                <h1 class="h2">
                    <?php 
                    if ($action === 'add') {
                        echo 'Add New Product';
                    } elseif ($action === 'edit') {
                        echo 'Edit Product';
                    } elseif ($action === 'delete') {
                        echo 'Delete Product';
                    } else {
                        echo 'Manage Products';
                    }
                    ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($action === 'list'): ?>
                        <a href="/ecommerce-platform/admin/products.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Product
                        </a>
                    <?php else: ?>
                        <a href="/ecommerce-platform/admin/products.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Products
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($action === 'list'): ?>
                <!-- Products List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">All Products</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <p class="text-center py-3 mb-0">No products found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Category</th>
                                            <th>Featured</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?= $product['id'] ?></td>
                                                <td>
                                                    <img 
                                                        src="<?= $product['image'] ? $product['image'] : 'https://images.pexels.com/photos/821651/pexels-photo-821651.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1' ?>" 
                                                        alt="<?= $product['name'] ?>"
                                                        style="width: 50px; height: 50px; object-fit: cover;"
                                                    >
                                                </td>
                                                <td><?= $product['name'] ?></td>
                                                <td>
                                                    <?php if ($product['discount_price']): ?>
                                                        <span class="text-success"><?= formatCurrency($product['discount_price']) ?></span>
                                                        <span class="text-muted text-decoration-line-through"><?= formatCurrency($product['price']) ?></span>
                                                    <?php else: ?>
                                                        <?= formatCurrency($product['price']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($product['quantity'] <= 0): ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php elseif ($product['quantity'] < 10): ?>
                                                        <span class="badge bg-warning text-dark"><?= $product['quantity'] ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?= $product['quantity'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $product['category_name'] ? $product['category_name'] : 'Uncategorized' ?></td>
                                                <td>
                                                    <?= $product['featured'] ? '<span class="badge bg-primary">Featured</span>' : '' ?>
                                                </td>
                                                <td>
                                                    <a href="/ecommerce-platform/admin/products.php?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/ecommerce-platform/admin/products.php?action=delete&id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
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
            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Product Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <?= $action === 'add' ? 'Add New Product' : 'Edit Product' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= $action === 'edit' ? $product['name'] : '' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?= $action === 'edit' ? $product['description'] : '' ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="price" class="form-label">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?= $action === 'edit' ? $product['price'] : '' ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_price" class="form-label">Discount Price (Optional)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="discount_price" name="discount_price" step="0.01" min="0" value="<?= $action === 'edit' && $product['discount_price'] ? $product['discount_price'] : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="<?= $action === 'edit' ? $product['quantity'] : '1' ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>" <?= $action === 'edit' && $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                        <?= $category['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="brand_id" class="form-label">Brand</label>
                                            <select class="form-select" id="brand_id" name="brand_id">
                                                <option value="">Select Brand</option>
                                                <?php foreach ($brands as $brand): ?>
                                                    <option value="<?= $brand['id'] ?>" <?= $action === 'edit' && $product['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                                                        <?= $brand['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Product Image URL</label>
                                        <input type="text" class="form-control" id="image" name="image" value="<?= $action === 'edit' ? $product['image'] : '' ?>" placeholder="https://example.com/image.jpg">
                                        <div class="form-text">Enter a URL for the product image</div>
                                    </div>
                                    
                                    <div id="image-preview-container" class="mb-3">
                                        <?php if ($action === 'edit' && $product['image']): ?>
                                            <div class="mt-2">
                                                <img src="<?= $product['image'] ?>" class="img-thumbnail" style="max-height: 200px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" <?= $action === 'edit' && $product['featured'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured">
                                            Featured Product
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/products.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="<?= $action === 'add' ? 'add_product' : 'update_product' ?>" class="btn btn-primary">
                                    <?= $action === 'add' ? 'Add Product' : 'Update Product' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($action === 'delete'): ?>
                <!-- Delete Product Confirmation -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete Product</h5>
                    </div>
                    <div class="card-body">
                        <p>Are you sure you want to delete the following product?</p>
                        <div class="alert alert-warning">
                            <h5><?= $product['name'] ?></h5>
                            <p class="mb-0">This action cannot be undone.</p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/products.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="delete_product" class="btn btn-danger">Delete Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>