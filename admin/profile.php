<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$session_email = mysqli_real_escape_string($con, $_SESSION['email']);
$admin_query   = "SELECT * FROM admin WHERE email = '$session_email' LIMIT 1";
$admin_run     = mysqli_query($con, $admin_query);
$admin         = mysqli_fetch_assoc($admin_run);

$admin_id    = (int)($admin['id'] ?? 0);
$db_name     = htmlspecialchars($admin['name'] ?? '');
$db_email    = htmlspecialchars($admin['email'] ?? '');
$db_image    = !empty($admin['image']) ? htmlspecialchars($admin['image']) : 'avatar.png';

$msg = null;
$error = null;

if (isset($_POST['submit'])) {  
    $name         = mysqli_real_escape_string($con, trim($_POST['name']));
    $password     = trim($_POST['password'] ?? '');                   
    $image_name   = $_FILES['upload']['name'] ?? '';
    $tmp_image    = $_FILES['upload']['tmp_name'] ?? '';
    
    if (!empty($name)) {
        $final_image = $admin['image'] ?? 'avatar.png';

        // Check if new image uploaded
        if (!empty($image_name)) {
            $safe_image = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
            $target = __DIR__ . '/img/' . $safe_image;
            if (move_uploaded_file($tmp_image, $target)) {
                $final_image = $safe_image;
            }
        }

        // Check password change
        $pass_clause = "";
        if (!empty($password)) {
            $escaped_pass = mysqli_real_escape_string($con, $password);
            $pass_clause = ", password = '$escaped_pass'";
        }

        $u_query = "UPDATE admin SET name = '$name', image = '$final_image' $pass_clause WHERE id = $admin_id";
        if (mysqli_query($con, $u_query)) {
            $_SESSION['name'] = $name;
            $_SESSION['admin_toast'] = "Admin profile updated successfully!";
            header('location: profile.php');
            exit;
        } else {
            $error = "Failed to update profile.";
        }
    } else {
        $error = "Admin name is required.";
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
                    <div class="admin-header-icon mr-3 text-primary bg-light">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div>
                        <h2 class="admin-header-title mb-0">Profile Settings</h2>
                        <small class="text-muted">Manage your administrator account details and security</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Email Address (Read Only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo $db_email; ?>" readonly disabled>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Administrator Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="<?php echo $db_name; ?>" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">New Password (Leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter new password">
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted text-uppercase">Change Avatar Image</label>
                                <input type="file" name="upload" class="form-control-file border p-1 rounded bg-light" accept="image/*">
                                <small class="text-muted">Recommended: Square PNG or JPG</small>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <label class="font-weight-bold small text-muted text-uppercase d-block mb-3">Current Profile Picture</label>
                            <img src="img/<?php echo $db_image; ?>" alt="Admin Profile" class="rounded-circle border shadow-sm mb-3" style="width: 130px; height: 130px; object-fit: cover;">
                            <p class="font-weight-bold text-dark mb-0"><?php echo $db_name; ?></p>
                            <small class="text-muted"><?php echo $db_email; ?></small>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" name="submit" class="btn btn-primary px-4 py-2 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save Profile
                        </button>
                    </div>
                </form>
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
