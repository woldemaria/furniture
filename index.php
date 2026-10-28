<?php 
include('include/header.php'); 

// Handle Add-to-Cart from Home
$feedback_msg = null;
if (isset($_GET['cart_id'])) {
    if (!isset($_SESSION['email'])) {
        header("location: sign-in.php?msg=login_required");
        exit;
    } else {
        $custid = (int)$_SESSION['id'];
        $p_id   = (int)$_GET['cart_id'];

        // Verify product exists
        $check_prod = mysqli_query($con, "SELECT pid FROM furniture_product WHERE pid = $p_id AND status = 'publish'");
        if ($check_prod && mysqli_num_rows($check_prod) > 0) {
            $sel_cart = "SELECT * FROM cart WHERE cust_id = $custid AND product_id = $p_id";
            $run_cart = mysqli_query($con, $sel_cart);

            if (mysqli_num_rows($run_cart) == 0) {
                $cart_query = "INSERT INTO `cart`(`cust_id`, `product_id`, `quantity`) VALUES ($custid, $p_id, 1)";    
                if (mysqli_query($con, $cart_query)) {
                    $_SESSION['toast'] = "Product added to your cart!";
                    header('location: index.php');
                    exit;
                }
            } else {
                $_SESSION['toast_warn'] = "This item is already in your cart!";
                header('location: index.php');
                exit;
            }
        }
    }
}
?>

<!-- Hero Carousel Slider -->
<div class="hero-slider-wrap">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-ride="carousel" data-interval="5000">
        <ol class="carousel-indicators">
            <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#heroCarousel" data-slide-to="1"></li>
            <li data-target="#heroCarousel" data-slide-to="2"></li>
        </ol>

        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <img src="img/couch.jpg" alt="Luxury Living Room Sofas">
                <div class="hero-overlay-content">
                    <span class="hero-badge"><i class="fas fa-sparkles"></i> 2026 Collection</span>
                    <h1 class="hero-title serif-heading">Elegance & Comfort For Modern Living</h1>
                    <p class="hero-desc">Discover bespoke luxury sofas, ergonomic armchairs, and handcrafted wooden tables crafted with perfection.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="product.php" class="btn btn-accent-custom mr-3 mb-2">
                            <i class="fas fa-shopping-bag mr-1"></i> Explore Catalog
                        </a>
                        <a href="product.php?cat_id=5" class="btn btn-outline-light nav-action-btn mb-2">
                            View Sofas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <img src="img/bedset_slide.jpg" alt="Master Bedroom Sets">
                <div class="hero-overlay-content">
                    <span class="hero-badge"><i class="fas fa-bed"></i> Master Bedroom Sets</span>
                    <h1 class="hero-title serif-heading">Peaceful Sleep In Handcrafted Luxury</h1>
                    <p class="hero-desc">Transform your private sanctuary with solid wood bed frames and premium upholstered headboards.</p>
                    <div>
                        <a href="product.php?cat_id=1" class="btn btn-accent-custom mr-3">
                            <i class="fas fa-arrow-right mr-1"></i> Shop Bedroom
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <img src="img/cupboard.jpg" alt="Architectural Storage & Wardrobes">
                <div class="hero-overlay-content">
                    <span class="hero-badge"><i class="fas fa-th-large"></i> Smart Storage</span>
                    <h1 class="hero-title serif-heading">Functional Spaces, Seamless Organization</h1>
                    <p class="hero-desc">Custom-styled cupboards, wardrobes, and credenzas engineered for contemporary lifestyles.</p>
                    <div>
                        <a href="product.php?cat_id=6" class="btn btn-accent-custom mr-3">
                            <i class="fas fa-boxes mr-1"></i> Shop Storage
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>

<!-- Category Quick Browse Chips -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
            <div>
                <span class="section-subtitle">Categories</span>
                <h3 class="serif-heading mb-0">Browse By Category</h3>
            </div>
            <a href="product.php" class="text-primary font-weight-bold">View All Categories <i class="fas fa-arrow-right fa-sm ml-1"></i></a>
        </div>

        <div class="row">
            <?php 
            $cat_res = mysqli_query($con, "SELECT c.*, (SELECT COUNT(*) FROM furniture_product fp WHERE fp.category = c.id AND fp.status='publish') as p_count FROM categories c ORDER BY c.id ASC");
            if ($cat_res && mysqli_num_rows($cat_res) > 0) {
                while ($c = mysqli_fetch_assoc($cat_res)) {
                    $cid   = (int)$c['id'];
                    $cname = htmlspecialchars(ucfirst($c['category']));
                    $icon  = htmlspecialchars(trim($c['fontawesome-icon']));
                    $count = (int)$c['p_count'];
                    ?>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="product.php?cat_id=<?php echo $cid; ?>" class="category-chip text-decoration-none">
                            <i class="fas <?php echo $icon ?: 'fa-couch'; ?>"></i>
                            <div>
                                <div class="font-weight-bold text-truncate" style="max-width: 110px;"><?php echo $cname; ?></div>
                                <small class="text-muted"><?php echo $count; ?> Items</small>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Latest Products Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Exclusive Selections</span>
            <h2 class="section-title">Latest Arrivals</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row">
            <?php    
            // Fetch wishlist product IDs for logged-in user
            $user_wishlist = [];
            if (isset($_SESSION['id'])) {
                $uid = (int)$_SESSION['id'];
                $w_res = mysqli_query($con, "SELECT product_id FROM wishlist WHERE cust_id = $uid");
                if ($w_res) {
                    while ($w_row = mysqli_fetch_assoc($w_res)) {
                        $user_wishlist[] = (int)$w_row['product_id'];
                    }
                }
            }

            $p_query = "SELECT fp.*, c.category as cat_name, 
                        COALESCE(AVG(r.rating), 0) as avg_rating,
                        COUNT(r.review_id) as review_count
                        FROM furniture_product fp 
                        LEFT JOIN categories c ON fp.category = c.id 
                        LEFT JOIN reviews r ON fp.pid = r.product_id AND r.status = 'approved'
                        WHERE fp.status = 'publish' 
                        GROUP BY fp.pid
                        ORDER BY fp.pid DESC LIMIT 8";
            $p_run   = mysqli_query($con, $p_query);
            
            if ($p_run && mysqli_num_rows($p_run) > 0) {
                while ($p = mysqli_fetch_assoc($p_run)) {
                    $pid      = (int)$p['pid'];
                    $ptitle   = htmlspecialchars($p['title']);
                    $pcat_name= htmlspecialchars(ucfirst($p['cat_name'] ?? 'Furniture'));
                    $p_price  = number_format($p['price']);
                    $size     = htmlspecialchars($p['size']);
                    $img1     = htmlspecialchars($p['image']);
                    $p_rating = round((float)$p['avg_rating'], 1);
                    $p_rev_cnt= (int)$p['review_count'];
                    $is_saved = in_array($pid, $user_wishlist);
                    $wish_url = isset($_SESSION['id']) ? ($is_saved ? "wishlist.php?remove_wishlist=$pid" : "wishlist.php?add_wishlist=$pid") : "sign-in.php";
                    ?>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                        <div class="product-card position-relative">
                            <!-- Wishlist Heart Badge -->
                            <a href="<?php echo $wish_url; ?>" class="btn position-absolute rounded-circle shadow-sm <?php echo $is_saved ? 'btn-danger' : 'btn-light border'; ?>" style="top: 10px; right: 10px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; z-index: 5;" title="<?php echo $is_saved ? 'Saved in Wishlist' : 'Add to Wishlist'; ?>">
                                <i class="<?php echo $is_saved ? 'fas' : 'far'; ?> fa-heart fa-sm"></i>
                            </a>

                            <div class="product-thumb-wrap">
                                <span class="product-badge-cat"><?php echo $pcat_name; ?></span>
                                <a href="product-detail.php?product_id=<?php echo $pid; ?>">
                                    <img src="img/<?php echo $img1; ?>" alt="<?php echo $ptitle; ?>" loading="lazy">
                                </a>
                            </div>
                            
                            <div class="product-body">
                                <!-- Star Rating Indicator -->
                                <div class="d-flex align-items-center mb-1">
                                    <span class="text-warning small mr-1"><i class="fas fa-star"></i> <?php echo number_format($p_rating, 1); ?></span>
                                    <small class="text-muted">(<?php echo $p_rev_cnt; ?>)</small>
                                </div>

                                <h4 class="product-title" title="<?php echo $ptitle; ?>">
                                    <a href="product-detail.php?product_id=<?php echo $pid; ?>"><?php echo $ptitle; ?></a>
                                </h4>
                                <?php if ($size): ?>
                                    <div class="product-dimension"><i class="fas fa-ruler-combined mr-1"></i> <?php echo $size; ?></div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <span class="currency">PKR</span> <?php echo $p_price; ?>
                                </div>
                                
                                <div class="product-actions mt-2">
                                    <a href="index.php?cart_id=<?php echo $pid; ?>" class="btn-card-cart">
                                        <i class="fas fa-shopping-bag"></i> Add to Bag
                                    </a>
                                    <a href="product-detail.php?product_id=<?php echo $pid; ?>" class="btn-card-view" title="Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php  
                }
            } else {
                echo "<div class='col-12 text-center py-5'><h4 class='text-muted'>No products available at the moment.</h4></div>";
            }
            ?>
        </div>

        <div class="text-center mt-4">
            <a href="product.php" class="btn btn-outline-custom px-4 py-2">
                View Entire Collection <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Brand Assurances / Value Props -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 2.2rem;">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Fast Delivery</h6>
                        <small class="text-muted">Doorstep delivery within 7 working days nationwide</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 2.2rem;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Premium Quality</h6>
                        <small class="text-muted">Built with seasoned hardwoods & durable upholstery</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 2.2rem;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Cash on Delivery</h6>
                        <small class="text-muted">Inspect upon arrival before you pay cash securely</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 2.2rem;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Dedicated Support</h6>
                        <small class="text-muted">Friendly customer assistance from selection to delivery</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- We Deal With Showcase -->
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Craftsmanship</span>
            <h2 class="section-title">Designed For Everyday Living</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Modern Bed Sets</h4>
                    <p class="text-muted">Engineered with solid wood veneers and sturdy slat systems. Ergonomic headboards that provide luxurious nighttime back support.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Contemporary Dining Sets</h4>
                    <p class="text-muted">Tempered glass and solid beechwood dining suites that seat family and guests in timeless style and durability.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-chair"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Armchairs & Executive Seating</h4>
                    <p class="text-muted">High-density foam padding wrapped in breathable fabrics and chrome metal frames for home offices and lounge spaces.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Plush Sofas & Loungers</h4>
                    <p class="text-muted">From 1-seaters to expansive 4-seater sectionals, designed with bold accent colors like Charcoal Gray, Sapphire Blue, and Emerald Green.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-border-all"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Coffee & Accent Tables</h4>
                    <p class="text-muted">Scandinavian and mid-century modern tables with scratch-resistant finishes that complement any living area.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Modular Wardrobes & Cupboards</h4>
                    <p class="text-muted">Spacious built-in closets and freestanding storage solutions designed to maintain pristine organization with minimal clutter.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Effortless Shopping</span>
            <h2 class="section-title">How It Works</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="p-4">
                    <div class="feature-icon-wrap bg-light text-primary">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2">1. Choose Your Piece</h5>
                    <p class="text-muted">Explore categories, inspect product dimensions, and add items to your online shopping cart.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4 mb-md-0">
                <div class="p-4">
                    <div class="feature-icon-wrap bg-light text-warning">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2">2. Fast Doorstep Delivery</h5>
                    <p class="text-muted">Enter your delivery address at checkout. We package and transport your furniture with white-glove care within 7 days.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4">
                    <div class="feature-icon-wrap bg-light text-success">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2">3. Cash on Delivery</h5>
                    <p class="text-muted">Receive and inspect your furniture in your home, then comfortably pay with cash on delivery.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
// Trigger Toasts if set
if (isset($_SESSION['toast'])) {
    $t_msg = $_SESSION['toast'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('success', '" . addslashes($t_msg) . "');
        });
    </script>";
    unset($_SESSION['toast']);
}
if (isset($_SESSION['toast_warn'])) {
    $t_warn = $_SESSION['toast_warn'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('info', '" . addslashes($t_warn) . "');
        });
    </script>";
    unset($_SESSION['toast_warn']);
}

include('include/footer.php'); 
?>
