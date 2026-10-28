<?php 
include('include/header.php');

$customer_id = (int)$_SESSION['id'];

if (isset($_POST['update_password'])) {
    $old_pass     = $_POST['old_pass'] ?? '';
    $new_pass     = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['conf_pass'] ?? '';

    if (!empty($old_pass) && !empty($new_pass) && !empty($confirm_pass)) {
        $query = "SELECT cust_pass FROM customer WHERE cust_id = $customer_id";
        $run   = mysqli_query($con, $query);

        if ($run && mysqli_num_rows($run) > 0) {
            $row = mysqli_fetch_assoc($run);
            $current_db_pass = $row['cust_pass'];

            if ($old_pass === $current_db_pass) {
                if ($new_pass === $confirm_pass) {
                    if (strlen($new_pass) >= 6) {
                        $escaped_pass = mysqli_real_escape_string($con, $new_pass);
                        $up_query = "UPDATE customer SET cust_pass = '$escaped_pass' WHERE cust_id = $customer_id";

                        if (mysqli_query($con, $up_query)) {
                            $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                <i class='fas fa-check-circle mr-2'></i> <strong>Success!</strong> Your password has been updated securely.
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'>&times;</span>
                                </button>
                            </div>";
                        } else {
                            $error = "Failed to update password. Please try again.";
                        }
                    } else {
                        $error = "New password must be at least 6 characters long.";
                    }
                } else {
                    $error = "New password and confirmation password do not match.";
                }
            } else {
                $error = "Your current password is incorrect.";
            }
        }
    } else {
        $error = "All password fields are required.";
    }
}
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Security & Password</h1>
        <div class="page-breadcrumb">
            <a href="../index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="index.php">Account</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Change Password</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 col-md-5 mb-4">
            <?php include('include/sidebar.php'); ?>
        </div>

        <div class="col-lg-8 col-md-7">
            <?php 
            if (isset($msg)) {
                echo $msg;
            }
            if (isset($error)) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-triangle mr-2'></i> " . htmlspecialchars($error) . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
            }
            ?>

            <div class="card border-0 shadow-sm rounded-lg bg-white p-4 p-md-5">
                <div class="mb-4">
                    <h4 class="font-weight-bold mb-1">Change Account Password</h4>
                    <p class="text-muted small">Choose a strong, unique password to safeguard your account and order history.</p>
                </div>

                <form method="post">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="old_pass" class="form-control" placeholder="Enter current password" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_pass" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="conf_pass" class="form-control" placeholder="Re-type new password" required>
                    </div>

                    <button type="submit" name="update_password" class="btn btn-primary-custom px-4">
                        <i class="fas fa-lock mr-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
