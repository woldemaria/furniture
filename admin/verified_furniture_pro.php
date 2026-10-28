<?php 
require_once("include/header.php");

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$order_query = "SELECT co.*, fp.title, fp.image, fp.size, cat.category as cat_name 
                FROM customer_order co
                LEFT JOIN furniture_product fp ON co.product_id = fp.pid
                LEFT JOIN categories cat ON fp.category = cat.id
                WHERE co.order_status='verified' 
                ORDER BY co.order_id DESC";
$run = mysqli_query($con, $order_query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once("include/sidebar.php"); ?>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="admin-header-icon mr-3 text-info bg-light">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Verified Orders</h2>
                        <small class="text-muted">Confirmed orders dispatched for packaging and delivery</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer & Contact</th>
                                <th>Product Item</th>
                                <th>Qty</th>
                                <th>Total (PKR)</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($run && mysqli_num_rows($run) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($run)): 
                                    $order_id      = (int)$row['order_id'];
                                    $order_invoice = htmlspecialchars($row['invoice_no']);
                                    $cust_name     = htmlspecialchars($row['customer_fullname']);
                                    $cust_phone    = htmlspecialchars($row['customer_phonenumber']);
                                    $cust_city     = htmlspecialchars($row['customer_city']);
                                    $title         = htmlspecialchars($row['title'] ?? 'Custom Product');
                                    $image         = htmlspecialchars($row['image'] ?? 'couch.jpg');
                                    $qty           = (int)$row['products_qty'];
                                    $amount        = number_format($row['product_amount']);
                                    $date          = htmlspecialchars($row['order_date']);
                                ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-primary">#<?php echo $order_invoice; ?></span>
                                            <small class="d-block text-muted">ID: <?php echo $order_id; ?></small>
                                        </td>
                                        <td>
                                            <strong class="d-block"><?php echo $cust_name; ?></strong>
                                            <small class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i> <?php echo $cust_phone; ?></small><br>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt fa-xs mr-1"></i> <?php echo $cust_city; ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="img/<?php echo $image; ?>" alt="<?php echo $title; ?>" class="rounded mr-2 border" style="width: 50px; height: 50px; object-fit: cover;">
                                                <span class="small font-weight-bold" style="max-width: 140px;"><?php echo $title; ?></span>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold">x <?php echo $qty; ?></td>
                                        <td class="font-weight-bold text-dark">PKR <?php echo $amount; ?></td>
                                        <td class="small text-muted"><?php echo $date; ?></td>
                                        <td>
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-check mr-1"></i> Verified</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="invoice.php?invoice=<?php echo $order_invoice; ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-1" title="Download PDF Invoice">
                                                <i class="fas fa-file-invoice"></i> PDF
                                            </a>
                                            <a href="edit_furn_verify_pen.php?order_id=<?php echo $order_id; ?>" class="btn btn-sm btn-outline-secondary mb-1" title="Change Status">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No verified orders at this time.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("include/footer.php"); ?>
