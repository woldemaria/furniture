<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$Fur_pro_id = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$Fur_pr_query = "SELECT * FROM furniture_product WHERE pid = $Fur_pro_id";
$Fur_pr_run   = mysqli_query($con, $Fur_pr_query);

if (!$Fur_pr_run || mysqli_num_rows($Fur_pr_run) == 0) {
    header('location: furniture_pro_view.php');
    exit;
}

$prod = mysqli_fetch_assoc($Fur_pr_run);
$db_pid      = (int)$prod['pid'];
$db_title    = htmlspecialchars($prod['title']);
$db_category = (int)$prod['category'];
$db_size     = htmlspecialchars($prod['size']);
$db_price    = (int)$prod['price'];    
$db_detail   = $prod['detail'];
$db_image    = htmlspecialchars($prod['image']);
$db_status   = htmlspecialchars($prod['status']);

$error = null;

if (isset($_POST['update'])) { 
    $title      = mysqli_real_escape_string($con, trim($_POST['title']));
    $size       = mysqli_real_escape_string($con, trim($_POST['size']));
    $price      = (int)$_POST['price'];
    $status     = mysqli_real_escape_string($con, $_POST['status']);
    $category   = (int)$_POST['category'];
    $detail     = mysqli_real_escape_string($con, trim($_POST['detail']));
    
    $image_name = $_FILES['upload']['name'] ?? '';
    $tmp_image  = $_FILES['upload']['tmp_name'] ?? '';
        
    if (!empty($title) && !empty($category) && $price > 0) {
        $final_image = $db_image;

        // If new image is uploaded
        if (!empty($image_name)) {
            $safe_image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
            $target_dir = __DIR__ . '/img/';
            $root_target = __DIR__ . '/../img/';

            if (move_uploaded_file($tmp_image, $target_dir . $safe_image_name)) {
                @copy($target_dir . $safe_image_name, $root_target . $safe_image_name);
                $final_image = $safe_image_name;
            }
        }

        $query = "UPDATE furniture_product SET 
                  title='$title', 
                  category=$category, 
                  size='$size', 
                  price=$price, 
                  detail='$detail', 
                  image='$final_image', 
                  status='$status' 
                  WHERE pid=$Fur_pro_id";                            
        
        if (mysqli_query($con, $query)) {
            $_SESSION['admin_toast'] = "Product updated successfully!";
            header("location: furniture_pro_view.php");
            exit;
        } else {
            $error = "Failed to update product: " . mysqli_error($con);
        }
    } else {
        $error = "Title, Category, and a positive Price are required.";
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
                        <h2 class="admin-header-title mb-0">Edit Product: <?php echo $db_title; ?></h2>
                        <small class="text-muted">Update product information, pricing, or replace catalog media</small>
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
                        <input type="text" class="form-control" name="title" value="<?php echo $db_title; ?>" required>
                    </div>
                  
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Category <span class="text-danger">*</span></label>
                            <select class="form-control" name="category" required>
                                <?php
                                $cat_query = "SELECT * FROM categories ORDER BY id ASC";
                                $cat_run   = mysqli_query($con, $cat_query);
                                if ($cat_run && mysqli_num_rows($cat_run) > 0) {
                                    while ($cat_row = mysqli_fetch_assoc($cat_run)) {
                                        $cat_id   = (int)$cat_row['id']; 
                                        $cat_name = htmlspecialchars(ucfirst($cat_row['category']));
                                        $selected = ($cat_id == $db_category) ? 'selected' : '';
                                        echo "<option value='$cat_id' $selected>$cat_name</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Dimensions / Size</label>
                            <input type="text" class="form-control" name="size" value="<?php echo $db_size; ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Price (PKR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" value="<?php echo $db_price; ?>" min="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Catalog Status</label>
                            <select class="form-control" name="status">
                                <option value="publish" <?php echo ($db_status == 'publish') ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($db_status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Replace Image (Optional)</label>
                            <input type="file" name="upload" class="form-control-file border p-1 rounded bg-light" accept="image/*">
                            <small class="text-muted">Leave blank to keep existing image</small>
                        </div>

                        <div class="col-md-3 mb-3 text-center">
                            <label class="font-weight-bold small text-muted text-uppercase d-block">Current Image</label>
                            <img src="img/<?php echo $db_image; ?>" alt="Current Product" class="rounded border" style="width: 70px; height: 70px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase">Product Description & Specs</label>
                        <textarea class="form-control" name="detail" rows="5"><?php echo htmlspecialchars($db_detail); ?></textarea>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" name="update" class="btn btn-primary px-4 py-2 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                        <a href="furniture_pro_view.php" class="btn btn-outline-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
