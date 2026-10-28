<?php 
$current_page = basename($_SERVER['PHP_SELF']);
$is_order_page = in_array($current_page, ['verified_furniture_pro.php', 'pending_furniture_pro.php', 'delivered_furniture_pro.php', 'edit_furn_verify_pen.php']);
?>
<div class="admin-sidebar shadow-sm">
  <div class="list-group list-group-flush">
    <a href="index.php" class="list-group-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <a href="#ordersSubmenu" data-toggle="collapse" aria-expanded="<?php echo $is_order_page ? 'true' : 'false'; ?>" class="list-group-item d-flex justify-content-between align-items-center <?php echo $is_order_page ? 'text-primary font-weight-bold' : ''; ?>">
      <span><i class="fas fa-boxes"></i> Orders Management</span>
      <i class="fas fa-chevron-down fa-xs"></i>
    </a>
    <ul class="collapse <?php echo $is_order_page ? 'show' : ''; ?>" id="ordersSubmenu">
      <li>
        <a href="pending_furniture_pro.php" class="list-group-item <?php echo ($current_page == 'pending_furniture_pro.php') ? 'active' : ''; ?>">
          <i class="fas fa-clock text-warning"></i> Pending Orders
        </a>
      </li>
      <li>
        <a href="verified_furniture_pro.php" class="list-group-item <?php echo ($current_page == 'verified_furniture_pro.php') ? 'active' : ''; ?>">
          <i class="fas fa-check-circle text-info"></i> Verified Orders
        </a>
      </li>
      <li>
        <a href="delivered_furniture_pro.php" class="list-group-item <?php echo ($current_page == 'delivered_furniture_pro.php') ? 'active' : ''; ?>">
          <i class="fas fa-truck text-success"></i> Delivered Orders
        </a>
      </li>
    </ul>

    <a href="furniture_pro.php" class="list-group-item <?php echo ($current_page == 'furniture_pro.php') ? 'active' : ''; ?>">
      <i class="fas fa-plus-circle"></i> Add New Product
    </a>

    <a href="furniture_pro_view.php" class="list-group-item <?php echo ($current_page == 'furniture_pro_view.php' || $current_page == 'furniture_pro_edit.php') ? 'active' : ''; ?>">
      <i class="fas fa-couch"></i> All Products Catalog
    </a>

    <a href="category.php" class="list-group-item <?php echo ($current_page == 'category.php' || $current_page == 'editcat.php') ? 'active' : ''; ?>">
      <i class="fas fa-border-all"></i> Categories
    </a>

    <a href="coupons.php" class="list-group-item <?php echo ($current_page == 'coupons.php') ? 'active' : ''; ?>">
      <i class="fas fa-tag text-warning"></i> Promo Coupons
    </a>

    <a href="reviews.php" class="list-group-item <?php echo ($current_page == 'reviews.php') ? 'active' : ''; ?>">
      <i class="fas fa-star text-warning"></i> Customer Reviews
    </a>

    <a href="customers.php" class="list-group-item <?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>">
      <i class="fas fa-users"></i> Registered Customers
    </a>

    <a href="profile.php" class="list-group-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
      <i class="fas fa-cog"></i> Profile Settings
    </a>

    <a href="logout.php" class="list-group-item text-danger border-top mt-2">
      <i class="fas fa-sign-out-alt text-danger"></i> Sign Out
    </a>
  </div>
</div>
