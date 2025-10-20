<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $admin = $db->getAdminById($admin_id);
    if (!$admin || !password_verify($current_password, $admin['password'])) {
        echo "<script>alert('Current password is incorrect.'); window.history.back();</script>"; exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match.'); window.history.back();</script>"; exit();
    }

    $db->changeAdminPassword($admin_id, password_hash($new_password, PASSWORD_DEFAULT));
    echo "<script>alert('Password changed successfully!'); window.location.href='dashboard.php';</script>";
}
?>
<!-- HTML form remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow col-md-6 mx-auto">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Change Password</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
                <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
