<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once(__DIR__ . '/include/dbcon.php');

$error = null;
$msg = null;

if (isset($_POST['submit'])) {
    $email        = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $new_password = mysqli_real_escape_string($con, trim($_POST['password'] ?? ''));
    
    if (!empty($email) && !empty($new_password)) {
        if (strlen($new_password) >= 6) {
            $check_query = "SELECT * FROM admin WHERE email = '$email' LIMIT 1";
            $run = mysqli_query($con, $check_query);

            if ($run && mysqli_num_rows($run) > 0) {
                $u_query = "UPDATE admin SET password = '$new_password' WHERE email = '$email'";
                if (mysqli_query($con, $u_query)) {
                    header("location: signin.php?msg=reset_success");
                    exit;
                } else {
                    $error = "Failed to update password. Database error.";
                }
            } else {
                $error = "No administrator account found with that email address.";
            }
        } else {
            $error = "Password must be at least 6 characters long.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password | Admin Panel</title>
  <link href="../img/favicon.png" rel="icon" type="image/png">
  <link href="css/bootstrap.css" rel="stylesheet">
  <link href="css/all.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body style="background-color: #0f172a;">

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
  <div class="row w-100 justify-content-center">
    <div class="col-lg-5 col-md-8 col-sm-11">
      <div class="admin-card shadow-lg p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="admin-header-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.75rem;">
            <i class="fas fa-key text-warning"></i>
          </div>
          <h3 class="font-weight-bold mb-1" style="color: #0f172a;">Reset Admin Password</h3>
          <p class="text-muted small">Enter your verified admin email and choose a new password</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted text-uppercase">Admin Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
          </div>

          <div class="form-group mb-4">
            <label class="font-weight-bold small text-muted text-uppercase">New Password</label>
            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
            <small class="text-muted">Enter a strong, secure password.</small>
          </div>

          <button class="btn btn-warning btn-block py-2 font-weight-bold mb-3 shadow-sm text-dark" type="submit" name="submit">
            <i class="fas fa-check-circle mr-1"></i> Update Admin Password
          </button>

          <div class="text-center pt-3 border-top small">
            Remember your credentials? <a href="signin.php" class="font-weight-bold text-primary">Return to Sign In</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
