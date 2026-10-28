<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once(__DIR__ . '/dbcon.php');

$current_page = basename($_SERVER['PHP_SELF']);

$admin_image = 'avatar.png';
$admin_name = 'Administrator';
if (isset($_SESSION['email'])) {
    $session_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $query = "SELECT * FROM admin WHERE email = '$session_email' LIMIT 1";
    $run = mysqli_query($con, $query);
    if ($run && $row = mysqli_fetch_assoc($run)) {
        $admin_image = !empty($row['image']) ? $row['image'] : 'avatar.png';
        $admin_name  = !empty($row['name']) ? $row['name'] : 'Administrator';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Admin Dashboard | FurniCraft Management</title>
  <link href="../img/favicon.png" rel="icon" type="image/png">

  <!-- Core CSS -->
  <link href="css/bootstrap.css" rel="stylesheet">
  <link href="css/all.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-top">
<div class="admin-main-wrapper">
<nav class="navbar navbar-expand-lg navbar-dark admin-navbar sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="index.php">
      <span class="badge badge-warning text-dark p-2 rounded mr-1"><i class="fas fa-couch"></i></span>
      FurniCraft <small class="text-muted ml-1" style="font-size:0.75rem;">ADMIN PANEL</small>
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar" aria-controls="adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt fa-sm mr-1"></i> View Live Store
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'furniture_pro.php') ? 'active' : ''; ?>" href="furniture_pro.php">
            <i class="fas fa-plus-circle fa-sm mr-1"></i> Add Product
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'category.php' || $current_page == 'editcat.php') ? 'active' : ''; ?>" href="category.php">
            <i class="fas fa-tags fa-sm mr-1"></i> Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>" href="customers.php">
            <i class="fas fa-users fa-sm mr-1"></i> Customers
          </a>
        </li>
        
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item dropdown ml-lg-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminProfileMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img src="img/<?php echo htmlspecialchars($admin_image); ?>" alt="Admin" class="rounded-circle mr-2 border border-secondary" style="width: 32px; height: 32px; object-fit: cover;">
              <span><?php echo htmlspecialchars(explode(' ', $admin_name)[0]); ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="adminProfileMenu">
              <a class="dropdown-item py-2" href="profile.php"><i class="fas fa-user-cog mr-2 text-muted"></i> Profile Settings</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item py-2 text-danger" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Log Out</a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="signin.php"><i class="fas fa-sign-in-alt mr-1"></i> Admin Sign In</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
