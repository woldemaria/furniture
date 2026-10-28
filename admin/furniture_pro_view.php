<?php 
require_once("include/header.php");

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$error = null;

// Handle Product Deletion with constraint check
if (isset($_GET['del'])) {
    $del = (int)$_GET['del'];

    // Check if referenced in customer_order
    $order_check = mysqli_query($con, "SELECT COUNT(*) as cnt FROM customer_order WHERE product_id = $del");
    $oc_row = mysqli_fetch_assoc($order_check);
    if ($oc_row && (int)$oc_row['cnt'] > 0) {
        $error = "Cannot delete product #$del: It is referenced in " . $oc_row['cnt'] . " historical customer order(s). Consider changing its status to 'Draft' instead.";
    } else {
        // Remove from cart references first
        mysqli_query($con, "DELETE FROM cart WHERE product_id = $del");

        $query = "DELETE FROM `furniture_product` WHERE pid = $del";
        if (mysqli_query($con, $query)) {
            $_SESSION['admin_toast'] = "Product #$del has been deleted.";
            header("location: furniture_pro_view.php");
            exit;
        } else {
            $error = "Failed to delete product.";
        }
    }
}

$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';

$where_clauses = [];
if (!empty($status_filter)) {
    $where_clauses[] = "fp.status = '$status_filter'";
}
if (!empty($search)) {
    $where_clauses[] = "(fp.title LIKE '%$search%' OR cat.category LIKE '%$search%')";
}
$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$pr_query = "SELECT fp.*, cat.category as cat_name 
             FROM furniture_product fp 
             LEFT JOIN categories cat ON fp.category = cat.id 
             $where_sql 
             ORDER BY fp.pid DESC";
$pr_run = mysqli_query($con, $pr_query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once("include/sidebar.php"); ?>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-4">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <div class="admin-header-icon mr-3">
                            <i class="fas fa-couch"></i>
                        </div>
                        <div>
                            <h2 class="admin-header-title mb-0">Furniture Catalog</h2>
                            <small class="text-muted">Review, edit, or manage catalog inventory</small>
                        </div>
                    </div>
                    <a href="furniture_pro.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle mr-1"></i> Add Product
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Filters & Search -->
                <div class="card bg-light border-0 p-3 rounded mb-4">
                    <form method="get" class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <input type="text" name="search" class="form-control" placeholder="Search by title or category..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">-- All Statuses --</option>
                                <option value="publish" <?php echo ($status_filter == 'publish') ? 'selected' : ''; ?>>Published Only</option>
                                <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Drafts Only</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <button type="submit" class="btn btn-secondary mr-2"><i class="fas fa-search"></i> Filter</button>
                            <?php if ($search || $status_filter): ?>
                                <a href="furniture_pro_view.php" class="btn btn-outline-danger btn-sm">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Dimensions</th>
                                <th>Price (PKR)</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pr_run && mysqli_num_rows($pr_run) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($pr_run)): 
                                    $pid      = (int)$row['pid'];
                                    $title    = htmlspecialchars($row['title']);
                                    $cat_name = htmlspecialchars(ucfirst($row['cat_name'] ?? 'Unassigned'));
                                    $size     = htmlspecialchars($row['size']);
                                    $price    = number_format($row['price']);
                                    $image    = htmlspecialchars($row['image']);
                                    $status   = strtolower($row['status']);
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted">#<?php echo $pid; ?></td>
                                        <td>
                                            <img src="img/<?php echo $image; ?>" alt="<?php echo $title; ?>" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <h6 class="font-weight-bold mb-0 text-dark"><?php echo $title; ?></h6>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border"><?php echo $cat_name; ?></span>
                                        </td>
                                        <td class="small text-muted"><?php echo $size ?: '&mdash;'; ?></td>
                                        <td class="font-weight-bold text-dark">PKR <?php echo $price; ?></td>
                                        <td>
                                            <?php if ($status == 'publish'): ?>
                                                <span class="badge badge-success px-2 py-1">Published</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="furniture_pro_edit.php?pid=<?php echo $pid; ?>" class="btn btn-sm btn-outline-primary mr-1" title="Edit Product">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="furniture_pro_view.php?del=<?php echo $pid; ?>" class="btn btn-sm btn-outline-danger" title="Delete Product" onclick="return confirm('Delete this furniture product permanently?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No furniture products match your criteria.</td>
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

require_once("include/footer.php"); 
?>
