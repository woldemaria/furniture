<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
include_once('include/dbcon.php');

if (isset($_SESSION['email'])) {
    header('location: customer/index.php');
    exit;
}

$error = null;
$message = null;

if (isset($_POST['register'])) {
    $fullname    = mysqli_real_escape_string($con, trim($_POST['fullname'] ?? ''));
    $email       = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $password    = $_POST['password'] ?? '';
    $conf_pass   = $_POST['confirm-password'] ?? '';
    $address     = mysqli_real_escape_string($con, trim($_POST['address'] ?? ''));
    $city        = mysqli_real_escape_string($con, trim($_POST['city'] ?? ''));
    $postal_code = mysqli_real_escape_string($con, trim($_POST['code'] ?? ''));
    $number      = mysqli_real_escape_string($con, trim($_POST['phone_number'] ?? ''));

    if (!empty($fullname) && !empty($email) && !empty($password) && !empty($conf_pass) && !empty($address) && !empty($city) && !empty($number)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($password !== $conf_pass) {
            $error = "Passwords do not match. Please re-enter.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Check if email already registered
            $check_email = mysqli_query($con, "SELECT cust_id FROM customer WHERE cust_email = '$email'");
            if ($check_email && mysqli_num_rows($check_email) > 0) {
                $error = "An account with this email address already exists. Please sign in instead.";
            } else {
                $escaped_pass = mysqli_real_escape_string($con, $password);
                $cust_query = "INSERT INTO customer (`cust_name`, `cust_email`, `cust_pass`, `cust_add`, `cust_city`, `cust_postalcode`, `cust_number`) 
                               VALUES ('$fullname', '$email', '$escaped_pass', '$address', '$city', '$postal_code', '$number')";

                if (mysqli_query($con, $cust_query)) {
                    $new_id = mysqli_insert_id($con);
                    
                    // Auto login the newly registered user
                    $_SESSION['id']     = $new_id;
                    $_SESSION['name']   = $fullname;
                    $_SESSION['email']  = $email;
                    $_SESSION['add']    = $address;
                    $_SESSION['city']   = $city;
                    $_SESSION['pcode']  = $postal_code;
                    $_SESSION['number'] = $number;
                    $_SESSION['toast']  = "Welcome to FurniCraft, $fullname!";

                    header('location: customer/index.php');
                    exit;
                } else {
                    $error = "Registration failed due to a server error. Please try again.";
                }
            }
        }
    } else {
        $error = "All fields marked with (*) are required.";
    }
}

include('include/header.php'); 
?>

<div class="container auth-wrapper">
    <div class="row align-items-center justify-content-center">
        <!-- Benefits Column -->
        <div class="col-lg-5 col-md-8 mb-5 mb-lg-0 pr-lg-4">
            <span class="hero-badge" style="background:#fef3c7; color:#b45309; border-color:#fde68a;">
                <i class="fas fa-gift"></i> New Member Benefits
            </span>
            <h1 class="serif-heading font-weight-bold display-4 mb-3" style="color: #0f172a;">
                Join the FurniCraft Family
            </h1>
            <p class="text-muted lead mb-4">
                Registering an account takes less than a minute and unlocks personalized delivery tracking, instant order history, and priority dispatch.
            </p>
            <ul class="list-unstyled text-muted">
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-check-circle text-success mr-3"></i> Seamless checkout with pre-saved address
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-check-circle text-success mr-3"></i> Real-time status updates on handcrafted pieces
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-check-circle text-success mr-3"></i> Download official PDF invoices anytime
                </li>
            </ul>
        </div>

        <!-- Registration Form Card Column -->
        <div class="col-lg-7 col-md-10">
            <div class="auth-card">
                <div class="mb-4">
                    <h3 class="serif-heading font-weight-bold mb-1">Create Customer Account</h3>
                    <p class="text-muted small">Please provide your delivery and contact details.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" placeholder="e.g. Sara Khan" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm-password" class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Delivery Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" placeholder="House #, Street name, Area" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Lahore" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Postal Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. 54000" value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small text-uppercase">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="0300-1234567" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" name="register" class="btn btn-accent-custom btn-block py-3 font-weight-bold shadow-sm mb-3">
                        <i class="fas fa-user-plus mr-1"></i> Complete Registration
                    </button>

                    <div class="text-center pt-3 border-top text-muted small">
                        Already have an account? <a href="sign-in.php" class="font-weight-bold text-primary">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
