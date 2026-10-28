<?php 
include('include/header.php');

$customer_id = (int)$_SESSION['id'];

// Handle Self Order Cancellation
if (isset($_GET['cancel_order'])) {
    $cancel_id = (int)$_GET['cancel_order'];

    // Verify ownership and pending status
    $check_own = mysqli_query($con, "SELECT invoice_no FROM customer_order WHERE order_id = $cancel_id AND customer_id = $customer_id AND order_status = 'pending'");
    if ($check_own && mysqli_num_rows($check_own) > 0) {
        $row_inv = mysqli_fetch_assoc($check_own);
        $inv_num = htmlspecialchars($row_inv['invoice_no']);
        
        mysqli_query($con, "UPDATE customer_order SET order_status = 'cancelled' WHERE order_id = $cancel_id");
        $_SESSION['message'] = "<div class='alert alert-warning alert-dismissible fade show p-3' role='alert'>
            <i class='fas fa-info-circle mr-2'></i> Order item under invoice #<strong>$inv_num</strong> has been cancelled.
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
        </div>";
    }
    header("location: orders.php");
    exit;
}

// Get all orders for this customer ordered by order_id DESC
$order_query = "SELECT co.*, fp.title, fp.image, fp.size, cat.category as cat_name 
                FROM customer_order co
                LEFT JOIN furniture_product fp ON co.product_id = fp.pid
                LEFT JOIN categories cat ON fp.category = cat.id
                WHERE co.customer_id = $customer_id 
                ORDER BY co.order_id DESC";
$run = mysqli_query($con, $order_query);
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">My Orders</h1>
        <div class="page-breadcrumb">
            <a href="../index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="index.php">Account</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Orders History</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 col-md-5 mb-4">
            <?php include('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-8 col-md-7">
            <?php 
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>

            <div class="card border-0 shadow-sm rounded-lg bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="font-weight-bold mb-1">Order History & Fulfillment</h4>
                        <p class="text-muted small mb-0">Track all your past and active furniture deliveries.</p>
                    </div>
                    <span class="badge badge-light border px-3 py-2 text-muted font-weight-bold">
                        <?php echo mysqli_num_rows($run); ?> Items Ordered
                    </span>
                </div>

                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($run) > 0): ?>
                        <div class="table-responsive">
                            <table class="table cart-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Order / Item</th>
                                        <th>Qty</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($run)): 
                                        $order_id      = (int)$row['order_id'];
                                        $order_invoice = htmlspecialchars($row['invoice_no']);
                                        $order_title   = htmlspecialchars($row['title'] ?? 'Custom Furniture Piece');
                                        $order_img     = htmlspecialchars($row['image'] ?? 'couch.jpg');
                                        $order_qty     = (int)$row['products_qty'];
                                        $order_amount  = number_format($row['product_amount']);
                                        $order_date    = htmlspecialchars($row['order_date']);
                                        $order_status  = strtolower($row['order_status']);
                                        $size          = htmlspecialchars($row['size'] ?? '');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../img/<?php echo $order_img; ?>" alt="<?php echo $order_title; ?>" class="rounded mr-3 border" style="width: 70px; height: 70px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1 font-weight-bold"><?php echo $order_title; ?></h6>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-file-invoice text-secondary mr-1"></i> #<?php echo $order_invoice; ?> &bull; <?php echo $order_date; ?>
                                                        </small>
                                                        <?php if ($size): ?>
                                                            <small class="text-muted"><i class="fas fa-ruler-combined mr-1"></i> <?php echo $size; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="font-weight-bold">
                                                x <?php echo $order_qty; ?>
                                            </td>
                                            <td class="font-weight-bold text-dark">
                                                PKR <?php echo $order_amount; ?>
                                            </td>
                                            <td>
                                                <?php if ($order_status == 'pending'): ?>
                                                    <span class="badge-status badge-status-pending">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                <?php elseif ($order_status == 'verified'): ?>
                                                    <span class="badge-status badge-status-verified">
                                                        <i class="fas fa-check-circle"></i> Confirmed
                                                    </span>
                                                <?php elseif ($order_status == 'delivered'): ?>
                                                    <span class="badge-status badge-status-delivered">
                                                        <i class="fas fa-truck"></i> Delivered
                                                    </span>
                                                <?php elseif ($order_status == 'cancelled'): ?>
                                                    <span class="badge badge-danger px-2 py-1">
                                                        <i class="fas fa-ban"></i> Cancelled
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($order_status); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($order_status !== 'cancelled'): ?>
                                                    <a href="../admin/invoice.php?invoice=<?php echo $order_invoice; ?>" target="_blank" class="btn btn-sm btn-outline-custom mb-1" title="View & Download PDF Invoice">
                                                        <i class="fas fa-download"></i> PDF
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($order_status === 'pending'): ?>
                                                    <a href="orders.php?cancel_order=<?php echo $order_id; ?>" class="btn btn-sm btn-outline-danger mb-1" title="Cancel this item" onclick="return confirm('Are you sure you want to cancel this order item?')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 px-3">
                            <div class="mb-3 text-muted" style="font-size: 3rem;">
                                <i class="fas fa-box-open text-warning"></i>
                            </div>
                            <h4 class="font-weight-bold">No orders placed yet</h4>
                            <p class="text-muted">Explore our curated collections and furnish your dream living spaces.</p>
                            <a href="../product.php" class="btn btn-accent-custom mt-2">
                                <i class="fas fa-shopping-bag mr-1"></i> Browse Products
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
