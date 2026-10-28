<?php
require_once(__DIR__ . '/fpdf/fpdf.php');
require_once(__DIR__ . '/include/dbcon.php');

if (!isset($_GET['invoice'])) {
    die("Invoice number not specified.");
}

$invoice_no = mysqli_real_escape_string($con, $_GET['invoice']);

// Fetch order items for this invoice
$order_query = "SELECT co.*, fp.title, fp.price as unit_price, fp.size 
                FROM customer_order co
                LEFT JOIN furniture_product fp ON co.product_id = fp.pid
                WHERE co.invoice_no = '$invoice_no'";
$order_run   = mysqli_query($con, $order_query);

if (!$order_run || mysqli_num_rows($order_run) == 0) {
    die("No order records found for invoice #" . htmlspecialchars($invoice_no));
}

// Read the first row for customer & order details
$items = [];
$first_row = null;
$grand_total = 0;

while ($row = mysqli_fetch_assoc($order_run)) {
    if (!$first_row) {
        $first_row = $row;
    }
    $items[] = $row;
    $grand_total += (int)$row['product_amount'];
}

$cust_id       = (int)$first_row['customer_id'];
$order_date    = $first_row['order_date'];
$order_status  = ucfirst($first_row['order_status']);
$cust_fullname = $first_row['customer_fullname'];
$cust_email    = $first_row['customer_email'];
$cust_address  = $first_row['customer_address'];
$cust_city     = $first_row['customer_city'];
$cust_pcode    = $first_row['customer_pcode'];
$cust_phone    = $first_row['customer_phonenumber'];

// Create PDF document
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Header / Brand
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(30, 41, 59);
$pdf->Cell(120, 8, 'FurniCraft Furniture Store', 0, 0);

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(217, 119, 6);
$pdf->Cell(60, 8, 'OFFICIAL INVOICE', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(120, 5, 'Main Boulevard, Lahore Cantt, Punjab 54000', 0, 0);
$pdf->Cell(60, 5, 'Invoice #: ' . $invoice_no, 0, 1, 'R');

$pdf->Cell(120, 5, 'Support: +92 300 123-4567 | support@furnicraft.store', 0, 0);
$pdf->Cell(60, 5, 'Date: ' . $order_date, 0, 1, 'R');

$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(60, 5, 'Status: ' . $order_status, 0, 1, 'R');

$pdf->Ln(6);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// Customer Bill-To Info
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 23, 42);
$pdf->Cell(90, 6, 'BILL TO:', 0, 0);
$pdf->Cell(90, 6, 'SHIPPING DESTINATION:', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(51, 65, 85);
$pdf->Cell(90, 5, $cust_fullname . ' (Cust ID: #' . $cust_id . ')', 0, 0);
$pdf->Cell(90, 5, $cust_fullname, 0, 1);

$pdf->Cell(90, 5, 'Email: ' . $cust_email, 0, 0);
$pdf->Cell(90, 5, $cust_address, 0, 1);

$pdf->Cell(90, 5, 'Phone: ' . $cust_phone, 0, 0);
$pdf->Cell(90, 5, $cust_city . ' ' . $cust_pcode . ', Pakistan', 0, 1);

$pdf->Cell(90, 5, 'Payment: Cash on Delivery (COD)', 0, 0);
$pdf->Cell(90, 5, 'Contact: ' . $cust_phone, 0, 1);

$pdf->Ln(8);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(241, 245, 249);
$pdf->SetTextColor(15, 23, 42);

$pdf->Cell(15, 8, '#', 1, 0, 'C', true);
$pdf->Cell(95, 8, 'Product Description', 1, 0, 'L', true);
$pdf->Cell(20, 8, 'Qty', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Unit Price', 1, 0, 'R', true);
$pdf->Cell(25, 8, 'Total (PKR)', 1, 1, 'R', true);

// Table Rows
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(30, 41, 59);

$idx = 1;
foreach ($items as $item) {
    $title      = $item['title'] ?? ('Product #' . $item['product_id']);
    $qty        = (int)$item['products_qty'];
    $amount     = (int)$item['product_amount'];
    $unit_price = $qty > 0 ? (int)($amount / $qty) : $amount;

    $pdf->Cell(15, 7, $idx++, 1, 0, 'C');
    $pdf->Cell(95, 7, substr($title, 0, 52), 1, 0, 'L');
    $pdf->Cell(20, 7, $qty, 1, 0, 'C');
    $pdf->Cell(25, 7, number_format($unit_price), 1, 0, 'R');
    $pdf->Cell(25, 7, number_format($amount), 1, 1, 'R');
}

// Totals Summary
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, '', 0, 0);
$pdf->Cell(25, 7, 'Subtotal:', 1, 0, 'L', true);
$pdf->Cell(25, 7, 'PKR ' . number_format($grand_total), 1, 1, 'R', true);

$pdf->Cell(130, 7, '', 0, 0);
$pdf->Cell(25, 7, 'Shipping:', 1, 0, 'L', true);
$pdf->Cell(25, 7, 'FREE', 1, 1, 'R', true);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(217, 119, 6);
$pdf->Cell(130, 8, '', 0, 0);
$pdf->Cell(25, 8, 'Total Due:', 1, 0, 'L', true);
$pdf->Cell(25, 8, 'PKR ' . number_format($grand_total), 1, 1, 'R', true);

// Footer notes
$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 116, 139);
$pdf->MultiCell(180, 5, "Terms & Conditions:\nAll furniture pieces are hand-inspected before transit. Payment is strictly collected in cash upon doorstep delivery. Thank you for choosing FurniCraft!", 0, 'L');

$pdf->Output('I', 'FurniCraft-Invoice-' . $invoice_no . '.pdf');
exit;
?>
