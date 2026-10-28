<?php 
include('include/header.php');

if (!isset($_SESSION['id'])) {
    ?>
    <div class="page-banner">
        <div class="container">
            <h1 class="page-banner-title">My Wishlist</h1>
            <div class="page-breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right fa-xs"></i>
                <span>Wishlist</span>
            </div>
        </div>
    </div>
    <div class="container my-5 py-5 text-center">
        <div class="mb-4 text-muted" style="font-size: 3.5rem;">
            <i class="fas fa-heart text-danger"></i>
        </div>
        <h3 class="serif-heading mb-2">Save Your Favorite Furniture</h3>
        <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
            Please sign in to keep track of your dream living room sets, sofas, and tables across devices.
        </p>
        <div>
            <a href="sign-in.php" class="btn btn-primary-custom px-4 mr-2">
                <i class="fas fa-sign-in-alt mr-1"></i> Sign In
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

// Handle Add to Wishlist
if (isset($_GET['add_wishlist'])) {
    $pid = (int)$_GET['add_wishlist'];
    $check_item = mysqli_query($con, "SELECT pid FROM furniture_product WHERE pid = $pid AND status = 'publish'");
    if ($check_item && mysqli_num_rows($check_item) > 0) {
        $ins = mysqli_query($con, "INSERT IGNORE INTO wishlist (cust_id, product_id) VALUES ($customer_id, $pid)");
        $_SESSION['toast'] = "Added to your wishlist!";
    }
    $back = $_SERVER['HTTP_REFERER'] ?? 'wishlist.php';
    header("location: $back");
    exit;
}

// Handle Remove from Wishlist
if (isset($_GET['remove_wishlist'])) {
    $pid = (int)$_GET['remove_wishlist'];
    mysqli_query($con, "DELETE FROM wishlist WHERE cust_id = $customer_id AND product_id = $pid");
    $_SESSION['toast'] = "Item removed from wishlist.";
    header("location: wishlist.php");
    exit;
}

// Handle Move to Cart
if (isset($_GET['move_to_cart'])) {
    $pid = (int)$_GET['move_to_cart'];

    // Check if product in cart
    $cart_check = mysqli_query($con, "SELECT * FROM cart WHERE cust_id = $customer_id AND product_id = $pid");
    if (mysqli_num_rows($cart_check) == 0) {
        mysqli_query($con, "INSERT INTO cart (cust_id, product_id, quantity) VALUES ($customer_id, $pid, 1)");
    }

    // Remove from wishlist
    mysqli_query($con, "DELETE FROM wishlist WHERE cust_id = $customer_id AND product_id = $pid");
    $_SESSION['toast'] = "Moved to your shopping bag!";
    header("location: cart.php");
    exit;
}

// Fetch Customer's Wishlist Items
$query = "SELECT w.wishlist_id, fp.*, c.category as cat_name 
          FROM wishlist w 
          INNER JOIN furniture_product fp ON w.product_id = fp.pid 
          LEFT JOIN categories c ON fp.category = c.id 
          WHERE w.cust_id = $customer_id 
          ORDER BY w.wishlist_id DESC";
$run = mysqli_query($con, $query);
$count = mysqli_num_rows($run);
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">My Wishlist</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Favorites</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($run && $count > 0): ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div>
                <h4 class="font-weight-bold mb-1">Saved Furniture Pieces</h4>
                <p class="text-muted small mb-0">You have <strong><?php echo $count; ?></strong> piece(s) saved for later.</p>
            </div>
            <a href="product.php" class="btn btn-outline-custom btn-sm mt-2 mt-sm-0">
                <i class="fas fa-couch mr-1"></i> Add More Pieces
            </a>
        </div>

        <div class="row">
            <?php while ($p = mysqli_fetch_assoc($run)): 
                $pid      = (int)$p['pid'];
                $ptitle   = htmlspecialchars($p['title']);
                $pcat_name= htmlspecialchars(ucfirst($p['cat_name'] ?? 'Furniture'));
                $p_price  = number_format($p['price']);
                $size     = htmlspecialchars($p['size']);
                $img1     = htmlspecialchars($p['image']);
            ?>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-thumb-wrap">
                            <span class="product-badge-cat"><?php echo $pcat_name; ?></span>
                            <a href="product-detail.php?product_id=<?php echo $pid; ?>">
                                <img src="img/<?php echo $img1; ?>" alt="<?php echo $ptitle; ?>" loading="lazy">
                            </a>
                        </div>
                        
                        <div class="product-body">
                            <h4 class="product-title" title="<?php echo $ptitle; ?>">
                                <a href="product-detail.php?product_id=<?php echo $pid; ?>"><?php echo $ptitle; ?></a>
                            </h4>
                            <?php if ($size): ?>
                                <div class="product-dimension"><i class="fas fa-ruler-combined mr-1"></i> <?php echo $size; ?></div>
                            <?php endif; ?>
                            
                            <div class="product-price">
                                <span class="currency">PKR</span> <?php echo $p_price; ?>
                            </div>
                            
                            <div class="product-actions mt-2 flex-column">
                                <a href="wishlist.php?move_to_cart=<?php echo $pid; ?>" class="btn-card-cart w-100 mb-2">
                                    <i class="fas fa-shopping-bag mr-1"></i> Move to Bag
                                </a>
                                <div class="d-flex w-100 gap-2">
                                    <a href="product-detail.php?product_id=<?php echo $pid; ?>" class="btn-card-view flex-grow-1" title="View Details">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </a>
                                    <a href="wishlist.php?remove_wishlist=<?php echo $pid; ?>" class="btn btn-sm btn-outline-danger" title="Remove from Wishlist">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-lg bg-white text-center py-5 px-3">
            <div class="mb-4 text-muted" style="font-size: 4rem;">
                <i class="far fa-heart text-danger"></i>
            </div>
            <h3 class="serif-heading mb-2">Your Wishlist is Empty</h3>
            <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                Explore our catalog and click the heart icon on any sofa, bed, or table to save it here for later.
            </p>
            <div>
                <a href="product.php" class="btn btn-accent-custom px-4 py-2 font-weight-bold">
                    <i class="fas fa-couch mr-2"></i> Explore Collections
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
