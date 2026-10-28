<?php 
require_once('include/header.php');

if (!isset($_SESSION['email'])) {
    header('location: signin.php');
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';
$where = !empty($search) ? "WHERE cust_name LIKE '%$search%' OR cust_email LIKE '%$search%' OR cust_city LIKE '%$search%'" : '';

$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM customer_order co WHERE co.customer_id = c.cust_id) as total_orders,
          (SELECT IFNULL(SUM(product_amount),0) FROM customer_order co WHERE co.customer_id = c.cust_id) as total_spent
          FROM customer c 
          $where 
          ORDER BY c.cust_id DESC";
$run = mysqli_query($con, $query);
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once('include/sidebar.php'); ?>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="admin-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-4">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <div class="admin-header-icon mr-3 text-primary bg-light">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h2 class="admin-header-title mb-0">Registered Customers</h2>
                            <small class="text-muted">Manage customer accounts, delivery addresses, and spending</small>
                        </div>
                    </div>
                </div>

                <!-- Search Filter -->
                <div class="card bg-light border-0 p-3 rounded mb-4">
                    <form method="get" class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <input type="text" name="search" class="form-control" placeholder="Search customer by name, email, or city..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Search</button>
                            <?php if ($search): ?>
                                <a href="customers.php" class="btn btn-outline-danger btn-sm">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Customers Table -->
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Address & City</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($run && mysqli_num_rows($run) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($run)): 
                                    $cust_id      = (int)$row['cust_id'];
                                    $cust_name    = htmlspecialchars($row['cust_name']);
                                    $cust_email   = htmlspecialchars($row['cust_email']);
                                    $cust_add     = htmlspecialchars($row['cust_add']);
                                    $cust_city    = htmlspecialchars($row['cust_city']);
                                    $cust_pcode   = htmlspecialchars($row['cust_postalcode']);
                                    $cust_number  = htmlspecialchars($row['cust_number']);
                                    $total_orders = (int)$row['total_orders'];
                                    $total_spent  = number_format($row['total_spent']);
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted">#<?php echo $cust_id; ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark"><?php echo $cust_name; ?></div>
                                            <small class="text-muted"><i class="fas fa-map-pin mr-1"></i> <?php echo $cust_city; ?></small>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo $cust_email; ?>" class="text-primary"><?php echo $cust_email; ?></a>
                                        </td>
                                        <td><?php echo $cust_number ?: '&mdash;'; ?></td>
                                        <td>
                                            <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="<?php echo "$cust_add, $cust_city $cust_pcode"; ?>">
                                                <?php echo "$cust_add, $cust_city $cust_pcode"; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border"><?php echo $total_orders; ?> Order(s)</span>
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            PKR <?php echo $total_spent; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No registered customers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('include/footer.php'); ?>
