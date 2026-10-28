<?php 
include('include/header.php');

if (!isset($_SESSION['id'])) {
    header("location: sign-in.php");
    exit;
}

$customer_id = (int)$_SESSION['id'];
$edit_cart = isset($_GET['cart_id']) ? (int)$_GET['cart_id'] : 0;

if (isset($_POST['update'])) {
    $qty = max(1, (int)$_POST['Qty']);
    $up_query = "UPDATE cart SET quantity = $qty WHERE product_id = $edit_cart AND cust_id = $customer_id";
    if (mysqli_query($con, $up_query)) {
        $_SESSION['toast'] = "Cart item updated successfully!";
        header("location: cart.php");
        exit;
    }
}

// Fetch the specific cart item
$cart = "SELECT c.quantity, fp.pid, fp.title, fp.price, fp.size, fp.image 
         FROM cart c 
         INNER JOIN furniture_product fp ON c.product_id = fp.pid 
         WHERE c.cust_id = $customer_id AND c.product_id = $edit_cart";
$run  = mysqli_query($con, $cart);

if (!$run || mysqli_num_rows($run) == 0) {
    header("location: cart.php");
    exit;
}

$item = mysqli_fetch_assoc($run);
$pid   = (int)$item['pid'];
$title = htmlspecialchars($item['title']);
$price = (int)$item['price'];
$qty   = (int)$item['quantity'];
$size  = htmlspecialchars($item['size']);
$img1  = htmlspecialchars($item['image']);
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Adjust Item Quantity</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="cart.php">Shopping Bag</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Edit Item</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-lg bg-white p-4 p-md-5">
                <div class="d-flex align-items-center mb-4">
                    <img src="img/<?php echo $img1; ?>" alt="<?php echo $title; ?>" class="rounded mr-3 border" style="width: 90px; height: 90px; object-fit: cover;">
                    <div>
                        <h5 class="font-weight-bold mb-1"><?php echo $title; ?></h5>
                        <p class="text-muted small mb-0"><i class="fas fa-ruler-combined mr-1"></i> <?php echo $size; ?></p>
                        <p class="text-accent font-weight-bold mb-0">PKR <?php echo number_format($price); ?></p>
                    </div>
                </div>

                <form method="post">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small text-uppercase">Desired Quantity</label>
                        <input type="number" name="Qty" value="<?php echo $qty; ?>" min="1" max="99" class="form-control" required>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <a href="cart.php" class="btn btn-outline-custom">Cancel</a>
                        <button type="submit" name="update" class="btn btn-primary-custom px-4">
                            <i class="fas fa-check mr-1"></i> Update Quantity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
