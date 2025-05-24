<?php
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /ecommerce-platform/index.php');
    exit;
}

$email = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Security validation failed. Please try again.";
    } else {
        // Sanitize input
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        // Validation
        if (empty($email)) {
            $errors[] = "Email is required";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        }
        
        // Authenticate user
        if (empty($errors)) {
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT id, first_name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /ecommerce-platform/admin/index.php');
                    } else {
                        // Check if there's a redirect URL
                        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/ecommerce-platform/index.php';
                        header("Location: $redirect");
                    }
                    exit;
                } else {
                    $errors[] = "Invalid email or password";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

// Check if there's a redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
?>

<div class="container">
    <div class="auth-container">
        <h2 class="text-center mb-4">Login to Your Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= $redirect ? "login.php?redirect=$redirect" : "login.php" ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
            
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="/ecommerce-platform/auth/register.php">Register</a></p>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>