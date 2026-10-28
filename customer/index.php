<?php 
include('include/header.php');

$customer_id = (int)$_SESSION['id'];

// Fetch quick stats
$total_orders = 0;
$pending_orders = 0;
$delivered_orders = 0;

$stat_res = mysqli_query($con, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered
    FROM customer_order WHERE customer_id = $customer_id");

if ($stat_res && $row = mysqli_fetch_assoc($stat_res)) {
    $total_orders = (int)$row['total'];
    $pending_orders = (int)$row['pending'];
    $delivered_orders = (int)$row['delivered'];
}
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">My Account</h1>
        <div class="page-breadcrumb">
            <a href="../index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Customer Portal</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 col-md-5 mb-4">
            <?php include('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-8 col-md-7">
            <!-- Welcome greeting -->
            <div class="card border-0 shadow-sm rounded-lg p-4 mb-4 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h3 class="serif-heading mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Friend'); ?>!</h3>
                        <p class="text-muted mb-0">From your account dashboard, you can view your recent orders and manage your account details.</p>
                    </div>
                    <a href="../product.php" class="btn btn-accent-custom btn-sm mt-3 mt-sm-0">
                        <i class="fas fa-shopping-bag mr-1"></i> Browse Catalog
                    </a>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="row mb-4">
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg p-3 bg-white h-100">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-primary bg-light p-3 rounded-circle">
                                <i class="fas fa-shopping-basket fa-2x"></i>
                            </div>
                            <div>
                                <h3 class="font-weight-bold mb-0"><?php echo $total_orders; ?></h3>
                                <small class="text-muted font-weight-bold uppercase">Total Orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg p-3 bg-white h-100">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-warning bg-light p-3 rounded-circle">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <div>
                                <h3 class="font-weight-bold mb-0"><?php echo $pending_orders; ?></h3>
                                <small class="text-muted font-weight-bold uppercase">Pending Orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg p-3 bg-white h-100">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-success bg-light p-3 rounded-circle">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div>
                                <h3 class="font-weight-bold mb-0"><?php echo $delivered_orders; ?></h3>
                                <small class="text-muted font-weight-bold uppercase">Delivered Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links Cards -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg p-4 h-100 bg-white">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-box text-accent fa-2x mr-3 mt-1"></i>
                            <div>
                                <h5 class="font-weight-bold mb-1">Recent Orders</h5>
                                <p class="text-muted small mb-3">Check order statuses, review items, and download official invoices.</p>
                                <a href="orders.php" class="btn btn-outline-custom btn-sm">View Orders</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg p-4 h-100 bg-white">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-user-edit text-primary fa-2x mr-3 mt-1"></i>
                            <div>
                                <h5 class="font-weight-bold mb-1">Profile Details</h5>
                                <p class="text-muted small mb-3">Keep your delivery address, phone number, and name up to date for fast checkout.</p>
                                <a href="personal-detail.php" class="btn btn-outline-custom btn-sm">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
