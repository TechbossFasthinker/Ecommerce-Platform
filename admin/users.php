<?php
require_once '../includes/header.php';

// Ensure user is admin
requireAdmin();

// Get all users
$conn = connectDB();
$result = $conn->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY id");
$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
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
                        <a class="nav-link active" href="/ecommerce-platform/admin/users.php">
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
                <h1 class="h2">Manage Users</h1>
            </div>
            
            <!-- Users List -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-center py-3 mb-0">No users found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered On</th>
                                        <th>Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= $user['first_name'] . ' ' . $user['last_name'] ?></td>
                                            <td><?= $user['email'] ?></td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Customer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <a href="/ecommerce-platform/admin/orders.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    View Orders
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
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>