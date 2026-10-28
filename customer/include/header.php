<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
include_once(__DIR__ . '/dbcon.php');

if (!isset($_SESSION['email'])) {
    header('location: ../sign-in.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// Count items in cart & wishlist
$cart_count = 0;
$wishlist_count = 0;

if (isset($_SESSION['id'])) {
    $cust_id = (int)$_SESSION['id'];

    $count_query = "SELECT SUM(quantity) as total_qty FROM cart WHERE cust_id = $cust_id";
    $count_res = mysqli_query($con, $count_query);
    if ($count_res && $count_row = mysqli_fetch_assoc($count_res)) {
        $cart_count = (int)$count_row['total_qty'];
    }

    $wish_query = "SELECT COUNT(*) as total_wish FROM wishlist WHERE cust_id = $cust_id";
    $wish_res = mysqli_query($con, $wish_query);
    if ($wish_res && $wish_row = mysqli_fetch_assoc($wish_res)) {
        $wishlist_count = (int)$wish_row['total_wish'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal | FurniCraft</title>
    <link href="../img/favicon.png" rel="icon" type="image/png">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  
<header>
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a href="../index.php" class="navbar-brand"> 
                <span class="brand-icon-wrap">
                    <i class="fas fa-couch"></i>
                </span>
                <span>Furni<span class="text-warning">Craft</span></span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars text-dark"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../product.php">Collection</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../about-us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contact-us.php">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item mr-2">
                        <a class="nav-link" href="../wishlist.php" title="My Wishlist">
                            <i class="fas fa-heart mr-1 text-danger"></i> Wishlist
                            <?php if ($wishlist_count > 0): ?>
                                <span class="nav-badge-pill" style="background: #e11d48;"><?php echo $wishlist_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item mr-2">
                        <a class="nav-link" href="../cart.php" title="View Cart">
                            <i class="fas fa-shopping-bag mr-1"></i> Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="nav-badge-pill"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown ml-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="customerMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg mr-1 text-primary"></i> 
                            <span class="font-weight-bold"><?php echo htmlspecialchars(explode(' ', $_SESSION['name'] ?? 'Account')[0]); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="customerMenu">
                            <a class="dropdown-item py-2" href="index.php"><i class="fas fa-th-large text-muted mr-2"></i> Dashboard</a>
                            <a class="dropdown-item py-2" href="orders.php"><i class="fas fa-box text-muted mr-2"></i> My Orders</a>
                            <a class="dropdown-item py-2" href="../wishlist.php"><i class="fas fa-heart text-danger mr-2"></i> My Wishlist</a>
                            <a class="dropdown-item py-2" href="personal-detail.php"><i class="fas fa-id-card text-muted mr-2"></i> Profile Details</a>
                            <a class="dropdown-item py-2" href="access-detail.php"><i class="fas fa-key text-muted mr-2"></i> Change Password</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item py-2 text-danger" href="../sign-out.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="page-wrapper">
