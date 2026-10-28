<?php 
include('include/header.php');

$customer_id = (int)$_SESSION['id'];

if (isset($_POST['update'])) {
    $fullname = mysqli_real_escape_string($con, trim($_POST['fullname']));
    $address  = mysqli_real_escape_string($con, trim($_POST['address']));
    $city     = mysqli_real_escape_string($con, trim($_POST['city']));
    $code     = mysqli_real_escape_string($con, trim($_POST['code']));
    $number   = mysqli_real_escape_string($con, trim($_POST['phone_number']));

    if (!empty($fullname) && !empty($address) && !empty($city) && !empty($number)) {
        $up_query = "UPDATE `customer` SET 
                     `cust_name`='$fullname',
                     `cust_add`='$address',
                     `cust_city`='$city',
                     `cust_postalcode`='$code',
                     `cust_number`='$number' 
                     WHERE cust_id=$customer_id";
        
        if (mysqli_query($con, $up_query)) {
            // Update session cache
            $_SESSION['name']   = $fullname;
            $_SESSION['add']    = $address;
            $_SESSION['city']   = $city;
            $_SESSION['pcode']  = $code;
            $_SESSION['number'] = $number;

            $_SESSION['msg'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle mr-2'></i> <strong>Success!</strong> Your personal profile has been updated.
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
            header('location: personal-detail.php');
            exit;
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    } else {
        $error = "Full Name, Address, City, and Phone Number are required.";
    }
}

// Fetch current details
$query = "SELECT * FROM customer WHERE cust_id=$customer_id";
$run   = mysqli_query($con, $query);
$row   = mysqli_fetch_array($run);

$cust_name   = htmlspecialchars($row['cust_name'] ?? '');
$cust_email  = htmlspecialchars($row['cust_email'] ?? '');
$cust_add    = htmlspecialchars($row['cust_add'] ?? '');
$cust_city   = htmlspecialchars($row['cust_city'] ?? '');
$cust_pcode  = htmlspecialchars($row['cust_postalcode'] ?? '');
$cust_number = htmlspecialchars($row['cust_number'] ?? '');
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Personal Details</h1>
        <div class="page-breadcrumb">
            <a href="../index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="index.php">Account</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Edit Profile</span>
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
            if (isset($_SESSION['msg'])) {
                echo $_SESSION['msg'];
                unset($_SESSION['msg']);
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
                    <h4 class="font-weight-bold mb-1">Update Personal Information</h4>
                    <p class="text-muted small">Update your contact details and default shipping destination.</p>
                </div>

                <form method="post">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $cust_name; ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Email Address (Read Only)</label>
                        <input type="email" name="email" class="form-control bg-light" value="<?php echo $cust_email; ?>" readonly disabled>
                        <small class="text-muted">Email is used for account login and cannot be altered directly.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted text-uppercase">Delivery Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" value="<?php echo $cust_add; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="<?php echo $cust_city; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Postal Code</label>
                            <input type="text" name="code" class="form-control" value="<?php echo $cust_pcode; ?>">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo $cust_number; ?>" required>
                    </div>

                    <button type="submit" name="update" class="btn btn-primary-custom px-4">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
