</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom mt-0 pt-0 border-top-0">
            <div>
                &copy; <?php echo date('Y'); ?> FurniCraft Online Furniture Shop. Customer Portal.
            </div>
            <div>
                <a href="../product.php" class="mr-3 text-muted">Shop Collection</a>
                <a href="../contact-us.php" class="mr-3 text-muted">Customer Support</a>
                <a href="../sign-out.php" class="text-danger">Sign Out</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
