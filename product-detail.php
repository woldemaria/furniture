<?php 
include('include/header.php');

$p_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Fetch product details
$pdetail_query = "SELECT fp.*, c.category as cat_name FROM furniture_product fp 
                  LEFT JOIN categories c ON fp.category = c.id 
                  WHERE fp.pid = $p_id";
$pdetail_run   = mysqli_query($con, $pdetail_query);

if (!$pdetail_run || mysqli_num_rows($pdetail_run) == 0) {
    echo "<div class='container my-5 py-5 text-center'>
            <h2 class='serif-heading mb-3'>Product Not Found</h2>
            <p class='text-muted mb-4'>The furniture item you are looking for is no longer available or does not exist.</p>
            <a href='product.php' class='btn btn-primary-custom'>Browse All Products</a>
          </div>";
    include('include/footer.php');
    exit;
}

$p = mysqli_fetch_assoc($pdetail_run);
$pid        = (int)$p['pid'];
$title      = htmlspecialchars($p['title']);
$category_id= (int)$p['category'];
$cat_name   = htmlspecialchars(ucfirst($p['cat_name'] ?? 'Furniture'));
$detail     = $p['detail'];
$price      = (int)$p['price'];
$formatted_price = number_format($price);
$size       = htmlspecialchars($p['size']);
$img1       = htmlspecialchars($p['image']);

// Check if item is in customer's wishlist
$in_wishlist = false;
if (isset($_SESSION['id'])) {
    $cust_id = (int)$_SESSION['id'];
    $w_check = mysqli_query($con, "SELECT wishlist_id FROM wishlist WHERE cust_id = $cust_id AND product_id = $pid");
    if ($w_check && mysqli_num_rows($w_check) > 0) {
        $in_wishlist = true;
    }
}

// Handle Add to Cart submission
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['email'])) {
        header("location: sign-in.php?msg=login_required");
        exit;
    } else {
        $custid = (int)$_SESSION['id'];
        $qty    = max(1, (int)($_POST['qty'] ?? 1));

        $sel_cart = "SELECT * FROM cart WHERE cust_id = $custid AND product_id = $pid";
        $run_cart = mysqli_query($con, $sel_cart);

        if (mysqli_num_rows($run_cart) == 0) {
            $cart_query = "INSERT INTO `cart`(`cust_id`, `product_id`, `quantity`) VALUES ($custid, $pid, $qty)";
            if (mysqli_query($con, $cart_query)) {
                $_SESSION['toast'] = "Added $qty item(s) to your shopping bag!";
            }
        } else {
            $cart_row = mysqli_fetch_assoc($run_cart);
            $new_qty = (int)$cart_row['quantity'] + $qty;
            mysqli_query($con, "UPDATE cart SET quantity = $new_qty WHERE cust_id = $custid AND product_id = $pid");
            $_SESSION['toast'] = "Updated bag quantity to $new_qty!";
        }
        header("location: product-detail.php?product_id=$pid");
        exit;
    }
}

// Handle Review Submission
$review_error = null;
if (isset($_POST['submit_review'])) {
    if (!isset($_SESSION['email'])) {
        header("location: sign-in.php?msg=login_required");
        exit;
    } else {
        $cust_id   = (int)$_SESSION['id'];
        $cust_name = mysqli_real_escape_string($con, $_SESSION['name'] ?? 'Customer');
        $rating    = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $r_title   = mysqli_real_escape_string($con, trim($_POST['review_title'] ?? ''));
        $r_text    = mysqli_real_escape_string($con, trim($_POST['review_text'] ?? ''));

        if (!empty($r_title) && !empty($r_text)) {
            $ins_review = "INSERT INTO `reviews` (`product_id`, `customer_id`, `customer_name`, `rating`, `review_title`, `review_text`) 
                           VALUES ($pid, $cust_id, '$cust_name', $rating, '$r_title', '$r_text')";
            if (mysqli_query($con, $ins_review)) {
                $_SESSION['toast'] = "Thank you! Your verified review has been posted.";
                header("location: product-detail.php?product_id=$pid#reviewsSection");
                exit;
            } else {
                $review_error = "Failed to submit review.";
            }
        } else {
            $review_error = "Please fill in both a review title and comment.";
        }
    }
}

// Fetch Reviews & Metrics
$reviews_query = "SELECT * FROM reviews WHERE product_id = $pid AND status = 'approved' ORDER BY review_id DESC";
$reviews_run   = mysqli_query($con, $reviews_query);
$reviews_count = mysqli_num_rows($reviews_run);

$avg_rating = 5.0;
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if ($reviews_count > 0) {
    $total_stars = 0;
    $reviews_list = [];
    while ($r = mysqli_fetch_assoc($reviews_run)) {
        $reviews_list[] = $r;
        $total_stars += (int)$r['rating'];
        $r_val = (int)$r['rating'];
        if (isset($rating_counts[$r_val])) {
            $rating_counts[$r_val]++;
        }
    }
    $avg_rating = round($total_stars / $reviews_count, 1);
} else {
    $reviews_list = [];
}
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title"><?php echo $title; ?></h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="product.php">Collection</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="product.php?cat_id=<?php echo $category_id; ?>"><?php echo $cat_name; ?></a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span><?php echo $title; ?></span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Product Image Gallery Box -->
        <div class="col-lg-6 mb-4">
            <div class="product-detail-img-box text-center position-relative">
                <!-- Wishlist Toggle Button -->
                <a href="<?php echo isset($_SESSION['id']) ? ($in_wishlist ? "wishlist.php?remove_wishlist=$pid" : "wishlist.php?add_wishlist=$pid") : "sign-in.php"; ?>" 
                   class="btn position-absolute rounded-circle shadow-sm <?php echo $in_wishlist ? 'btn-danger' : 'btn-light border'; ?>" 
                   style="top: 25px; right: 25px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; z-index: 5;"
                   title="<?php echo $in_wishlist ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>">
                    <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart fa-lg"></i>
                </a>

                <img src="img/<?php echo $img1; ?>" alt="<?php echo $title; ?>" id="mainProductImage" class="img-fluid">
                <div class="d-flex justify-content-center mt-3">
                    <span class="badge badge-light border text-muted px-3 py-2">
                        <i class="fas fa-search-plus mr-1"></i> High-resolution handcrafted preview
                    </span>
                </div>
            </div>
        </div>

        <!-- Product Meta & Purchase Form -->
        <div class="col-lg-6">
            <div class="product-detail-meta">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge-status badge-status-verified">
                        <i class="fas fa-tag"></i> <?php echo $cat_name; ?>
                    </span>
                    <span class="text-success small font-weight-bold">
                        <i class="fas fa-check-circle"></i> In Stock &bull; Ready to Ship
                    </span>
                </div>

                <h2 class="serif-heading mb-2"><?php echo $title; ?></h2>

                <!-- Rating Stars Header -->
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning mr-2">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= round($avg_rating)) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="font-weight-bold text-dark mr-2"><?php echo number_format($avg_rating, 1); ?></span>
                    <a href="#reviewsSection" class="text-muted small">(<?php echo $reviews_count; ?> customer reviews)</a>
                </div>

                <div class="d-flex align-items-baseline mb-4">
                    <span class="text-muted mr-2 font-weight-bold">Price:</span>
                    <span class="product-detail-price mr-2">PKR <?php echo $formatted_price; ?></span>
                    <span class="text-muted small">(Inclusive of standard taxes)</span>
                </div>

                <hr class="my-4">

                <!-- Specifications -->
                <div class="mb-4">
                    <h6 class="text-uppercase small font-weight-bold text-muted mb-2">Specifications & Sizing</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="pl-0 text-muted" style="width: 130px;"><i class="fas fa-ruler-combined text-accent mr-2"></i> Dimensions:</th>
                                <td class="font-weight-bold text-dark"><?php echo $size ?: 'Standard Sizing'; ?></td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted"><i class="fas fa-couch text-accent mr-2"></i> Category:</th>
                                <td class="font-weight-bold text-dark"><?php echo $cat_name; ?></td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted"><i class="fas fa-truck text-accent mr-2"></i> Delivery:</th>
                                <td class="text-dark">Doorstep delivery within 7 working days</td>
                            </tr>
                            <tr>
                                <th class="pl-0 text-muted"><i class="fas fa-shield-alt text-accent mr-2"></i> Payment:</th>
                                <td class="text-dark">Cash on Delivery (Pay after inspection)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Product Description -->
                <div class="mb-4">
                    <h6 class="text-uppercase small font-weight-bold text-muted mb-2">Description</h6>
                    <div class="text-muted" style="line-height: 1.7;">
                        <?php echo $detail; ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Add to Cart Form -->
                <form method="post" class="mt-4">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="mr-3 mb-2">
                            <label class="d-block small font-weight-bold text-muted text-uppercase mb-1">Quantity</label>
                            <div class="qty-stepper">
                                <button type="button" onclick="changeQty(-1)">-</button>
                                <input type="number" id="qtyInput" name="qty" value="1" min="1" max="99" readonly>
                                <button type="button" onclick="changeQty(1)">+</button>
                            </div>
                        </div>

                        <div class="flex-grow-1 mb-2">
                            <label class="d-block small font-weight-bold text-muted text-uppercase mb-1">&nbsp;</label>
                            <button type="submit" name="add_to_cart" class="btn btn-accent-custom btn-block py-2">
                                <i class="fas fa-shopping-bag mr-2"></i> Add to Shopping Bag
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="mt-5 pt-5 border-top" id="reviewsSection">
        <div class="section-header text-left mb-4">
            <span class="section-subtitle">Real Customer Feedback</span>
            <h3 class="serif-heading mb-0">Ratings & Reviews (<?php echo $reviews_count; ?>)</h3>
        </div>

        <div class="row">
            <!-- Reviews Breakdown Widget Column -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm rounded-lg bg-white p-4">
                    <div class="text-center pb-3 border-bottom mb-3">
                        <h1 class="display-3 font-weight-bold mb-0 text-dark"><?php echo number_format($avg_rating, 1); ?></h1>
                        <div class="text-warning my-1">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= round($avg_rating)) {
                                    echo '<i class="fas fa-star fa-lg"></i>';
                                } else {
                                    echo '<i class="far fa-star fa-lg"></i>';
                                }
                            }
                            ?>
                        </div>
                        <small class="text-muted">Based on <?php echo $reviews_count; ?> verified buyer review(s)</small>
                    </div>

                    <!-- Breakdown Bars -->
                    <div class="small">
                        <?php for ($s = 5; $s >= 1; $s--): 
                            $count_s = $rating_counts[$s] ?? 0;
                            $pct = ($reviews_count > 0) ? round(($count_s / $reviews_count) * 100) : 0;
                        ?>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted mr-2" style="width: 45px;"><?php echo $s; ?> Stars</span>
                                <div class="progress flex-grow-1" style="height: 7px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pct; ?>%;" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="text-muted ml-2 font-weight-bold" style="width: 30px; text-align: right;"><?php echo $count_s; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Write Review Trigger Card -->
                <div class="card border-0 shadow-sm rounded-lg bg-light p-4 mt-3 text-center">
                    <h6 class="font-weight-bold mb-1">Own this furniture piece?</h6>
                    <p class="text-muted small mb-3">Share your experience with other homeowners.</p>
                    <a href="#writeReviewForm" class="btn btn-primary-custom btn-sm">
                        <i class="fas fa-pen mr-1"></i> Write a Customer Review
                    </a>
                </div>
            </div>

            <!-- Reviews List & Submission Form Column -->
            <div class="col-lg-8">
                <!-- Existing Reviews -->
                <?php if (!empty($reviews_list)): ?>
                    <div class="mb-5">
                        <?php foreach ($reviews_list as $rev): 
                            $r_name   = htmlspecialchars($rev['customer_name']);
                            $r_rating = (int)$rev['rating'];
                            $r_title  = htmlspecialchars($rev['review_title']);
                            $r_text   = htmlspecialchars($rev['review_text']);
                            $r_date   = date("F j, Y", strtotime($rev['created_at']));
                        ?>
                            <div class="card border-0 shadow-sm rounded-lg bg-white p-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light text-primary mr-3 d-flex align-items-center justify-content-center font-weight-bold" style="width: 42px; height: 42px;">
                                            <?php echo strtoupper(substr($r_name, 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold mb-0"><?php echo $r_name; ?></h6>
                                            <small class="text-success"><i class="fas fa-check-circle fa-xs mr-1"></i> Verified Buyer</small>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo $r_date; ?></small>
                                </div>

                                <div class="text-warning mb-2 small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?php echo ($i <= $r_rating) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>

                                <h6 class="font-weight-bold text-dark mb-1"><?php echo $r_title; ?></h6>
                                <p class="text-muted mb-0" style="line-height: 1.6;"><?php echo $r_text; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-lg bg-white p-4 text-center mb-4">
                        <p class="text-muted mb-0">No reviews yet for this piece. Be the first to share your thoughts!</p>
                    </div>
                <?php endif; ?>

                <!-- Review Submission Form -->
                <div class="card border-0 shadow-sm rounded-lg bg-white p-4 p-md-5" id="writeReviewForm">
                    <h5 class="font-weight-bold mb-1">Write a Review</h5>
                    <p class="text-muted small mb-4">Tell us what you liked about the finish, assembly, and comfort.</p>

                    <?php if ($review_error): ?>
                        <div class="alert alert-danger alert-dismissible fade show small mb-3">
                            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($review_error); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['email'])): ?>
                        <form method="post">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Your Rating</label>
                                <select name="rating" class="form-control" style="max-width: 180px;">
                                    <option value="5">★★★★★ (5 - Excellent)</option>
                                    <option value="4">★★★★☆ (4 - Good)</option>
                                    <option value="3">★★★☆☆ (3 - Average)</option>
                                    <option value="2">★★☆☆☆ (2 - Fair)</option>
                                    <option value="1">★☆☆☆☆ (1 - Poor)</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Review Headline <span class="text-danger">*</span></label>
                                <input type="text" name="review_title" class="form-control" placeholder="e.g. Stunning craftsmanship and super comfortable" required>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted text-uppercase">Detailed Feedback <span class="text-danger">*</span></label>
                                <textarea name="review_text" class="form-control" rows="4" placeholder="How does it fit in your room? How was the wood and finish?" required></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="btn btn-primary-custom px-4">
                                <i class="fas fa-paper-plane mr-1"></i> Submit Review
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-3 bg-light rounded">
                            <p class="text-muted mb-3">You must be signed in with your account to leave a verified review.</p>
                            <a href="sign-in.php" class="btn btn-accent-custom btn-sm">
                                <i class="fas fa-sign-in-alt mr-1"></i> Sign In to Review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-5 pt-4 border-top">
        <div class="section-header text-left mb-4">
            <span class="section-subtitle">You May Also Like</span>
            <h3 class="serif-heading mb-0">Related Products in <?php echo $cat_name; ?></h3>
        </div>

        <div class="row">
            <?php 
            $rel_query = "SELECT fp.*, c.category as cat_name FROM furniture_product fp
                          LEFT JOIN categories c ON fp.category = c.id
                          WHERE fp.category = $category_id AND fp.pid != $pid AND fp.status = 'publish'
                          ORDER BY fp.pid DESC LIMIT 4";
            $rel_run   = mysqli_query($con, $rel_query);

            if ($rel_run && mysqli_num_rows($rel_run) > 0) {
                while ($rp = mysqli_fetch_assoc($rel_run)) {
                    $r_pid     = (int)$rp['pid'];
                    $r_title   = htmlspecialchars($rp['title']);
                    $r_price   = number_format($rp['price']);
                    $r_img     = htmlspecialchars($rp['image']);
                    $r_size    = htmlspecialchars($rp['size']);
                    ?>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                        <div class="product-card">
                            <div class="product-thumb-wrap">
                                <span class="product-badge-cat"><?php echo $cat_name; ?></span>
                                <a href="product-detail.php?product_id=<?php echo $r_pid; ?>">
                                    <img src="img/<?php echo $r_img; ?>" alt="<?php echo $r_title; ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="product-body">
                                <h4 class="product-title" title="<?php echo $r_title; ?>">
                                    <a href="product-detail.php?product_id=<?php echo $r_pid; ?>"><?php echo $r_title; ?></a>
                                </h4>
                                <div class="product-price">
                                    <span class="currency">PKR</span> <?php echo $r_price; ?>
                                </div>
                                <div class="product-actions mt-2">
                                    <a href="product.php?cart_id=<?php echo $r_pid; ?>" class="btn-card-cart">
                                        <i class="fas fa-shopping-bag"></i> Add to Bag
                                    </a>
                                    <a href="product-detail.php?product_id=<?php echo $r_pid; ?>" class="btn-card-view" title="Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    let current = parseInt(input.value) || 1;
    current = Math.max(1, current + delta);
    input.value = current;
}
</script>

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
