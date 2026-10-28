</div><!-- /.admin-main-wrapper -->

<footer class="admin-footer">
  <div class="container-fluid text-center">
    <span>&copy; <?php echo date('Y'); ?> FurniCraft Management System. All Rights Reserved.</span>
  </div>
</footer>

<!-- JavaScript Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function showAdminToast(icon, title) {
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
