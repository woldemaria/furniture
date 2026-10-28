<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$error = null;
$msg = null;

// Handle Category Deletion
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];

    // Check if products exist in this category
    $prod_check = mysqli_query($con, "SELECT COUNT(*) as cnt FROM furniture_product WHERE category = $del_id");
    $cnt_row = mysqli_fetch_assoc($prod_check);
    if ($cnt_row && (int)$cnt_row['cnt'] > 0) {
        $error = "Cannot delete category: " . $cnt_row['cnt'] . " product(s) are currently associated with it. Please reassign or delete the products first.";
    } else {
        $del_query = "DELETE FROM categories WHERE id = $del_id";
        if (mysqli_query($con, $del_query)) {
            $_SESSION['admin_toast'] = "Category deleted successfully.";
            header("location: category.php");
            exit;
        } else {
            $error = "Failed to delete category.";
        }
    }
}

// Handle Category Creation
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($con, trim($_POST['category']));
    $fontawesome   = mysqli_real_escape_string($con, trim($_POST['fonts']));

    if (!empty($category_name)) {
        if (empty($fontawesome)) {
            $fontawesome = 'fa-couch';
        }
        $query = "INSERT INTO `categories` (`category`, `fontawesome-icon`) VALUES ('$category_name', '$fontawesome')";
        if (mysqli_query($con, $query)) {
            $_SESSION['admin_toast'] = "New category added!";
            header("location: category.php");
            exit;
        } else {
            $error = "Database error while adding category.";
        }
    } else {
        $error = "Category title is required.";
    }
}

// Fetch all categories with product counts
$cat_query = "SELECT c.*, (SELECT COUNT(*) FROM furniture_product fp WHERE fp.category = c.id) as product_count 
              FROM categories c 
              ORDER BY c.id ASC";
$run = mysqli_query($con, $cat_query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="admin-card mb-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="admin-header-icon mr-3">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Furniture Categories</h2>
                        <small class="text-muted">Manage product classifications and iconography</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Add New Category Form -->
                <div class="card bg-light border-0 p-4 rounded mb-4">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-plus-circle text-primary mr-1"></i> Add New Category</h6>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Category Title <span class="text-danger">*</span></label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Office Desks" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">FontAwesome Icon</label>
                                <input type="text" name="fonts" class="form-control" placeholder="e.g. fa-chair, fa-bed">
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" name="add_category" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Icon</th>
                                <th>Category Name</th>
                                <th>Active Products</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($run && mysqli_num_rows($run) > 0): ?>
                                <?php while ($cat = mysqli_fetch_assoc($run)): 
                                    $cid    = (int)$cat['id'];
                                    $cname  = htmlspecialchars(ucfirst($cat['category']));
                                    $icon   = htmlspecialchars(trim($cat['fontawesome-icon']));
                                    $pcount = (int)$cat['product_count'];
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted">#<?php echo $cid; ?></td>
                                        <td>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary" style="width: 38px; height: 38px;">
                                                <i class="fas <?php echo $icon ?: 'fa-couch'; ?>"></i>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold"><?php echo $cname; ?></td>
                                        <td>
                                            <span class="badge badge-light border px-2 py-1"><?php echo $pcount; ?> Products</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="editcat.php?edit=<?php echo $cid; ?>" class="btn btn-sm btn-outline-primary mr-1" title="Edit Category">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="category.php?del=<?php echo $cid; ?>" class="btn btn-sm btn-outline-danger" title="Delete Category" onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No categories created yet.</td>
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
