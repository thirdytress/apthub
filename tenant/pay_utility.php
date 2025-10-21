<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['utility_id'])) {
  $utility_id = $_POST['utility_id'];

  $db = new Database();
  $conn = $db->connect();

  // Update the status to "Paid"
  $stmt = $conn->prepare("UPDATE utilities SET status = 'Paid' WHERE id = ? AND tenant_id = ?");
  $stmt->execute([$utility_id, $_SESSION['user_id']]);

  $_SESSION['message'] = "Utility bill has been marked as paid successfully.";
  header("Location: utilities.php");
  exit();
} else {
  header("Location: utilities.php");
  exit();
}
?>
