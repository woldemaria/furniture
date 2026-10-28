<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$error = null;
$msg = null;

if (isset($_POST['submit'])) { 
    $title      = mysqli_real_escape_string($con, trim($_POST['title']));
    $category   = (int)$_POST['category'];
    $size       = mysqli_real_escape_string($con, trim($_POST['size']));
    $price      = (int)$_POST['price'];
    $status     = mysqli_real_escape_string($con, $_POST['status']);
    $detail     = mysqli_real_escape_string($con, trim($_POST['detail']));
    $date       = date("d-m-Y");

    $image_name = $_FILES['upload']['name'] ?? '';
    $tmp_image  = $_FILES['upload']['tmp_name'] ?? '';
        
    if (!empty($title) && !empty($category) && $price > 0 && !empty($image_name)) {
        // Sanitize filename
        $safe_image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
        $target_dir = __DIR__ . '/img/';
        $root_target = __DIR__ . '/../img/';

        if (move_uploaded_file($tmp_image, $target_dir . $safe_image_name)) {
            @copy($target_dir . $safe_image_name, $root_target . $safe_image_name);

            $query = "INSERT INTO furniture_product (`title`, `category`, `size`, `price`, `detail`, `image`, `date`, `status`)
                      VALUES ('$title', $category, '$size', $price, '$detail', '$safe_image_name', '$date', '$status')";
         
            if (mysqli_query($con, $query)) {
                $_SESSION['admin_toast'] = "Furniture product published successfully!";
                header("location: furniture_pro_view.php");
                exit;
            } else {
                $error = "Database query error: " . mysqli_error($con);
            }
        } else {
            $error = "Failed to upload product image. Check folder permissions.";
        }
    } else {
        $error = "Product title, category, positive price, and an image are required.";
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
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Add New Furniture Product</h2>
                        <small class="text-muted">Publish a new catalog item with images, sizing, and pricing</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Furniture Product Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. Valerie 2 Seater Luxury Sofa" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    </div>
                  
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Category <span class="text-danger">*</span></label>
                            <select class="form-control" name="category" required>
                                <option value="">-- Choose Category --</option>
                                <?php
                                $cat_query = "SELECT * FROM categories ORDER BY id ASC";
                                $cat_run   = mysqli_query($con, $cat_query);
                                if ($cat_run && mysqli_num_rows($cat_run) > 0) {
                                    while ($cat_row = mysqli_fetch_assoc($cat_run)) {
                                        $cat_id   = (int)$cat_row['id']; 
                                        $cat_name = htmlspecialchars(ucfirst($cat_row['category']));
                                        $sel = (isset($_POST['category']) && $_POST['category'] == $cat_id) ? 'selected' : '';
                                        echo "<option value='$cat_id' $sel>$cat_name</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Dimensions / Size</label>
                            <input type="text" class="form-control" name="size" placeholder="e.g. 84w X 30h (inches)" value="<?php echo htmlspecialchars($_POST['size'] ?? ''); ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Price (PKR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" placeholder="e.g. 45000" min="1" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Catalog Status</label>
                            <select class="form-control" name="status">
                                <option value="publish" selected>Published (Visible in store)</option>
                                <option value="draft">Draft (Hidden)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Product Image <span class="text-danger">*</span></label>
                            <input type="file" name="upload" class="form-control-file border p-1 rounded bg-light" accept="image/*" required>
                            <small class="text-muted">Recommended: JPG, PNG, or WebP</small>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase">Product Description & Craftsmanship Details</label>
                        <textarea class="form-control" name="detail" rows="5" placeholder="Enter materials, wood type, foam quality, warranty info..."><?php echo htmlspecialchars($_POST['detail'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" name="submit" class="btn btn-primary px-4 py-2 font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> Publish Furniture Product
                        </button>
                        <a href="furniture_pro_view.php" class="btn btn-outline-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
