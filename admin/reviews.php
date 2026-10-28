<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$error = null;

// Handle review deletion
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];
    $del_query = "DELETE FROM reviews WHERE review_id = $del_id";
    if (mysqli_query($con, $del_query)) {
        $_SESSION['admin_toast'] = "Review #$del_id has been removed.";
        header('location: reviews.php');
        exit;
    } else {
        $error = "Failed to delete review.";
    }
}

// Filters
$filter_rating = isset($_GET['rating']) && is_numeric($_GET['rating']) ? (int)$_GET['rating'] : null;
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';

$where_clauses = [];
if ($filter_rating) {
    $where_clauses[] = "r.rating = $filter_rating";
}
if (!empty($search)) {
    $where_clauses[] = "(fp.title LIKE '%$search%' OR r.customer_name LIKE '%$search%' OR r.review_title LIKE '%$search%' OR r.review_text LIKE '%$search%')";
}
$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Stats
$total_reviews_res = mysqli_query($con, "SELECT COUNT(*) as cnt, AVG(rating) as avg_r FROM reviews");
$total_stats = mysqli_fetch_assoc($total_reviews_res);
$total_count = (int)($total_stats['cnt'] ?? 0);
$overall_avg = round((float)($total_stats['avg_r'] ?? 0), 1);

// Reviews list
$query = "SELECT r.*, fp.title as product_title, fp.image as product_image, c.cust_name, c.cust_email 
          FROM reviews r 
          LEFT JOIN furniture_product fp ON r.product_id = fp.pid 
          LEFT JOIN customer c ON r.customer_id = c.cust_id 
          $where_sql 
          ORDER BY r.review_id DESC";
$run = mysqli_query($con, $query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-4">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <div class="admin-header-icon mr-3 text-warning bg-light">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h2 class="admin-header-title mb-0">Customer Reviews & Ratings</h2>
                            <small class="text-muted">Moderate customer feedback, inspect 5-star ratings, and ensure review quality</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="badge badge-warning p-2 px-3 text-dark font-weight-bold mr-2">
                            <i class="fas fa-star"></i> <?php echo number_format($overall_avg, 1); ?> Overall Avg
                        </div>
                        <div class="badge badge-secondary p-2 px-3 font-weight-bold">
                            <?php echo $total_count; ?> Total Reviews
                        </div>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_toast'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['admin_toast']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['admin_toast']); ?>
                <?php endif; ?>

                <!-- Filter & Search Bar -->
                <div class="card bg-light border-0 p-3 mb-4 rounded-lg">
                    <form method="get" action="reviews.php" class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by customer, product, or keyword..." value="<?php echo htmlspecialchars($search); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2 mb-md-0">
                            <select name="rating" class="form-control" onchange="this.form.submit()">
                                <option value="">All Ratings (1 - 5 Stars)</option>
                                <option value="5" <?php echo $filter_rating === 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 Stars)</option>
                                <option value="4" <?php echo $filter_rating === 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 Stars)</option>
                                <option value="3" <?php echo $filter_rating === 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3 Stars)</option>
                                <option value="2" <?php echo $filter_rating === 2 ? 'selected' : ''; ?>>⭐⭐ (2 Stars)</option>
                                <option value="1" <?php echo $filter_rating === 1 ? 'selected' : ''; ?>>⭐ (1 Star)</option>
                            </select>
                        </div>

                        <div class="col-md-3 text-md-right">
                            <?php if ($filter_rating || !empty($search)): ?>
                                <a href="reviews.php" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-times mr-1"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Reviews Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Review Comment</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($run && mysqli_num_rows($run) > 0) {
                                while ($row = mysqli_fetch_assoc($run)) {
                                    $rid      = (int)$row['review_id'];
                                    $p_id     = (int)$row['product_id'];
                                    $p_title  = htmlspecialchars($row['product_title'] ?? 'Deleted Product');
                                    $p_img    = htmlspecialchars($row['product_image'] ?? '');
                                    $c_name   = htmlspecialchars($row['customer_name'] ?? $row['cust_name'] ?? 'Verified Buyer');
                                    $c_email  = htmlspecialchars($row['cust_email'] ?? 'Direct customer');
                                    $rating   = (int)$row['rating'];
                                    $r_title  = htmlspecialchars($row['review_title'] ?? '');
                                    $r_text   = htmlspecialchars($row['review_text'] ?? '');
                                    $date     = date('M d, Y', strtotime($row['created_at']));
                                    ?>
                                    <tr>
                                        <td><span class="badge badge-light border">#<?php echo $rid; ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($p_img)): ?>
                                                    <img src="../img/<?php echo $p_img; ?>" alt="<?php echo $p_title; ?>" class="rounded mr-2" style="width: 44px; height: 44px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <a href="../product-detail.php?product_id=<?php echo $p_id; ?>" target="_blank" class="font-weight-bold text-dark text-truncate d-block" style="max-width: 170px;">
                                                        <?php echo $p_title; ?>
                                                    </a>
                                                    <small class="text-muted">ID: #<?php echo $p_id; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold"><?php echo $c_name; ?></div>
                                            <small class="text-muted"><?php echo $c_email; ?></small>
                                        </td>
                                        <td>
                                            <div class="text-warning text-nowrap">
                                                <?php 
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star text-muted"></i>';
                                                }
                                                ?>
                                                <span class="font-weight-bold text-dark ml-1"><?php echo $rating; ?>/5</span>
                                            </div>
                                        </td>
                                        <td style="max-width: 280px;">
                                            <?php if ($r_title): ?>
                                                <div class="font-weight-bold text-dark mb-1 small"><?php echo $r_title; ?></div>
                                            <?php endif; ?>
                                            <span class="text-muted small"><?php echo nl2br($r_text); ?></span>
                                        </td>
                                        <td><small class="text-nowrap"><?php echo $date; ?></small></td>
                                        <td class="text-center">
                                            <a href="reviews.php?del=<?php echo $rid; ?>" onclick="return confirm('Are you sure you want to permanently delete this customer review?');" class="btn btn-sm btn-outline-danger" title="Delete Review">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5 text-muted'><i class='fas fa-comment-slash fa-2x mb-2 d-block'></i>No reviews found matching the criteria.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
