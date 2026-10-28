<?php 
// Database connection configuration with multiple fallbacks
$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "furniture-shop";
$db_port = 3306;
$db_socket = "/tmp/mysql.sock";

// Try 127.0.0.1 first
mysqli_report(MYSQLI_REPORT_OFF);
$con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$con) {
    // Fallback to socket if available
    if (file_exists($db_socket)) {
        $con = @mysqli_connect("localhost", $db_user, $db_pass, $db_name, 0, $db_socket);
    }
}

if (!$con) {
    // Final fallback to standard localhost
    $con = @mysqli_connect("localhost", $db_user, $db_pass, $db_name);
}

if (!$con) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");
?>
