</main>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <!-- Brand & Mission -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <span class="brand-icon-wrap mr-2">
                        <i class="fas fa-couch"></i>
                    </span>
                    <h4 class="text-white mb-0 serif-heading">FurniCraft</h4>
                </div>
                <p class="text-muted pr-lg-4">
                    Transforming spaces with handcrafted comfort, sustainable materials, and timeless modern designs. Premium furniture delivered straight to your doorstep.
                </p>
                <div class="mt-4">
                    <a href="#" class="social-icon-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fab fa-pinterest-p"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                <h5>Shop</h5>
                <ul class="list-unstyled">
                    <li><a href="product.php">All Furniture</a></li>
                    <li><a href="product.php?cat_id=1">Bed Sets</a></li>
                    <li><a href="product.php?cat_id=5">Sofas & Couches</a></li>
                    <li><a href="product.php?cat_id=2">Dining Sets</a></li>
                    <li><a href="product.php?cat_id=3">Chairs & Seating</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                <h5>Help & Support</h5>
                <ul class="list-unstyled">
                    <li><a href="about-us.php">About Us</a></li>
                    <li><a href="contact-us.php">Contact Us</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                    <li><a href="customer/orders.php">Order Tracking</a></li>
                    <li><a href="admin/signin.php" target="_blank">Staff Portal</a></li>
                </ul>
            </div>

            <!-- Contact & Hours -->
            <div class="col-lg-4 col-md-6">
                <h5>Showroom & Contact</h5>
                <p class="text-muted mb-2">
                    <i class="fas fa-map-marker-alt text-warning mr-2"></i> Lahore Cantt, Punjab, Pakistan
                </p>
                <p class="text-muted mb-2">
                    <i class="fas fa-phone-alt text-warning mr-2"></i> +92 (300) 123-4567
                </p>
                <p class="text-muted mb-3">
                    <i class="fas fa-envelope text-warning mr-2"></i> support@furnicraft.store
                </p>
                <div class="bg-dark p-3 rounded border border-secondary">
                    <small class="text-light d-block font-weight-bold mb-1">
                        <i class="fas fa-clock text-warning mr-1"></i> Opening Hours
                    </small>
                    <small class="text-muted">Monday - Saturday: 09:00 AM – 08:00 PM</small>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div>
                &copy; <?php echo date('Y'); ?> FurniCraft Online Furniture Shop. All rights reserved.
            </div>
            <div class="mt-2 mt-md-0">
                <span class="mr-3"><i class="fas fa-shield-alt text-success mr-1"></i> Secure Checkout</span>
                <span><i class="fas fa-truck text-warning mr-1"></i> Fast Doorstep Delivery</span>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- SweetAlert Helper -->
<script>
function showToast(icon, title) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
</script>

</body>
</html>
<?php 
if (ob_get_level() > 0) {
    ob_end_flush(); 
}
?>
