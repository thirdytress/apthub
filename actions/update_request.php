<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if ($id && $status) {
  $stmt = $db->connect()->prepare("UPDATE maintenance_requests SET status = ? WHERE id = ?");
  $stmt->execute([$status, $id]);
}

header("Location: ../admin/dashboard.php?msg=Status+Updated");
exit();
?>
