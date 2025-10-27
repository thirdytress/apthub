<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$payment_id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
if ($payment_id <= 0) {
    header("Location: ../admin/manage_payments.php?msg=" . urlencode('Invalid payment ID.'));
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("UPDATE payments SET status = 'Paid', date_paid = NOW() WHERE payment_id = :pid");
    $stmt->execute([':pid' => $payment_id]);

    header("Location: ../admin/manage_payments.php?msg=" . urlencode('Payment marked as Paid.'));
    exit();
} catch (Exception $e) {
    header("Location: ../admin/manage_payments.php?msg=" . urlencode('Failed to update payment.'));
    exit();
}
