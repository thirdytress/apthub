<?php
require_once "../classes/database.php";
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$payment_id = $_POST['rent_id'] ?? null; // this is actually the payment_id
$method = $_POST['payment_method'] ?? null;
$ref = $_POST['reference_number'] ?? null;
$tenant_id = $_SESSION['user_id'];

if (!$payment_id || !$method) {
    die("Invalid input.");
}

// Optional: handle GCash receipt upload
$receiptPath = null;
if (!empty($_FILES['gcash_receipt']['name'])) {
    $targetDir = "../uploads/receipts/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["gcash_receipt"]["name"]);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["gcash_receipt"]["tmp_name"], $targetFile)) {
        $receiptPath = "uploads/receipts/" . $fileName;
    }
}

// Update the payments table
$stmt = $conn->prepare("
    UPDATE payments 
    SET 
        status = 'Paid', 
        payment_method = :method, 
        reference_number = :ref, 
        date_paid = NOW()
    WHERE payment_id = :pid AND tenant_id = :tenant_id
");
$stmt->execute([
    ':method' => $method,
    ':ref' => $ref,
    ':pid' => $payment_id,
    ':tenant_id' => $tenant_id
]);

header("Location: ../tenant/tenant_pay_rent.php?success=1");
exit;
