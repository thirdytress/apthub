<?php
session_start();
require_once "../classes/database.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$payments = $db->getAllPayments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Payments | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="text-primary mb-3">Manage Tenant Payments</h3>
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>Tenant</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Date Paid</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></td>
          <td>₱<?= number_format($p['amount'],2) ?></td>
          <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
          <td>
            <span class="badge bg-<?= $p['status'] === 'Paid' ? 'success' : 'danger' ?>">
              <?= $p['status'] ?>
            </span>
          </td>
          <td><?= $p['date_paid'] ? date('M d, Y h:i A', strtotime($p['date_paid'])) : '-' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
