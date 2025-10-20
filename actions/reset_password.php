<?php
session_start();
require_once "../classes/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$token = trim($_POST['token'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($token) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'All fields are required']);
  exit;
}

$db = new Database();
$conn = $db->connect();

// Verify token
$stmt = $conn->prepare("SELECT tenant_id FROM tenants WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
  exit;
}

// Update password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE tenants SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE tenant_id = ?");

if ($stmt->execute([$hashedPassword, $user['tenant_id']])) {
  echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
}
?>