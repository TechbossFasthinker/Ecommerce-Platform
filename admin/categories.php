<?php
// Start session and handle redirects BEFORE any output
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/ecommerce-platform/includes/functions.php';

// Ensure user is admin
requireAdmin();

// Get action from URL
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
            // Sanitize input
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Category name is required";
            }
            
            // Process if no errors
            if (empty($errors)) {
                $conn = connectDB();
                
                if (!$conn) {
                    $_SESSION['error_message'] = "Database connection failed.";
                } else {
                    if (isset($_POST['add_category'])) {
                        // Add new category
                        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ss", $name, $description);
                            
                            if ($stmt->execute()) {
                                $_SESSION['success_message'] = "Category added successfully.";
                                $stmt->close();
                                $conn->close();
                                header('Location: /ecommerce-platform/admin/categories.php');
                                exit;
                            } else {
                                $_SESSION['error_message'] = "Failed to add category: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $_SESSION['error_message'] = "Database prepare failed: " . $conn->error;
                        }
                    } else {
                        // Update existing category
                        $category_id = (int)$_POST['category_id'];
                        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("ssi", $name, $description, $category_id);
                            
                            if ($stmt->execute()) {
                                $_SESSION['success_message'] = "Category updated successfully.";
                                $stmt->close();
                                $conn->close();
                                header('Location: /ecommerce-platform/admin/categories.php');
                                exit;
                            } else {
                                $_SESSION['error_message'] = "Failed to update category: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $_SESSION['error_message'] = "Database prepare failed: " . $conn->error;
                        }
                    }
                    $conn->close();
                }
            } else {
                $_SESSION['error_message'] = implode("<br>", $errors);
            }
        } elseif (isset($_POST['delete_category'])) {
            // Delete category
            $category_id = (int)$_POST['category_id'];
            $conn = connectDB();
            
            if (!$conn) {
                $_SESSION['error_message'] = "Database connection failed.";
            } else {
                // First update products to remove the category reference
                $stmt = $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $category_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Then delete the category
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $category_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Category deleted successfully.";
                        $stmt->close();
                        $conn->close();
                        header('Location: /ecommerce-platform/admin/categories.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Failed to delete category: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['error_message'] = "Database prepare failed: " . $conn->error;
                }
                $conn->close();
            }
        }
    }
}

// Get category data for edit
$category = null;
if (($action === 'edit' || $action === 'delete') && $category_id > 0) {
    $category = getCategoryById($category_id);
    
    if (!$category) {
        $_SESSION['error_message'] = "Category not found.";
        header('Location: /ecommerce-platform/admin/categories.php');
        exit;
    }
}

// Get all categories for listing
$categories = getAllCategories();

// NOW include the header - after all processing is done
require_once '../includes/header.php';
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
                        <a class="nav-link active" href="/ecommerce-platform/admin/categories.php">
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
                        echo 'Add New Category';
                    } elseif ($action === 'edit') {
                        echo 'Edit Category';
                    } elseif ($action === 'delete') {
                        echo 'Delete Category';
                    } else {
                        echo 'Manage Categories';
                    }
                    ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($action === 'list'): ?>
                        <a href="/ecommerce-platform/admin/categories.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Category
                        </a>
                    <?php else: ?>
                        <a href="/ecommerce-platform/admin/categories.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Categories
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($action === 'list'): ?>
                <!-- Categories List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">All Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <p class="text-center py-3 mb-0">No categories found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($category['id']) ?></td>
                                                <td><?= htmlspecialchars($category['name']) ?></td>
                                                <td><?= $category['description'] ? htmlspecialchars($category['description']) : 'No description' ?></td>
                                                <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                                                <td>
                                                    <a href="/ecommerce-platform/admin/categories.php?action=edit&id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/ecommerce-platform/admin/categories.php?action=delete&id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-danger">
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
                <!-- Add/Edit Category Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <?= $action === 'add' ? 'Add New Category' : 'Edit Category' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= $action === 'edit' ? htmlspecialchars($category['name']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= $action === 'edit' ? htmlspecialchars($category['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/categories.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="<?= $action === 'add' ? 'add_category' : 'update_category' ?>" class="btn btn-primary">
                                    <?= $action === 'add' ? 'Add Category' : 'Update Category' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($action === 'delete'): ?>
                <!-- Delete Category Confirmation -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete Category</h5>
                    </div>
                    <div class="card-body">
                        <p>Are you sure you want to delete the following category?</p>
                        <div class="alert alert-warning">
                            <h5><?= htmlspecialchars($category['name']) ?></h5>
                            <p>Note: Products in this category will be set to uncategorized.</p>
                            <p class="mb-0">This action cannot be undone.</p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/categories.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>