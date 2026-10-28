<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="customer-sidebar">
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body text-center p-4 border-bottom">
            <div class="mx-auto mb-3 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 68px; height: 68px; background: linear-gradient(135deg, #1e293b, #0f172a); font-size: 1.75rem;">
                <i class="fas fa-user"></i>
            </div>
            <h5 class="mb-1 font-weight-bold"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Customer'); ?></h5>
            <small class="text-muted text-break"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></small>
        </div>
        <div class="p-3">
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Overview
                </a>
                <a href="orders.php" class="list-group-item <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-box-open"></i> My Orders
                </a>
                <a href="../wishlist.php" class="list-group-item">
                    <i class="fas fa-heart text-danger"></i> My Wishlist
                </a>
                <a href="personal-detail.php" class="list-group-item <?php echo ($current_page == 'personal-detail.php') ? 'active' : ''; ?>">
                    <i class="fas fa-id-card"></i> Personal Details
                </a>
                <a href="access-detail.php" class="list-group-item <?php echo ($current_page == 'access-detail.php') ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> Access & Password
                </a>
                <a href="../sign-out.php" class="list-group-item text-danger">
                    <i class="fas fa-sign-out-alt text-danger"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
</div>
