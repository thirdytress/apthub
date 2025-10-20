<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../actions/login.php");
    exit();
}

$db = new Database();

// ✅ Get utility ID and status from the URL (GET request)
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($id && $status) {
    $valid_status = ['Paid', 'Unpaid', 'Pending'];
    
    // check kung valid yung status bago i-update
    if (in_array($status, $valid_status)) {
        $db->updateUtilityStatus($id, $status);
        $_SESSION['success'] = "Utility status updated successfully!";
    } else {
        $_SESSION['error'] = "Invalid status value.";
    }
} else {
    $_SESSION['error'] = "Missing utility ID or status.";
}

// ✅ Redirect back to manage_utilities.php
header("Location: manage_utilities.php");
exit();
?>
