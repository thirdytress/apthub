<?php
session_start();
require_once "../classes/database.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();

$id = $_POST['id'];
$status = $_POST['status'];

if ($db->updateUtilityStatus($id, $status)) {
  header("Location: ../admin/utilities.php?msg=Status+Updated");
} else {
  header("Location: ../admin/utilities.php?msg=Update+Failed");
}
exit();
?>
