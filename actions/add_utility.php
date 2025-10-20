<?php
session_start();
require_once "../classes/database.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();

$tenant_id = $_POST['tenant_id'];
$type = $_POST['type'];
$amount = $_POST['amount'];
$due_date = $_POST['due_date'];

if ($db->addUtility($tenant_id, $type, $amount, $due_date)) {
  header("Location: ../admin/utilities.php?msg=Utility+Added");
} else {
  header("Location: ../admin/utilities.php?msg=Failed+to+Add");
}
exit();
?>
