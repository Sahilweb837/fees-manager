<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    die("Invoice ID missing");
}

$invoice_id = sanitize($_GET['id']);

$query = "SELECT p.*, s.first_name, s.last_name, s.admission_no, s.roll_no, s.class, c.category_name 
          FROM fees_payments p 
          JOIN students s ON p.student_id = s.id 
          JOIN fees_categories c ON p.fee_category_id = c.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Invoice not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $payment['invoice_no']; ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #f39c12; font-weight: bold; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #f39c12; color: #fff; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #f39c12; font-weight: bold; }
        .print-btn { margin-bottom: 20px; background: #f39c12; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; }
        @media print { .print-btn { display: none; } .invoice-box { border: none; box-shadow: none; } }
    </style>
</head>
<body>

    <div style="text-align: center;">
        <button class="print-btn" onclick="window.print()">Print Invoice</button>
    </div>

    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">ERP Pro</td>
                            <td>
                                Invoice #: <?php echo $payment['invoice_no']; ?><br>
                                Created: <?php echo date('d M, Y', strtotime($payment['payment_date'])); ?><br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>School Name</strong><br>
                                123 Education Lane<br>
                                City, State 12345
                            </td>
                            <td>
                                <strong>Student Info</strong><br>
                                <?php echo $payment['first_name'] . ' ' . $payment['last_name']; ?><br>
                                Adm No: <?php echo $payment['admission_no']; ?><br>
                                Class: <?php echo $payment['class']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Description</td>
                <td>Amount</td>
            </tr>

            <tr class="item">
                <td><?php echo $payment['category_name']; ?></td>
                <td><?php echo formatCurrency($payment['total_amount']); ?></td>
            </tr>

            <tr class="item">
                <td>Paid Amount</td>
                <td><?php echo formatCurrency($payment['paid_amount']); ?></td>
            </tr>

            <tr class="item">
                <td>Fine / Late Fee</td>
                <td><?php echo formatCurrency($payment['fine']); ?></td>
            </tr>

            <tr class="item last">
                <td>Discount</td>
                <td><?php echo formatCurrency($payment['discount']); ?></td>
            </tr>

            <tr class="total">
                <td></td>
                <td>Due: <?php echo formatCurrency($payment['due_amount']); ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #aaa;">
            This is a computer generated invoice and does not require a physical signature.
        </div>
    </div>
</body>
</html>
