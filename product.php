<?php 
include('include/header.php');

$customer_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

// Handle Add to Cart
if (isset($_GET['cart_id'])) {
    if (!isset($_SESSION['email'])) {
        header("location: sign-in.php?msg=login_required");
        exit;
    } else {
        $p_id = (int)$_GET['cart_id'];

        $check_prod = mysqli_query($con, "SELECT pid FROM furniture_product WHERE pid = $p_id AND status = 'publish'");
        if ($check_prod && mysqli_num_rows($check_prod) > 0) {
            $sel_cart = "SELECT * FROM cart WHERE cust_id = $customer_id AND product_id = $p_id";
            $run_cart = mysqli_query($con, $sel_cart);

            if (mysqli_num_rows($run_cart) == 0) {
                $cart_query = "INSERT INTO `cart`(`cust_id`, `product_id`, `quantity`) VALUES ($customer_id, $p_id, 1)";    
                if (mysqli_query($con, $cart_query)) {
                    $_SESSION['toast'] = "Product added to your cart!";
                }
            } else {
                $_SESSION['toast_warn'] = "This item is already in your cart!";
            }
        }
        
        // Redirect back preserving filters
        $redirect_params = $_GET;
        unset($redirect_params['cart_id']);
        $query_str = !empty($redirect_params) ? '?' . http_build_query($redirect_params) : '';
        header("location: product.php" . $query_str);
        exit;
    }
}

// Fetch user wishlist IDs for active heart state
$user_wishlist = [];
if ($customer_id) {
    $w_res = mysqli_query($con, "SELECT product_id FROM wishlist WHERE cust_id = $customer_id");
    if ($w_res) {
        while ($wr = mysqli_fetch_assoc($w_res)) {
            $user_wishlist[] = (int)$wr['product_id'];
        }
    }
}

// Filters & Sorting Setup
$page_id = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$required_pro = 9;

$cat_id    = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
$search    = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : null;
$min_price = (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) ? (int)$_GET['min_price'] : null;
$max_price = (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) ? (int)$_GET['max_price'] : null;
$sort      = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Build WHERE clause
$where_clauses = ["fp.status = 'publish'"];
$url_params = [];

if ($cat_id) {
    $where_clauses[] = "fp.category = $cat_id";
    $url_params['cat_id'] = $cat_id;
}
if ($search) {
    $where_clauses[] = "(fp.title LIKE '%$search%' OR fp.detail LIKE '%$search%')";
    $url_params['search'] = $search;
}
if ($min_price !== null && $min_price > 0) {
    $where_clauses[] = "fp.price >= $min_price";
    $url_params['min_price'] = $min_price;
}
if ($max_price !== null && $max_price > 0) {
    $where_clauses[] = "fp.price <= $max_price";
    $url_params['max_price'] = $max_price;
}
if ($sort !== 'newest') {
    $url_params['sort'] = $sort;
}

$where_sql = implode(' AND ', $where_clauses);

// Determine ORDER BY
switch ($sort) {
    case 'price_low':
        $order_by_sql = "ORDER BY fp.price ASC";
        break;
    case 'price_high':
        $order_by_sql = "ORDER BY fp.price DESC";
        break;
    case 'title_asc':
        $order_by_sql = "ORDER BY fp.title ASC";
        break;
    case 'rating':
        $order_by_sql = "ORDER BY avg_rating DESC, fp.pid DESC";
        break;
    case 'newest':
    default:
        $order_by_sql = "ORDER BY fp.pid DESC";
        break;
}

// Count total matching items
$count_query = "SELECT COUNT(*) as total FROM furniture_product fp WHERE $where_sql";
$count_res = mysqli_query($con, $count_query);
$total_products = 0;
if ($count_res && $crow = mysqli_fetch_assoc($count_res)) {
    $total_products = (int)$crow['total'];
}

$pages = max(1, ceil($total_products / $required_pro));
$product_start = ($page_id - 1) * $required_pro;

// Active Category Name
$active_cat_name = "All Collections";
if ($cat_id) {
    $cres = mysqli_query($con, "SELECT category FROM categories WHERE id = $cat_id");
    if ($cres && $crow = mysqli_fetch_assoc($cres)) {
        $active_cat_name = ucfirst($crow['category']);
    }
}

// Fetch products with their review rating averages
$p_query = "SELECT fp.*, c.category as cat_name, 
            IFNULL(AVG(r.rating), 5.0) as avg_rating,
            COUNT(r.review_id) as review_count
            FROM furniture_product fp 
            LEFT JOIN categories c ON fp.category = c.id 
            LEFT JOIN reviews r ON fp.pid = r.product_id AND r.status='approved'
            WHERE $where_sql 
            GROUP BY fp.pid 
            $order_by_sql 
            LIMIT $product_start, $required_pro";
$p_run = mysqli_query($con, $p_query);
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title"><?php echo htmlspecialchars($active_cat_name); ?></h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Furniture Catalog</span>
            <?php if ($cat_id): ?>
                <i class="fas fa-chevron-right fa-xs"></i>
                <span><?php echo htmlspecialchars($active_cat_name); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filter Column -->
        <div class="col-lg-3 col-md-4 mb-4">
            <!-- Categories List -->
            <div class="card border-0 shadow-sm rounded-lg bg-white p-3 mb-4">
                <h5 class="font-weight-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-filter text-accent mr-2"></i> Categories
                </h5>
                <div class="list-group list-group-flush">
                    <?php 
                    $cat_link_base = "product.php?";
                    $params_no_cat = $url_params;
                    unset($params_no_cat['cat_id']);
                    $all_url = "product.php" . (!empty($params_no_cat) ? '?' . http_build_query($params_no_cat) : '');
                    ?>
                    <a href="<?php echo $all_url; ?>" class="list-group-item d-flex justify-content-between align-items-center px-2 py-3 border-bottom <?php echo (!$cat_id) ? 'font-weight-bold text-accent' : 'text-dark'; ?>">
                        <span><i class="fas fa-th-large mr-2"></i> All Products</span>
                        <span class="badge badge-light border"><?php echo $total_products; ?></span>
                    </a>
                    
                    <?php 
                    $all_cats = mysqli_query($con, "SELECT c.*, (SELECT COUNT(*) FROM furniture_product fp WHERE fp.category = c.id AND fp.status='publish') as p_count FROM categories c ORDER BY c.id ASC");
                    if ($all_cats && mysqli_num_rows($all_cats) > 0) {
                        while ($cat_row = mysqli_fetch_assoc($all_cats)) {
                            $cid   = (int)$cat_row['id'];
                            $cname = htmlspecialchars(ucfirst($cat_row['category']));
                            $icon  = htmlspecialchars(trim($cat_row['fontawesome-icon']));
                            $count = (int)$cat_row['p_count'];
                            $is_active = ($cat_id == $cid);
                            
                            $cat_params = $url_params;
                            $cat_params['cat_id'] = $cid;
                            $cat_url = "product.php?" . http_build_query($cat_params);
                            ?>
                            <a href="<?php echo $cat_url; ?>" class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 border-bottom <?php echo $is_active ? 'font-weight-bold text-accent' : 'text-dark'; ?>">
                                <span><i class="fas <?php echo $icon ?: 'fa-couch'; ?> mr-2 text-muted"></i> <?php echo $cname; ?></span>
                                <span class="badge <?php echo $is_active ? 'badge-warning text-white' : 'badge-light border'; ?>"><?php echo $count; ?></span>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Price Range Filter Card -->
            <div class="card border-0 shadow-sm rounded-lg bg-white p-3 mb-4">
                <h5 class="font-weight-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-money-bill-wave text-accent mr-2"></i> Price Filter (PKR)
                </h5>
                <form method="get" action="product.php">
                    <?php if ($cat_id): ?><input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>"><?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                    <?php if ($sort): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>"><?php endif; ?>

                    <div class="form-row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block font-weight-bold mb-1">Min Price</small>
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="0" value="<?php echo $min_price ?: ''; ?>">
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block font-weight-bold mb-1">Max Price</small>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="100000" value="<?php echo $max_price ?: ''; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom btn-sm btn-block">Apply Filter</button>
                </form>

                <!-- Quick Price Presets -->
                <div class="mt-3 pt-3 border-top small">
                    <span class="font-weight-bold text-muted d-block mb-2">Quick Presets:</span>
                    <?php 
                    $p1 = $url_params; $p1['min_price'] = 0; $p1['max_price'] = 25000;
                    $p2 = $url_params; $p2['min_price'] = 25000; $p2['max_price'] = 50000;
                    $p3 = $url_params; $p3['min_price'] = 50000; unset($p3['max_price']);
                    ?>
                    <a href="product.php?<?php echo http_build_query($p1); ?>" class="badge badge-light border p-2 mr-1 mb-1 d-inline-block">Under 25k</a>
                    <a href="product.php?<?php echo http_build_query($p2); ?>" class="badge badge-light border p-2 mr-1 mb-1 d-inline-block">25k - 50k</a>
                    <a href="product.php?<?php echo http_build_query($p3); ?>" class="badge badge-light border p-2 mb-1 d-inline-block">50k+</a>
                </div>
            </div>

            <!-- Value Prop Card -->
            <div class="card border-0 shadow-sm rounded-lg p-3 bg-light text-center d-none d-md-block">
                <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                <h6 class="font-weight-bold mb-1">Authentic Craftsmanship</h6>
                <small class="text-muted">Direct from artisan workshops with 100% verified hardwood construction.</small>
            </div>
        </div>

        <!-- Product Grid Column -->
        <div class="col-lg-9 col-md-8">
            <!-- Search & Sort Filter Bar -->
            <div class="card border-0 shadow-sm rounded-lg bg-white p-3 mb-4">
                <form method="get" action="product.php" class="row align-items-center">
                    <?php if ($cat_id): ?><input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>"><?php endif; ?>
                    <?php if ($min_price): ?><input type="hidden" name="min_price" value="<?php echo $min_price; ?>"><?php endif; ?>
                    <?php if ($max_price): ?><input type="hidden" name="max_price" value="<?php echo $max_price; ?>"><?php endif; ?>
                    
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search furniture by title..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 d-flex align-items-center justify-content-md-end">
                        <label class="small text-muted font-weight-bold mr-2 mb-0 d-none d-sm-inline">Sort:</label>
                        <select name="sort" class="form-control form-control-sm mr-2" style="max-width: 170px;" onchange="this.form.submit()">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                            <option value="price_low" <?php echo ($sort === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($sort === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo ($sort === 'rating') ? 'selected' : ''; ?>>Customer Rating</option>
                            <option value="title_asc" <?php echo ($sort === 'title_asc') ? 'selected' : ''; ?>>Title: A to Z</option>
                        </select>
                        <?php if ($search || $cat_id || $min_price || $max_price || $sort !== 'newest'): ?>
                            <a href="product.php" class="badge badge-light border text-danger p-2" title="Reset all filters"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products List -->
            <div class="row">
                <?php 
                if ($p_run && mysqli_num_rows($p_run) > 0) {
                    while ($p = mysqli_fetch_assoc($p_run)) {
                        $pid        = (int)$p['pid'];
                        $ptitle     = htmlspecialchars($p['title']);
                        $pcat_name  = htmlspecialchars(ucfirst($p['cat_name'] ?? 'Furniture'));
                        $p_price    = number_format($p['price']);
                        $size       = htmlspecialchars($p['size']);
                        $img1       = htmlspecialchars($p['image']);
                        $p_rating   = round((float)$p['avg_rating'], 1);
                        $p_rev_cnt  = (int)$p['review_count'];
                        $is_saved   = in_array($pid, $user_wishlist);

                        // Build add-to-cart redirect URL
                        $current_query_str = !empty($url_params) ? '&' . http_build_query($url_params) : '';
                        $cart_url = "product.php?cart_id=$pid$current_query_str&page=$page_id";
                        $wish_url = isset($_SESSION['id']) ? ($is_saved ? "wishlist.php?remove_wishlist=$pid" : "wishlist.php?add_wishlist=$pid") : "sign-in.php";
                        ?>
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
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
                                        <a href="<?php echo $cart_url; ?>" class="btn-card-cart">
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
                    ?>
                    <div class="col-12 text-center py-5">
                        <div class="mb-3 text-muted" style="font-size: 3.5rem;">
                            <i class="fas fa-couch"></i>
                        </div>
                        <h4 class="font-weight-bold">No Furniture Products Found</h4>
                        <p class="text-muted">We couldn't find any items matching your selected criteria or price range.</p>
                        <a href="product.php" class="btn btn-accent-custom mt-2">
                            <i class="fas fa-undo mr-1"></i> Reset All Filters
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Pagination (Preserves filters) -->
            <?php if ($pages > 1): ?>
                <nav aria-label="Product navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page_id > 1): 
                            $prev_params = $url_params;
                            $prev_params['page'] = $page_id - 1;
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="product.php?<?php echo http_build_query($prev_params); ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pages; $i++): 
                            $page_params = $url_params;
                            $page_params['page'] = $i;
                        ?>
                            <li class="page-item <?php echo ($i == $page_id) ? 'active' : ''; ?>">
                                <a class="page-link" href="product.php?<?php echo http_build_query($page_params); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page_id < $pages): 
                            $next_params = $url_params;
                            $next_params['page'] = $page_id + 1;
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="product.php?<?php echo http_build_query($next_params); ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
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
