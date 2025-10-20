<?php
require_once "../classes/database.php";
session_start();

if (!isset($_POST['payment_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../tenant/pay_rent.php");
    exit();
}

$db = new Database();
$db->markPaymentPaid($_POST['payment_id']);

header("Location: ../tenant/pay_rent.php?success=1");
exit();
?>
