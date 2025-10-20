<?php
session_start();
require_once "../classes/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'All fields are required']);
  exit;
}

$db = new Database();
$conn = $db->connect();

// ✅ Hardcoded admin check (inside tenants table concept)
if ($username === 'admin' && $password === 'admin123') {
  $_SESSION['user_id'] = 0;
  $_SESSION['username'] = 'admin';
  $_SESSION['role'] = 'admin';
  $_SESSION['name'] = 'System Administrator';

  echo json_encode([
    'success' => true,
    'name' => 'System Administrator',
    'redirect' => 'admin/dashboard.php'
  ]);
  exit;
}

// ✅ Tenant login
$stmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
$stmt->execute([$username, $username]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tenant && password_verify($password, $tenant['password'])) {
  $_SESSION['user_id'] = $tenant['tenant_id'];
  $_SESSION['username'] = $tenant['username'];
  $_SESSION['email'] = $tenant['email'];
  $_SESSION['role'] = 'tenant';
  $_SESSION['name'] = $tenant['firstname'] . ' ' . $tenant['lastname'];

  echo json_encode([
    'success' => true,
    'name' => $tenant['firstname'],
    'redirect' => 'tenant/dashboard.php'
  ]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
?>
