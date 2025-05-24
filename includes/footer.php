</main>
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>ShopHub</h5>
                    <p class="text-muted">Your one-stop shop for quality products at affordable prices.</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <h5>Shop</h5>
                    <ul class="list-unstyled">
                        <?php
                        $categories = getAllCategories();
                        $count = 0;
                        foreach ($categories as $category) {
                            if ($count < 4) {
                                echo '<li><a href="/ecommerce-platform/products/index.php?category=' . $category['id'] . '" class="text-muted">' . $category['name'] . '</a></li>';
                                $count++;
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <h5>Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/ecommerce-platform/index.php" class="text-muted">Home</a></li>
                        <li><a href="/ecommerce-platform/products/index.php" class="text-muted">Products</a></li>
                        <li><a href="#" class="text-muted">About Us</a></li>
                        <li><a href="#" class="text-muted">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address class="text-muted">
                        <p><i class="fas fa-map-marker-alt me-2"></i> 123 Commerce St, City, Country</p>
                        <p><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope me-2"></i> info@shophub.com</p>
                    </address>
                </div>
            </div>
            <hr class="my-3 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> ShopHub. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 text-muted">
                        <a href="#" class="text-muted me-3">Privacy Policy</a>
                        <a href="#" class="text-muted me-3">Terms of Service</a>
                        <a href="#" class="text-muted">FAQ</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/ecommerce-platform/assets/js/script.js"></script>
</body>
</html>