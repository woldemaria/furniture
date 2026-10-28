<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$fur_order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order_query  = "SELECT co.*, fp.title, fp.image, fp.size, cat.category as cat_name 
                 FROM customer_order co
                 LEFT JOIN furniture_product fp ON co.product_id = fp.pid
                 LEFT JOIN categories cat ON fp.category = cat.id
                 WHERE co.order_id = $fur_order_id";
$run = mysqli_query($con, $order_query);

if (!$run || mysqli_num_rows($run) == 0) {
    header('location: pending_furniture_pro.php');
    exit;
}

$order = mysqli_fetch_assoc($run);
$order_id      = (int)$order['order_id'];
$order_invoice = htmlspecialchars($order['invoice_no']);
$cust_id       = (int)$order['customer_id'];
$cust_fullname = htmlspecialchars($order['customer_fullname']);
$cust_email    = htmlspecialchars($order['customer_email']);
$cust_phone    = htmlspecialchars($order['customer_phonenumber']);
$cust_address  = htmlspecialchars($order['customer_address']);
$cust_city     = htmlspecialchars($order['customer_city']);
$cust_pcode    = htmlspecialchars($order['customer_pcode']);
$order_pro_id  = (int)$order['product_id'];
$order_qty     = (int)$order['products_qty'];
$order_amount  = (int)$order['product_amount'];
$order_date    = htmlspecialchars($order['order_date']);
$order_status  = strtolower($order['order_status']);
$title         = htmlspecialchars($order['title'] ?? 'Custom Product');
$image         = htmlspecialchars($order['image'] ?? 'couch.jpg');
$category      = htmlspecialchars(ucfirst($order['cat_name'] ?? 'General'));

$msg = null;
$error = null;

if (isset($_POST['update'])) { 
    $new_status = mysqli_real_escape_string($con, strtolower(trim($_POST['status'])));
    
    if (in_array($new_status, ['pending', 'verified', 'delivered', 'cancelled'])) {
        $up_query = "UPDATE customer_order SET order_status = '$new_status' WHERE order_id = $order_id";     
        if (mysqli_query($con, $up_query)) {
            $_SESSION['admin_toast'] = "Order #$order_id status changed to " . ucfirst($new_status) . "!";
            if ($new_status == 'pending') {
                header("location: pending_furniture_pro.php");
            } elseif ($new_status == 'verified') {
                header("location: verified_furniture_pro.php");
            } elseif ($new_status == 'delivered') {
                header("location: delivered_furniture_pro.php");
            } else {
                header("location: index.php");
            }
            exit;
        } else {
            $error = "Failed to update order status.";
        }
    }
}
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="admin-header-icon mr-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Manage Order #<?php echo $order_id; ?></h2>
                        <small class="text-muted">Invoice #: <strong><?php echo $order_invoice; ?></strong> &bull; Order Date: <?php echo $order_date; ?></small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <!-- Order Items & Customer Overview -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 p-3 rounded h-100">
                                <h6 class="font-weight-bold text-muted text-uppercase mb-2"><i class="fas fa-user mr-1"></i> Customer Information</h6>
                                <p class="mb-1 font-weight-bold"><?php echo $cust_fullname; ?> <small class="text-muted">(Customer #<?php echo $cust_id; ?>)</small></p>
                                <p class="mb-1 small text-muted"><i class="fas fa-envelope mr-1"></i> <?php echo $cust_email; ?></p>
                                <p class="mb-1 small text-muted"><i class="fas fa-phone mr-1"></i> <?php echo $cust_phone; ?></p>
                                <p class="mb-0 small text-muted"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo "$cust_address, $cust_city $cust_pcode"; ?></p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 p-3 rounded h-100">
                                <h6 class="font-weight-bold text-muted text-uppercase mb-2"><i class="fas fa-couch mr-1"></i> Product Information</h6>
                                <div class="d-flex align-items-center">
                                    <img src="img/<?php echo $image; ?>" alt="<?php echo $title; ?>" class="rounded mr-3 border" style="width: 70px; height: 70px; object-fit: cover;">
                                    <div>
                                        <h6 class="font-weight-bold mb-1"><?php echo $title; ?></h6>
                                        <small class="text-muted d-block">Category: <?php echo $category; ?> &bull; Product ID: #<?php echo $order_pro_id; ?></small>
                                        <span class="font-weight-bold text-dark">Qty: <?php echo $order_qty; ?> &times; Total: PKR <?php echo number_format($order_amount); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Selection -->
                    <div class="card border p-4 rounded mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="font-weight-bold small text-muted text-uppercase d-block">Update Fulfillment Status</label>
                                <select class="form-control" name="status">
                                    <option value="pending" <?php echo ($order_status == 'pending') ? 'selected' : ''; ?>>Pending (Awaiting Confirmation)</option>
                                    <option value="verified" <?php echo ($order_status == 'verified') ? 'selected' : ''; ?>>Verified (Dispatched for Delivery)</option>
                                    <option value="delivered" <?php echo ($order_status == 'delivered') ? 'selected' : ''; ?>>Delivered (Completed & Paid)</option>
                                    <option value="cancelled" <?php echo ($order_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled (Void)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold small text-muted text-uppercase d-block">Official Invoice</label>
                                <a href="invoice.php?invoice=<?php echo $order_invoice; ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf mr-1"></i> Download PDF Invoice
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" name="update" class="btn btn-primary px-4 py-2 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save Order Status
                        </button>
                        <a href="pending_furniture_pro.php" class="btn btn-outline-secondary ml-2">Back to Orders</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
