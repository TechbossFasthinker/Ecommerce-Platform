<?php
require_once '../includes/header.php';

// Ensure user is logged in
requireLogin();

// Get user information
$user = getUserInfo($_SESSION['user_id']);

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Security validation failed. Please try again.";
    } else {
        // Sanitize input
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        // Check if email already exists (if changed)
        if ($email !== $user['email']) {
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists. Please use a different email.";
            }
            
            $stmt->close();
            $conn->close();
        }
        
        // Password validation
        $update_password = false;
        if (!empty($new_password) || !empty($confirm_password)) {
            // Current password is required to change password
            if (empty($current_password)) {
                $errors[] = "Current password is required to change password";
            } else {
                // Verify current password
                $conn = connectDB();
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                if (!password_verify($current_password, $row['password'])) {
                    $errors[] = "Current password is incorrect";
                } else {
                    // Validate new password
                    if (strlen($new_password) < 6) {
                        $errors[] = "New password must be at least 6 characters";
                    } elseif ($new_password !== $confirm_password) {
                        $errors[] = "New passwords do not match";
                    } else {
                        $update_password = true;
                    }
                }
                
                $conn->close();
            }
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            $conn = connectDB();
            
            // Update basic information
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("sssi", $first_name, $last_name, $email, $_SESSION['user_id']);
            $result = $stmt->execute();
            $stmt->close();
            
            // Update password if requested
            if ($update_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
            }
            
            $conn->close();
            
            // Update session
            $_SESSION['user_name'] = $first_name;
            $_SESSION['user_email'] = $email;
            
            $success = true;
            $_SESSION['success_message'] = "Profile updated successfully.";
            
            // Refresh user data
            $user = getUserInfo($_SESSION['user_id']);
        }
    }
}
?>

<div class="container">
    <h1 class="mb-4">Edit Profile</h1>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="list-group">
                <a href="/ecommerce-platform/users/dashboard.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="/ecommerce-platform/users/orders.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-bag me-2"></i> My Orders
                </a>
                <a href="/ecommerce-platform/users/update_profile.php" class="list-group-item list-group-item-action active">
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
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Profile updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
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
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?>" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Change Password</h5>
                        <p class="text-muted mb-3">Leave blank if you don't want to change your password.</p>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Password must be at least 6 characters.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>