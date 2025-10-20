<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
$subject = trim($_POST['subject']);
$description = trim($_POST['description']);

if (!empty($subject) && !empty($description)) {
  $stmt = $db->connect()->prepare("INSERT INTO maintenance_requests (tenant_id, subject, description) VALUES (?, ?, ?)");
  $stmt->execute([$tenant_id, $subject, $description]);
  header("Location: ../tenant/maintenance_request.php?msg=Request+Submitted");
  exit();
} else {
  header("Location: ../tenant/maintenance_request.php?error=Missing+Fields");
  exit();
}
?>
