<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once(__DIR__ . '/include/dbcon.php');

$error = null;

if (isset($_POST['submit'])) {
    $name     = mysqli_real_escape_string($con, trim($_POST['name'] ?? ''));
    $email    = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $password = mysqli_real_escape_string($con, trim($_POST['password'] ?? ''));
    
    if (!empty($name) && !empty($email) && !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (strlen($password) >= 6) {
                // Check if admin email already exists
                $check_query = "SELECT id FROM admin WHERE email = '$email' LIMIT 1";
                $check_run   = mysqli_query($con, $check_query);
                
                if ($check_run && mysqli_num_rows($check_run) > 0) {
                    $error = "An administrator account with this email already exists.";
                } else {
                    $insert_query = "INSERT INTO admin (`name`, `image`, `email`, `password`) 
                                     VALUES ('$name', 'avatar.png', '$email', '$password')";
                    if (mysqli_query($con, $insert_query)) {
                        header("location: signin.php?msg=registered");
                        exit;
                    } else {
                        $error = "Failed to register new admin. Database error.";
                    }
                }
            } else {
                $error = "Password must be at least 6 characters long.";
            }
        } else {
            $error = "Please enter a valid email address.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Administrator | FurniCraft Management</title>
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
            <i class="fas fa-user-shield text-primary"></i>
          </div>
          <h3 class="font-weight-bold mb-1" style="color: #0f172a;">Register Administrator</h3>
          <p class="text-muted small">Create an account to manage catalog, orders, and fulfillment</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted text-uppercase">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Store Manager" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required autofocus>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted text-uppercase">Admin Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@furnicraft.store" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
          </div>

          <div class="form-group mb-4">
            <label class="font-weight-bold small text-muted text-uppercase">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
            <small class="text-muted">Will be stored for admin panel access.</small>
          </div>

          <button class="btn btn-primary btn-block py-2 font-weight-bold mb-3 shadow-sm" type="submit" name="submit">
            <i class="fas fa-user-plus mr-1"></i> Register Admin Account
          </button>

          <div class="text-center pt-3 border-top small">
            Already have admin access? <a href="signin.php" class="font-weight-bold text-primary">Sign In</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
