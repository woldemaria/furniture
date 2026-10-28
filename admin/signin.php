<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
include_once(__DIR__ . '/include/dbcon.php');

if (isset($_SESSION['email'])) {
    header('location: index.php');
    exit;
}

$error = null;
if (isset($_POST['signin'])) {
    $email    = mysqli_real_escape_string($con, trim($_POST['email']));    
    $password = mysqli_real_escape_string($con, trim($_POST['password']));  

    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM admin WHERE email = '$email' AND password = '$password' LIMIT 1";
        $run   = mysqli_query($con, $query);
        
        if ($run && mysqli_num_rows($run) > 0) {
            $row = mysqli_fetch_assoc($run);
            $_SESSION['id']       = $row['id'];
            $_SESSION['name']     = $row['name'];
            $_SESSION['email']    = $row['email'];
            $_SESSION['image']    = $row['image'];
            
            header('location: index.php'); 
            exit;
        } else {
            $error = "Invalid administrator email or password.";
        }
    } else {
        $error = "Please fill in all credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Sign In | FurniCraft Management</title>
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
            <i class="fas fa-couch"></i>
          </div>
          <h3 class="font-weight-bold mb-1" style="color: #0f172a;">FurniCraft Admin</h3>
          <p class="text-muted small">Sign in to access store administration and fulfillment</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
          <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle mr-1"></i> New admin account created! Please sign in.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'reset_success'): ?>
          <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle mr-1"></i> Password reset successfully! Please sign in with your new password.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted text-uppercase">Email Address</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
              </div>
              <input type="email" name="email" class="form-control border-left-0" placeholder="admin@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
            </div>
          </div>

          <div class="form-group mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <label class="font-weight-bold small text-muted text-uppercase mb-0">Password</label>
              <a href="forget_pass.php" class="small text-primary">Forgot password?</a>
            </div>
            <div class="input-group mt-1">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
              </div>
              <input type="password" name="password" class="form-control border-left-0" placeholder="Enter password" required>
            </div>
          </div>

          <button class="btn btn-primary btn-block py-2 font-weight-bold mb-3 shadow-sm" type="submit" name="signin">
            <i class="fas fa-sign-in-alt mr-1"></i> Enter Admin Dashboard
          </button>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
            <a href="register.php" class="text-muted"><i class="fas fa-user-plus mr-1"></i> Create Admin</a>
            <a href="../index.php" class="text-muted"><i class="fas fa-store mr-1"></i> Live Store</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
