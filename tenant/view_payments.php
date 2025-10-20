<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Generate monthly billing before showing payments
$db->generateMonthlyPayments();

// Get tenant's payments
$payments = $db->getTenantPayments($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="text-primary mb-3"><i class="bi bi-credit-card me-2"></i>My Payments</h3>

  <?php if ($payments): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Apartment</th>
            <th>Amount (₱)</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Date Paid</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['apartment_name'] ?? 'N/A') ?></td>
              <td><?= number_format($p['amount'], 2) ?></td>
              <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
              <td>
                <span class="badge bg-<?= strtolower($p['status']) === 'paid' ? 'success' : 'warning' ?>">
                  <?= htmlspecialchars($p['status']) ?>
                </span>
              </td>
              <td><?= $p['date_paid'] ? date('M d, Y', strtotime($p['date_paid'])) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted">No payment records yet.</p>
  <?php endif; ?>
</div>
</body>
</html>
