<?php
session_start();
require_once "../classes/database.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$id = $_POST['id'];

if ($db->deleteUtility($id)) {
  header("Location: ../admin/utilities.php?msg=Deleted+Successfully");
} else {
  header("Location: ../admin/utilities.php?msg=Delete+Failed");
}
exit();
?>
