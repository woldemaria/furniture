<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

// Quick verify order from dashboard
if (isset($_GET['verify_order'])) {
    $oid = (int)$_GET['verify_order'];
    mysqli_query($con, "UPDATE customer_order SET order_status = 'verified' WHERE order_id = $oid");
    $_SESSION['admin_toast'] = "Order #$oid verified successfully!";
    header('location: index.php');
    exit;
}

// Calculate Metrics
$num_pending = 0;
$res_pen = mysqli_query($con, "SELECT COUNT(*) as cnt FROM customer_order WHERE order_status='pending'");
if ($res_pen && $r = mysqli_fetch_assoc($res_pen)) $num_pending = (int)$r['cnt'];

$num_delivered = 0;
$res_del = mysqli_query($con, "SELECT COUNT(*) as cnt FROM customer_order WHERE order_status='delivered'");
if ($res_del && $r = mysqli_fetch_assoc($res_del)) $num_delivered = (int)$r['cnt'];

$num_customers = 0;
$res_cust = mysqli_query($con, "SELECT COUNT(*) as cnt FROM customer");
if ($res_cust && $r = mysqli_fetch_assoc($res_cust)) $num_customers = (int)$r['cnt'];

$total_earned = 0;
$res_earn = mysqli_query($con, "SELECT IFNULL(SUM(product_amount),0) as earn FROM customer_order WHERE order_status != 'cancelled'");
if ($res_earn && $r = mysqli_fetch_assoc($res_earn)) $total_earned = (int)$r['earn'];

$num_reviews = 0;
$res_rev = mysqli_query($con, "SELECT COUNT(*) as cnt FROM reviews");
if ($res_rev && $r = mysqli_fetch_assoc($res_rev)) $num_reviews = (int)$r['cnt'];

$num_coupons = 0;
$res_cp = mysqli_query($con, "SELECT COUNT(*) as cnt FROM coupons WHERE status='active'");
if ($res_cp && $r = mysqli_fetch_assoc($res_cp)) $num_coupons = (int)$r['cnt'];

// Top 4 Best-Selling Products
$top_sellers_query = "SELECT fp.pid, fp.title, fp.image, cat.category as cat_name, 
                      SUM(co.products_qty) as total_sold,
                      SUM(co.product_amount) as total_revenue
                      FROM customer_order co
                      INNER JOIN furniture_product fp ON co.product_id = fp.pid
                      LEFT JOIN categories cat ON fp.category = cat.id
                      WHERE co.order_status != 'cancelled'
                      GROUP BY fp.pid
                      ORDER BY total_sold DESC
                      LIMIT 4";
$top_sellers_run = mysqli_query($con, $top_sellers_query);

// Recent Pending Orders
$recent_orders_query = "SELECT co.*, fp.title, fp.image, cat.category as cat_name 
                        FROM customer_order co
                        LEFT JOIN furniture_product fp ON co.product_id = fp.pid
                        LEFT JOIN categories cat ON fp.category = cat.id
                        WHERE co.order_status='pending' 
                        ORDER BY co.order_id DESC LIMIT 5";
$recent_orders_run = mysqli_query($con, $recent_orders_query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>

        <!-- Main Dashboard Column -->
        <div class="col-lg-9 col-md-8">
            <!-- Welcome Bar -->
            <div class="admin-card mb-4 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h2 class="admin-header-title mb-1">
                            Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>!
                        </h2>
                        <p class="text-muted small mb-0">Overview of inventory, customer purchases, and active sales promotions.</p>
                    </div>
                    <div>
                        <a href="coupons.php" class="btn btn-outline-warning mr-2 mt-2 mt-sm-0">
                            <i class="fas fa-tag mr-1"></i> Promo Codes (<?php echo $num_coupons; ?>)
                        </a>
                        <a href="furniture_pro.php" class="btn btn-primary mt-2 mt-sm-0">
                            <i class="fas fa-plus mr-1"></i> Add Product
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4 Metric Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-white bg-warning admin-stat-card">
                        <div class="card-body">
                            <div>
                                <div class="admin-stat-number"><?php echo $num_pending; ?></div>
                                <div class="admin-stat-label">Pending Orders</div>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                        <a class="card-footer text-white small d-flex justify-content-between align-items-center" href="pending_furniture_pro.php" style="background: rgba(0,0,0,0.1);">
                            <span>Review Orders</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-white bg-success admin-stat-card">
                        <div class="card-body">
                            <div>
                                <div class="admin-stat-number"><?php echo $num_delivered; ?></div>
                                <div class="admin-stat-label">Delivered Orders</div>
                            </div>
                            <i class="fas fa-truck fa-2x opacity-75"></i>
                        </div>
                        <a class="card-footer text-white small d-flex justify-content-between align-items-center" href="delivered_furniture_pro.php" style="background: rgba(0,0,0,0.1);">
                            <span>View Delivered</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-white bg-primary admin-stat-card">
                        <div class="card-body">
                            <div>
                                <div class="admin-stat-number"><?php echo $num_customers; ?></div>
                                <div class="admin-stat-label">Active Customers</div>
                            </div>
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <a class="card-footer text-white small d-flex justify-content-between align-items-center" href="customers.php" style="background: rgba(0,0,0,0.1);">
                            <span>View Directory</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-white bg-dark admin-stat-card">
                        <div class="card-body">
                            <div>
                                <div class="admin-stat-number" style="font-size: 1.5rem;">PKR <?php echo number_format($total_earned); ?></div>
                                <div class="admin-stat-label">Fulfillment Revenue</div>
                            </div>
                            <i class="fas fa-coins fa-2x opacity-75 text-warning"></i>
                        </div>
                        <a class="card-footer text-white small d-flex justify-content-between align-items-center" href="verified_furniture_pro.php" style="background: rgba(255,255,255,0.08);">
                            <span>Order Invoices</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top-Selling Products Leaderboard -->
            <div class="admin-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0"><i class="fas fa-chart-line text-success mr-2"></i> Best-Selling Furniture Pieces</h5>
                    <span class="badge badge-light border text-muted">Sales Performance</span>
                </div>

                <div class="row">
                    <?php if ($top_sellers_run && mysqli_num_rows($top_sellers_run) > 0): ?>
                        <?php while ($ts = mysqli_fetch_assoc($top_sellers_run)): 
                            $ts_title = htmlspecialchars($ts['title']);
                            $ts_cat   = htmlspecialchars(ucfirst($ts['cat_name'] ?? 'Furniture'));
                            $ts_img   = htmlspecialchars($ts['image']);
                            $ts_sold  = (int)$ts['total_sold'];
                            $ts_rev   = number_format($ts['total_revenue']);
                        ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border rounded p-3 h-100 bg-light">
                                    <img src="img/<?php echo $ts_img; ?>" alt="<?php echo $ts_title; ?>" class="rounded border mb-2" style="width: 100%; height: 110px; object-fit: cover;">
                                    <h6 class="font-weight-bold text-truncate mb-1" title="<?php echo $ts_title; ?>"><?php echo $ts_title; ?></h6>
                                    <small class="text-muted d-block mb-2"><?php echo $ts_cat; ?></small>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="badge badge-success"><?php echo $ts_sold; ?> Sold</span>
                                        <span class="font-weight-bold small text-dark">PKR <?php echo $ts_rev; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-3 text-muted">No sales data available yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="admin-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0"><i class="fas fa-clock text-warning mr-2"></i> Recent Orders Needing Verification</h5>
                    <a href="pending_furniture_pro.php" class="text-primary small font-weight-bold">View All Pending &rarr;</a>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Product Item</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders_run && mysqli_num_rows($recent_orders_run) > 0): ?>
                                <?php while ($orow = mysqli_fetch_assoc($recent_orders_run)): 
                                    $oid     = (int)$orow['order_id'];
                                    $inv     = htmlspecialchars($orow['invoice_no']);
                                    $cname   = htmlspecialchars($orow['customer_fullname']);
                                    $ccity   = htmlspecialchars($orow['customer_city']);
                                    $otitle  = htmlspecialchars($orow['title'] ?? 'Custom Piece');
                                    $oimg    = htmlspecialchars($orow['image'] ?? 'couch.jpg');
                                    $oamount = number_format($orow['product_amount']);
                                    $odate   = htmlspecialchars($orow['order_date']);
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-primary">#<?php echo $inv; ?></td>
                                        <td>
                                            <div class="font-weight-bold"><?php echo $cname; ?></div>
                                            <small class="text-muted"><i class="fas fa-map-pin mr-1"></i> <?php echo $ccity; ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="img/<?php echo $oimg; ?>" alt="<?php echo $otitle; ?>" class="rounded mr-2 border" style="width: 45px; height: 45px; object-fit: cover;">
                                                <small class="font-weight-bold" style="max-width: 130px;"><?php echo $otitle; ?></small>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold text-dark">PKR <?php echo $oamount; ?></td>
                                        <td class="small text-muted"><?php echo $odate; ?></td>
                                        <td class="text-center">
                                            <a href="index.php?verify_order=<?php echo $oid; ?>" class="btn btn-sm btn-success mr-1" title="Verify Now">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="edit_furn_verify_pen.php?order_id=<?php echo $oid; ?>" class="btn btn-sm btn-outline-secondary" title="Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No pending orders. All orders have been processed!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
if (isset($_SESSION['admin_toast'])) {
    $t_msg = $_SESSION['admin_toast'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showAdminToast('success', '" . addslashes($t_msg) . "');
        });
    </script>";
    unset($_SESSION['admin_toast']);
}

require_once('include/footer.php'); 
?>
