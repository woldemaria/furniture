<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$error = null;

// Handle coupon toggle status
if (isset($_GET['toggle_id'])) {
    $tid = (int)$_GET['toggle_id'];
    $current_st = $_GET['current'] === 'active' ? 'inactive' : 'active';
    mysqli_query($con, "UPDATE coupons SET status = '$current_st' WHERE id = $tid");
    $_SESSION['admin_toast'] = "Coupon status updated to $current_st.";
    header('location: coupons.php');
    exit;
}

// Handle coupon delete
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];
    mysqli_query($con, "DELETE FROM coupons WHERE id = $del_id");
    $_SESSION['admin_toast'] = "Coupon deleted successfully.";
    header('location: coupons.php');
    exit;
}

// Handle create new coupon
if (isset($_POST['add_coupon'])) {
    $code      = mysqli_real_escape_string($con, strtoupper(trim($_POST['code'] ?? '')));
    $type      = $_POST['discount_type'] === 'fixed' ? 'fixed' : 'percentage';
    $val       = max(1, (int)($_POST['discount_value'] ?? 0));
    $min_order = max(0, (int)($_POST['min_order'] ?? 0));

    if (!empty($code) && $val > 0) {
        $check = mysqli_query($con, "SELECT id FROM coupons WHERE code = '$code'");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = "A coupon with this code already exists.";
        } else {
            $ins = "INSERT INTO coupons (`code`, `discount_type`, `discount_value`, `min_order`, `status`) 
                    VALUES ('$code', '$type', $val, $min_order, 'active')";
            if (mysqli_query($con, $ins)) {
                $_SESSION['admin_toast'] = "Promo coupon $code created successfully!";
                header('location: coupons.php');
                exit;
            } else {
                $error = "Database error creating coupon.";
            }
        }
    } else {
        $error = "Coupon code and positive discount value are required.";
    }
}

// Fetch all coupons
$run = mysqli_query($con, "SELECT * FROM coupons ORDER BY id DESC");
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="admin-header-icon mr-3 text-warning bg-light">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Promotional Coupons</h2>
                        <small class="text-muted">Create discount codes, set thresholds, and track promo offers</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Create Coupon Form Box -->
                <div class="card bg-light border-0 p-4 rounded mb-4">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-plus-circle text-primary mr-1"></i> Create New Discount Code</h6>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Coupon Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" placeholder="e.g. FLASH15" style="text-transform: uppercase;" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Discount Type</label>
                                <select name="discount_type" class="form-control">
                                    <option value="percentage">Percentage Off (%)</option>
                                    <option value="fixed">Fixed Amount (PKR)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Discount Value <span class="text-danger">*</span></label>
                                <input type="number" name="discount_value" class="form-control" placeholder="e.g. 15 or 3000" min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Min Order (PKR)</label>
                                <input type="number" name="min_order" class="form-control" placeholder="0 = No minimum" min="0" value="0">
                            </div>
                        </div>

                        <button type="submit" name="add_coupon" class="btn btn-primary px-4 mt-2">
                            <i class="fas fa-check mr-1"></i> Save Coupon Code
                        </button>
                    </form>
                </div>

                <!-- Coupons Table -->
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Coupon Code</th>
                                <th>Discount</th>
                                <th>Min. Order</th>
                                <th>Created Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($run && mysqli_num_rows($run) > 0): ?>
                                <?php while ($cp = mysqli_fetch_assoc($run)): 
                                    $cid   = (int)$cp['id'];
                                    $code  = htmlspecialchars($cp['code']);
                                    $type  = $cp['discount_type'];
                                    $val   = (int)$cp['discount_value'];
                                    $min   = (int)$cp['min_order'];
                                    $st    = $cp['status'];
                                    $cdate = date("M j, Y", strtotime($cp['created_at']));
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted">#<?php echo $cid; ?></td>
                                        <td>
                                            <span class="badge badge-warning text-dark font-weight-bold px-3 py-2" style="letter-spacing: 0.05em; font-size: 0.9rem;">
                                                <i class="fas fa-tag mr-1"></i> <?php echo $code; ?>
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            <?php echo ($type === 'percentage') ? ($val . '% Off') : ('PKR ' . number_format($val) . ' Off'); ?>
                                        </td>
                                        <td>
                                            <?php echo ($min > 0) ? ('PKR ' . number_format($min)) : '<span class="text-muted">None</span>'; ?>
                                        </td>
                                        <td class="small text-muted"><?php echo $cdate; ?></td>
                                        <td>
                                            <?php if ($st === 'active'): ?>
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-pause-circle mr-1"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="coupons.php?toggle_id=<?php echo $cid; ?>&current=<?php echo $st; ?>" class="btn btn-sm btn-outline-secondary mr-1" title="Toggle Status">
                                                <i class="fas fa-power-off"></i> <?php echo ($st === 'active') ? 'Deactivate' : 'Activate'; ?>
                                            </a>
                                            <a href="coupons.php?del=<?php echo $cid; ?>" class="btn btn-sm btn-outline-danger" title="Delete Coupon" onclick="return confirm('Delete this coupon code permanently?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No coupon codes created yet.</td>
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
