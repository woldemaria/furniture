<?php 
include('include/header.php');

if (!isset($_SESSION['id'])) {
    header("location: sign-in.php?msg=checkout_login");
    exit;
}

$customer_id    = (int)$_SESSION['id'];
$customer_email = htmlspecialchars($_SESSION['email'] ?? ''); 
$customer_name  = htmlspecialchars($_SESSION['name'] ?? '');
$customer_add   = htmlspecialchars($_SESSION['add'] ?? '');  
$customer_city  = htmlspecialchars($_SESSION['city'] ?? ''); 
$customer_pcode = htmlspecialchars($_SESSION['pcode'] ?? '');
$customer_number= htmlspecialchars($_SESSION['number'] ?? '');

// Fetch items currently in cart
$cart_query = "SELECT c.quantity, fp.pid, fp.title, fp.price, fp.size, fp.image 
               FROM cart c 
               INNER JOIN furniture_product fp ON c.product_id = fp.pid 
               WHERE c.cust_id = $customer_id";
$cart_res = mysqli_query($con, $cart_query);

if (!$cart_res || mysqli_num_rows($cart_res) == 0) {
    echo "<div class='container my-5 py-5 text-center'>
            <div class='mb-4 text-warning' style='font-size: 3.5rem;'><i class='fas fa-shopping-bag'></i></div>
            <h3 class='serif-heading mb-2'>Your Shopping Bag is Empty</h3>
            <p class='text-muted mb-4'>Add furniture pieces to your bag before proceeding to checkout.</p>
            <a href='product.php' class='btn btn-accent-custom px-4'>Explore Collection</a>
          </div>";
    include('include/footer.php');
    exit;
}

// Compute totals
$sub_total = 0;
$cart_items = [];
while ($item = mysqli_fetch_assoc($cart_res)) {
    $cart_items[] = $item;
    $sub_total += ((int)$item['price'] * (int)$item['quantity']);
}

// Check coupon discount
$discount = 0;
$applied_coupon = $_SESSION['coupon'] ?? null;
if ($applied_coupon) {
    if ($applied_coupon['discount_type'] === 'percentage') {
        $discount = floor($sub_total * ($applied_coupon['discount_value'] / 100));
    } else {
        $discount = min($sub_total, (int)$applied_coupon['discount_value']);
    }
}

$shipping_cost = 0; // Free delivery
$grand_total = max(0, $sub_total - $discount + $shipping_cost);

// Handle Order Placement
$error = null;
if (isset($_POST['checkout'])) {
    $fullname = mysqli_real_escape_string($con, trim($_POST['fullname']));
    $address  = mysqli_real_escape_string($con, trim($_POST['address']));
    $city     = mysqli_real_escape_string($con, trim($_POST['city']));
    $code     = mysqli_real_escape_string($con, trim($_POST['code']));
    $number   = mysqli_real_escape_string($con, trim($_POST['phone_number']));

    if (!empty($fullname) && !empty($address) && !empty($city) && !empty($number)) {
        $invoice  = mt_rand(1000000000, 9999999999);
        $date     = date("d-m-Y");
        $all_success = true;

        // Calculate discount ratio per product so item totals sum up to grand_total
        $discount_ratio = ($sub_total > 0 && $discount > 0) ? ($discount / $sub_total) : 0;

        foreach ($cart_items as $item) {
            $pro_id    = (int)$item['pid'];
            $pro_qty   = (int)$item['quantity'];
            $pro_price = (int)$item['price'];
            $item_gross = $pro_price * $pro_qty;
            $item_discount = floor($item_gross * $discount_ratio);
            $item_net   = max(0, $item_gross - $item_discount);

            $insert_query = "INSERT INTO `customer_order` (
                `customer_id`, `customer_email`, `customer_fullname`, 
                `customer_address`, `customer_city`, `customer_pcode`, 
                `customer_phonenumber`, `product_id`, `product_amount`, 
                `invoice_no`, `products_qty`, `order_date`, `order_status`
            ) VALUES (
                $customer_id, '$customer_email', '$fullname', 
                '$address', '$city', '$code', 
                '$number', $pro_id, $item_net, 
                '$invoice', $pro_qty, '$date', 'pending'
            )";

            if (!mysqli_query($con, $insert_query)) {
                $all_success = false;
                break;
            }
        }

        if ($all_success) {
            // Empty the cart
            mysqli_query($con, "DELETE FROM cart WHERE cust_id = $customer_id");
            unset($_SESSION['coupon']);

            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show p-3' role='alert'>
                <h5 class='alert-heading font-weight-bold mb-1'><i class='fas fa-check-circle mr-2'></i> Order Placed Successfully!</h5>
                <p class='mb-0'>Thank you, <strong>" . htmlspecialchars($fullname) . "</strong>! Your invoice #<strong>$invoice</strong> has been registered. Your handcrafted furniture will arrive within 7 working days. Payment is Cash on Delivery.</p>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";

            header('location: customer/orders.php');
            exit;
        } else {
            $error = "There was an error saving your order. Please try again.";
        }
    } else {
        $error = "Please fill in all required shipping fields (*).";
    }
}
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Checkout & Shipping</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="cart.php">Cart</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Checkout</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role='alert'>
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Shipping Details Form Column -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-lg bg-white p-4 p-md-5">
                <div class="mb-4">
                    <h4 class="font-weight-bold mb-1">Delivery Destination</h4>
                    <p class="text-muted small">Where should our courier deliver your handcrafted furniture?</p>
                </div>

                <form method="post">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Account Email</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $customer_email; ?>" readonly disabled>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Full Recipient Name <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" placeholder="e.g. Ali Ahmed" value="<?php echo $customer_name; ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Street Address & Landmark <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" placeholder="House #, Street name, Area" value="<?php echo $customer_add; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Lahore, Karachi, Islamabad" value="<?php echo $customer_city; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Postal Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. 54000" value="<?php echo $customer_pcode; ?>">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase">Phone Number (For Delivery Rider) <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="e.g. 0300-1234567" value="<?php echo $customer_number; ?>" required>
                    </div>

                    <div class="card bg-light border p-3 rounded mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave text-success fa-2x mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-0">Payment Method: Cash On Delivery (COD)</h6>
                                <small class="text-muted">Pay in cash after inspecting your furniture in person upon doorstep arrival.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="checkout" class="btn btn-accent-custom btn-block py-3 font-weight-bold shadow-sm">
                        <i class="fas fa-check-circle mr-2"></i> Confirm & Place Order (PKR <?php echo number_format($grand_total); ?>)
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Items Preview Column -->
        <div class="col-lg-5">
            <div class="order-summary-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0">Order Summary</h5>
                    <a href="cart.php" class="text-primary small font-weight-bold">Edit Bag</a>
                </div>

                <div class="mb-3" style="max-height: 280px; overflow-y: auto;">
                    <?php foreach ($cart_items as $ci): 
                        $ci_title = htmlspecialchars($ci['title']);
                        $ci_qty   = (int)$ci['quantity'];
                        $ci_price = (int)$ci['price'];
                        $ci_img   = htmlspecialchars($ci['image']);
                    ?>
                        <div class="d-flex align-items-center py-2 border-bottom">
                            <img src="img/<?php echo $ci_img; ?>" alt="<?php echo $ci_title; ?>" class="rounded mr-3 border" style="width: 55px; height: 55px; object-fit: cover;">
                            <div class="flex-grow-1 mr-2">
                                <h6 class="mb-0 text-truncate font-weight-bold" style="max-width: 170px;"><?php echo $ci_title; ?></h6>
                                <small class="text-muted">Qty: <?php echo $ci_qty; ?> &times; PKR <?php echo number_format($ci_price); ?></small>
                            </div>
                            <span class="font-weight-bold text-dark small">PKR <?php echo number_format($ci_price * $ci_qty); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span class="font-weight-bold">PKR <?php echo number_format($sub_total); ?></span>
                </div>

                <?php if ($discount > 0 && $applied_coupon): ?>
                    <div class="summary-row text-success">
                        <span>Coupon (<?php echo htmlspecialchars($applied_coupon['code']); ?>):</span>
                        <span class="font-weight-bold">- PKR <?php echo number_format($discount); ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Doorstep Delivery:</span>
                    <span class="text-success font-weight-bold">FREE</span>
                </div>

                <div class="summary-total">
                    <span>Total Amount Due:</span>
                    <span class="text-accent">PKR <?php echo number_format($grand_total); ?></span>
                </div>

                <div class="mt-4 pt-3 border-top text-muted small text-center">
                    <i class="fas fa-truck text-warning mr-1"></i> Arrives within 7 working days nationwide
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
