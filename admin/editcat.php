<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$query   = "SELECT * FROM categories WHERE id = $edit_id";
$run     = mysqli_query($con, $query);

if (!$run || mysqli_num_rows($run) == 0) {
    header('location: category.php');
    exit;
}

$row         = mysqli_fetch_assoc($run);
$dbcat_id    = (int)$row['id'];
$dbcat_name  = htmlspecialchars($row['category']);
$dbcat_icon  = htmlspecialchars(trim($row['fontawesome-icon']));

$msg = null;
$error = null;

if (isset($_POST['submit'])) {
    $catname = mysqli_real_escape_string($con, trim($_POST['catname']));
    $icon    = mysqli_real_escape_string($con, trim($_POST['fonts']));

    if (!empty($catname)) {
        $up_query = "UPDATE categories SET `category` = '$catname', `fontawesome-icon` = '$icon' WHERE id = $dbcat_id";
        if (mysqli_query($con, $up_query)) {
            $_SESSION['admin_toast'] = "Category updated successfully!";
            header("location: category.php");
            exit;
        } else {
            $error = "Failed to update category. Database error.";
        }
    } else {
        $error = "Category title cannot be empty.";
    }
}
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="admin-header-icon mr-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Edit Category</h2>
                        <small class="text-muted">Modify category title and its associated FontAwesome icon</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Category ID</label>
                            <input type="text" class="form-control" value="#<?php echo $dbcat_id; ?>" disabled readonly>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Category Title <span class="text-danger">*</span></label>
                            <input type="text" name="catname" class="form-control" value="<?php echo $dbcat_name; ?>" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">FontAwesome Icon Class</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas <?php echo $dbcat_icon ?: 'fa-tag'; ?>"></i></span>
                                </div>
                                <input type="text" name="fonts" class="form-control" value="<?php echo $dbcat_icon; ?>" placeholder="fa-couch, fa-bed, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" name="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Update Category
                        </button>
                        <a href="category.php" class="btn btn-outline-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
