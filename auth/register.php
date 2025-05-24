<?php
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /ecommerce-platform/index.php');
    exit;
}

$first_name = $last_name = $email = $password = $confirm_password = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Security validation failed. Please try again.";
    } else {
        // Sanitize and validate input
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
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
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists. Please login or use a different email.";
            }
            
            $stmt->close();
            $conn->close();
        }
        
        // Register user if no errors
        if (empty($errors)) {
            $conn = connectDB();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer'; // Default role
            
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                // Set session variables
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $first_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                
                // Redirect to homepage with success message
                $_SESSION['success_message'] = "Registration successful! Welcome to ShopHub.";
                header('Location: /ecommerce-platform/index.php');
                exit;
            } else {
                $errors[] = "Registration failed. Please try again later.";
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<div class="container">
    <div class="auth-container">
        <h2 class="text-center mb-4">Create an Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= $first_name ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= $last_name ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">Password must be at least 6 characters.</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Register</button>
            </div>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="/ecommerce-platform/auth/login.php">Login</a></p>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>