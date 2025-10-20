<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Generate monthly billing automatically
$db->generateMonthlyPayments();

$payments = $db->getTenantPayments($tenant_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pay Rent | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-4 text-primary">My Rent Payments</h3>
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Apartment</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= $p['payment_id'] ?></td>
          <td><?= htmlspecialchars($p['apartment_name']) ?></td>
          <td>₱<?= number_format($p['amount'], 2) ?></td>
          <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
          <td>
            <span class="badge bg-<?= $p['status'] === 'Paid' ? 'success' : 'warning' ?>">
              <?= $p['status'] ?>
            </span>
          </td>
          <td>
            <?php if ($p['status'] === 'Unpaid'): ?>
              <form action="../actions/pay_rent_action.php" method="POST">
                <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                <button class="btn btn-success btn-sm">Pay Now</button>
              </form>
            <?php else: ?>
              <span class="text-success fw-bold">Paid</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
