<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get action from URL
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['add_brand']) || isset($_POST['update_brand'])) {
            // Sanitize input
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Brand name is required";
            }
            
            // Process if no errors
            if (empty($errors)) {
                $conn = connectDB();
                
                if (isset($_POST['add_brand'])) {
                    // Add new brand
                    $stmt = $conn->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $description);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Brand added successfully.";
                        header('Location: /ecommerce-platform/admin/brands.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Failed to add brand. " . $stmt->error;
                    }
                } else {
                    // Update existing brand
                    $brand_id = (int)$_POST['brand_id'];
                    $stmt = $conn->prepare("UPDATE brands SET name = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $name, $description, $brand_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Brand updated successfully.";
                        header('Location: /ecommerce-platform/admin/brands.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Failed to update brand. " . $stmt->error;
                    }
                }
                
                $stmt->close();
                $conn->close();
            } else {
                $_SESSION['error_message'] = implode("<br>", $errors);
            }
        } elseif (isset($_POST['delete_brand'])) {
            // Delete brand
            $brand_id = (int)$_POST['brand_id'];
            $conn = connectDB();
            
            // First update products to remove the brand reference
            $stmt = $conn->prepare("UPDATE products SET brand_id = NULL WHERE brand_id = ?");
            $stmt->bind_param("i", $brand_id);
            $stmt->execute();
            $stmt->close();
            
            // Then delete the brand
            $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
            $stmt->bind_param("i", $brand_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Brand deleted successfully.";
                header('Location: /ecommerce-platform/admin/brands.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to delete brand. " . $stmt->error;
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// Get brand data for edit
$brand = null;
if (($action === 'edit' || $action === 'delete') && $brand_id > 0) {
    $brand = getBrandById($brand_id);
    
    if (!$brand) {
        $_SESSION['error_message'] = "Brand not found.";
        header('Location: /ecommerce-platform/admin/brands.php');
        exit;
    }
}

// Get all brands for listing
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
                        <a class="nav-link active" href="/ecommerce-platform/admin/brands.php">
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
                        echo 'Add New Brand';
                    } elseif ($action === 'edit') {
                        echo 'Edit Brand';
                    } elseif ($action === 'delete') {
                        echo 'Delete Brand';
                    } else {
                        echo 'Manage Brands';
                    }
                    ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($action === 'list'): ?>
                        <a href="/ecommerce-platform/admin/brands.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Add New Brand
                        </a>
                    <?php else: ?>
                        <a href="/ecommerce-platform/admin/brands.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Brands
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($action === 'list'): ?>
                <!-- Brands List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">All Brands</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($brands)): ?>
                            <p class="text-center py-3 mb-0">No brands found.</p>
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
                                        <?php foreach ($brands as $brand): ?>
                                            <tr>
                                                <td><?= $brand['id'] ?></td>
                                                <td><?= $brand['name'] ?></td>
                                                <td><?= $brand['description'] ? $brand['description'] : 'No description' ?></td>
                                                <td><?= date('M d, Y', strtotime($brand['created_at'])) ?></td>
                                                <td>
                                                    <a href="/ecommerce-platform/admin/brands.php?action=edit&id=<?= $brand['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/ecommerce-platform/admin/brands.php?action=delete&id=<?= $brand['id'] ?>" class="btn btn-sm btn-outline-danger">
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
                <!-- Add/Edit Brand Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <?= $action === 'add' ? 'Add New Brand' : 'Edit Brand' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="brand_id" value="<?= $brand['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Brand Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= $action === 'edit' ? $brand['name'] : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= $action === 'edit' ? $brand['description'] : '' ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/brands.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="<?= $action === 'add' ? 'add_brand' : 'update_brand' ?>" class="btn btn-primary">
                                    <?= $action === 'add' ? 'Add Brand' : 'Update Brand' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif ($action === 'delete'): ?>
                <!-- Delete Brand Confirmation -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete Brand</h5>
                    </div>
                    <div class="card-body">
                        <p>Are you sure you want to delete the following brand?</p>
                        <div class="alert alert-warning">
                            <h5><?= $brand['name'] ?></h5>
                            <p>Note: Products with this brand will be set to no brand.</p>
                            <p class="mb-0">This action cannot be undone.</p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="brand_id" value="<?= $brand['id'] ?>">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/ecommerce-platform/admin/brands.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="delete_brand" class="btn btn-danger">Delete Brand</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>