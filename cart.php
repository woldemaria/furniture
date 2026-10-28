<?php 
include('include/header.php'); 

// Check customer authentication
if (!isset($_SESSION['id'])) {
    ?>
    <div class="page-banner">
        <div class="container">
            <h1 class="page-banner-title">Shopping Bag</h1>
            <div class="page-breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right fa-xs"></i>
                <span>Cart</span>
            </div>
        </div>
    </div>
    <div class="container my-5 py-5 text-center">
        <div class="mb-4 text-muted" style="font-size: 3.5rem;">
            <i class="fas fa-user-lock text-warning"></i>
        </div>
        <h3 class="serif-heading mb-2">Sign In to View Your Shopping Bag</h3>
        <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
            Log in to your account to review items in your cart, track deliveries, or proceed to checkout.
        </p>
        <div>
            <a href="sign-in.php" class="btn btn-primary-custom px-4 mr-2">
                <i class="fas fa-sign-in-alt mr-1"></i> Sign In Now
            </a>
            <a href="product.php" class="btn btn-outline-custom px-4">
                Browse Collection
            </a>
        </div>
    </div>
    <?php
    include('include/footer.php');
    exit;
}

$customer_id = (int)$_SESSION['id'];

// Handle item removal
if (isset($_GET['del_pid'])) {
    $proid = (int)$_GET['del_pid'];
    $del_query = "DELETE FROM cart WHERE product_id = $proid AND cust_id = $customer_id";
    if (mysqli_query($con, $del_query)) {
        $_SESSION['toast'] = "Item removed from cart.";
        header("location: cart.php");
        exit;
    }
}

// Handle inline quantity update
if (isset($_POST['update_cart_qty'])) {
    $p_id = (int)$_POST['product_id'];
    $new_qty = max(1, (int)$_POST['quantity']);
    $up_query = "UPDATE cart SET quantity = $new_qty WHERE product_id = $p_id AND cust_id = $customer_id";
    mysqli_query($con, $up_query);
    $_SESSION['toast'] = "Cart updated successfully!";
    header("location: cart.php");
    exit;
}

// Fetch Cart Items
$cart_query = "SELECT c.quantity, fp.pid, fp.title, fp.price, fp.size, fp.image 
               FROM cart c 
               INNER JOIN furniture_product fp ON c.product_id = fp.pid 
               WHERE c.cust_id = $customer_id 
               ORDER BY c.cart_id DESC";
$run = mysqli_query($con, $cart_query);

$sub_total = 0;
$items_count = 0;
$cart_items = [];

if ($run && mysqli_num_rows($run) > 0) {
    while ($row = mysqli_fetch_assoc($run)) {
        $cart_items[] = $row;
        $sub_total += ((int)$row['price'] * (int)$row['quantity']);
        $items_count += (int)$row['quantity'];
    }
}

// Handle Coupon Application
$coupon_msg = null;
$coupon_error = null;

if (isset($_POST['apply_coupon'])) {
    $coupon_code = mysqli_real_escape_string($con, strtoupper(trim($_POST['coupon_code'] ?? '')));
    if (!empty($coupon_code)) {
        $cp_query = "SELECT * FROM coupons WHERE code = '$coupon_code' AND status = 'active' LIMIT 1";
        $cp_run   = mysqli_query($con, $cp_query);

        if ($cp_run && mysqli_num_rows($cp_run) > 0) {
            $coupon = mysqli_fetch_assoc($cp_run);
            $min_order = (int)$coupon['min_order'];

            if ($sub_total >= $min_order) {
                $discount_amount = 0;
                if ($coupon['discount_type'] === 'percentage') {
                    $discount_amount = floor($sub_total * ((int)$coupon['discount_value'] / 100));
                } else {
                    $discount_amount = min($sub_total, (int)$coupon['discount_value']);
                }

                $_SESSION['coupon'] = [
                    'code'           => $coupon['code'],
                    'discount_type'  => $coupon['discount_type'],
                    'discount_value' => (int)$coupon['discount_value'],
                    'discount_amount'=> $discount_amount
                ];
                $_SESSION['toast'] = "Promo coupon applied successfully!";
                header("location: cart.php");
                exit;
            } else {
                $coupon_error = "This coupon requires a minimum subtotal of PKR " . number_format($min_order) . ".";
            }
        } else {
            $coupon_error = "Invalid or expired promo code.";
        }
    } else {
        $coupon_error = "Please enter a coupon code.";
    }
}

// Handle Coupon Removal
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon']);
    $_SESSION['toast'] = "Coupon code removed.";
    header("location: cart.php");
    exit;
}

// Calculate discount if session coupon exists
$discount = 0;
$applied_coupon = $_SESSION['coupon'] ?? null;
if ($applied_coupon) {
    // Re-verify against current subtotal
    if ($applied_coupon['discount_type'] === 'percentage') {
        $discount = floor($sub_total * ($applied_coupon['discount_value'] / 100));
    } else {
        $discount = min($sub_total, $applied_coupon['discount_value']);
    }
    $_SESSION['coupon']['discount_amount'] = $discount;
}

$shipping_cost = 0; // Free nationwide shipping
$grand_total = max(0, $sub_total - $discount + $shipping_cost);
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Shopping Bag</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Cart</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if (!empty($cart_items)): ?>
        <div class="row">
            <!-- Cart Items Table Column -->
            <div class="col-lg-8 mb-4">
                <div class="cart-card">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold mb-0">Your Selected Pieces</h5>
                        <a href="product.php" class="text-primary small font-weight-bold">
                            <i class="fas fa-plus fa-xs mr-1"></i> Add More Items
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table cart-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $row): 
                                    $pid   = (int)$row['pid'];
                                    $title = htmlspecialchars($row['title']);
                                    $price = (int)$row['price'];
                                    $qty   = (int)$row['quantity'];
                                    $size  = htmlspecialchars($row['size']);
                                    $img1  = htmlspecialchars($row['image']);
                                    $item_total = $price * $qty;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="product-detail.php?product_id=<?php echo $pid; ?>">
                                                    <img src="img/<?php echo $img1; ?>" alt="<?php echo $title; ?>" class="rounded mr-3 border" style="width: 80px; height: 80px; object-fit: cover;">
                                                </a>
                                                <div>
                                                    <h6 class="mb-1 font-weight-bold">
                                                        <a href="product-detail.php?product_id=<?php echo $pid; ?>" class="text-dark"><?php echo $title; ?></a>
                                                    </h6>
                                                    <?php if ($size): ?>
                                                        <small class="text-muted d-block"><i class="fas fa-ruler-combined mr-1"></i> <?php echo $size; ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold">
                                            PKR <?php echo number_format($price); ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-flex align-items-center">
                                                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                                <input type="number" name="quantity" value="<?php echo $qty; ?>" min="1" max="99" class="form-control text-center form-control-sm mr-2" style="width: 65px;" onchange="this.form.submit()">
                                                <button type="submit" name="update_cart_qty" class="btn btn-sm btn-light border" title="Update Quantity">
                                                    <i class="fas fa-sync-alt fa-xs text-muted"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            PKR <?php echo number_format($item_total); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="cart.php?del_pid=<?php echo $pid; ?>" class="btn btn-sm btn-outline-danger" title="Remove Item" onclick="return confirm('Remove this piece from your bag?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap">
                        <a href="product.php" class="btn btn-outline-custom btn-sm mb-2 mb-sm-0">
                            <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
                        </a>
                        <span class="text-muted small">
                            <i class="fas fa-check-circle text-success mr-1"></i> Free nationwide delivery included
                        </span>
                    </div>
                </div>

                <!-- Promo Coupon Form Box -->
                <div class="card border-0 shadow-sm rounded-lg bg-white p-4 mt-4">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-tag text-accent mr-2"></i> Have a Promotional Coupon?</h6>
                    <p class="text-muted small mb-3">Try codes: <code class="text-primary font-weight-bold">WELCOME10</code> (10% off), <code class="text-primary font-weight-bold">SAVE20</code> (20% off over PKR 50k), or <code class="text-primary font-weight-bold">FURNI5000</code> (PKR 5,000 off over PKR 30k).</p>
                    
                    <?php if ($coupon_error): ?>
                        <div class="alert alert-danger alert-dismissible fade show small mb-3">
                            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($coupon_error); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="d-flex flex-wrap gap-2" style="max-width: 480px;">
                        <input type="text" name="coupon_code" class="form-control mr-2" placeholder="Enter coupon code" style="text-transform: uppercase; max-width: 260px;" value="<?php echo htmlspecialchars($applied_coupon['code'] ?? ''); ?>">
                        <button type="submit" name="apply_coupon" class="btn btn-primary-custom">
                            Apply Coupon
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary Column -->
            <div class="col-lg-4">
                <div class="order-summary-card">
                    <h5 class="font-weight-bold mb-3">Order Summary</h5>
                    
                    <div class="summary-row">
                        <span>Items Count:</span>
                        <span class="font-weight-bold"><?php echo $items_count; ?> unit(s)</span>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span class="font-weight-bold">PKR <?php echo number_format($sub_total); ?></span>
                    </div>

                    <?php if ($discount > 0 && $applied_coupon): ?>
                        <div class="summary-row text-success">
                            <span>
                                Coupon Discount (<?php echo htmlspecialchars($applied_coupon['code']); ?>):
                                <a href="cart.php?remove_coupon=1" class="text-danger small ml-1" title="Remove coupon"><i class="fas fa-times-circle"></i></a>
                            </span>
                            <span class="font-weight-bold">- PKR <?php echo number_format($discount); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span class="text-success font-weight-bold">FREE</span>
                    </div>

                    <div class="summary-total">
                        <span>Grand Total:</span>
                        <span class="text-accent">PKR <?php echo number_format($grand_total); ?></span>
                    </div>

                    <div class="mt-4">
                        <a href="checkout.php" class="btn btn-accent-custom btn-block py-3 font-weight-bold shadow-sm">
                            Proceed to Checkout <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    <div class="mt-4 pt-3 border-top text-center text-muted small">
                        <div class="mb-2"><i class="fas fa-lock text-success mr-1"></i> Safe & Secure Checkout</div>
                        <div><i class="fas fa-money-bill-wave text-warning mr-1"></i> Cash on Delivery Available</div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-lg bg-white text-center py-5 px-3">
            <div class="mb-4 text-muted" style="font-size: 4rem;">
                <i class="fas fa-shopping-bag text-warning"></i>
            </div>
            <h3 class="serif-heading mb-2">Your Shopping Bag is Empty</h3>
            <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                Looks like you haven't added any pieces to your cart yet. Explore our handcrafted furniture catalog to discover what inspires you.
            </p>
            <div>
                <a href="product.php" class="btn btn-accent-custom px-4 py-2 font-weight-bold">
                    <i class="fas fa-couch mr-2"></i> Start Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
if (isset($_SESSION['toast'])) {
    $t_msg = $_SESSION['toast'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('success', '" . addslashes($t_msg) . "');
        });
    </script>";
    unset($_SESSION['toast']);
}

include('include/footer.php'); 
?>
