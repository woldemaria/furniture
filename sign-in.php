<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
include_once('include/dbcon.php');

// Redirect if already logged in
if (isset($_SESSION['email'])) {
    header('location: customer/index.php');
    exit;
}

$error = null;
if (isset($_POST['signin'])) {
    $email    = mysqli_real_escape_string($con, trim($_POST['email']));    
    $password = mysqli_real_escape_string($con, trim($_POST['password']));    
    
    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM customer WHERE cust_email = '$email' AND cust_pass = '$password' LIMIT 1";
        $run   = mysqli_query($con, $query);
        
        if ($run && mysqli_num_rows($run) > 0) {
            $row = mysqli_fetch_assoc($run);

            $_SESSION['id']     = $row['cust_id'];
            $_SESSION['name']   = $row['cust_name'];
            $_SESSION['email']  = $row['cust_email'];
            $_SESSION['add']    = $row['cust_add'];
            $_SESSION['city']   = $row['cust_city'];
            $_SESSION['pcode']  = $row['cust_postalcode'];
            $_SESSION['number'] = $row['cust_number'];
            
            $_SESSION['toast']  = "Welcome back, " . $row['cust_name'] . "!";

            // Check if there was an intended cart redirect
            if (isset($_GET['msg']) && $_GET['msg'] == 'checkout_login') {
                header('location: checkout.php');
            } else {
                header('location: customer/index.php'); 
            }
            exit;
        } else {
            $error = "Invalid email address or password. Please try again.";
        }
    } else {
        $error = "Please provide both your email and password.";
    }
}

include('include/header.php'); 
?>

<div class="container auth-wrapper">
    <div class="row align-items-center justify-content-center">
        <!-- Brand Info Column -->
        <div class="col-lg-6 col-md-8 mb-5 mb-lg-0 pr-lg-5">
            <span class="hero-badge" style="background:#fef3c7; color:#b45309; border-color:#fde68a;">
                <i class="fas fa-couch"></i> Welcome to FurniCraft
            </span>
            <h1 class="serif-heading font-weight-bold display-4 mb-3" style="color: #0f172a;">
                Artisan Furniture Crafted For Your Home
            </h1>
            <p class="text-muted lead mb-4">
                Access your personal shopping portal to track deliveries, review your past orders, manage delivery addresses, and enjoy seamless one-click ordering.
            </p>
            <div class="d-flex align-items-center text-muted small">
                <span class="mr-4"><i class="fas fa-truck text-warning mr-1"></i> Free Doorstep Delivery</span>
                <span><i class="fas fa-shield-alt text-success mr-1"></i> Cash on Delivery</span>
            </div>
        </div>

        <!-- Sign In Form Card Column -->
        <div class="col-lg-5 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <div class="brand-icon-wrap mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3 class="serif-heading font-weight-bold mb-1">Customer Sign In</h3>
                    <p class="text-muted small">Enter your credentials to access your account.</p>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'login_required'): ?>
                    <div class="alert alert-warning alert-dismissible fade show small" role="alert">
                        <i class="fas fa-info-circle mr-1"></i> Please log in or create an account to add items to your cart.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
                    <div class="alert alert-success alert-dismissible fade show small" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> Account created successfully! Please sign in below.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Email Address</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                            </div>
                            <input type="email" name="email" class="form-control border-left-0 pl-0" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-lock text-muted"></i></span>
                            </div>
                            <input type="password" name="password" class="form-control border-left-0 pl-0" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" name="signin" class="btn btn-accent-custom btn-block py-2 font-weight-bold mb-3 shadow-sm">
                        <i class="fas fa-sign-in-alt mr-1"></i> Sign In to Account
                    </button>

                    <div class="text-center pt-3 border-top text-muted small">
                        Not registered yet? <a href="register.php" class="font-weight-bold text-primary">Create an Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
