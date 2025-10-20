<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$conn = $db->connect();
$tenant_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM utilities WHERE tenant_id = ? ORDER BY created_at DESC");
$stmt->execute([$tenant_id]);
$utilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Utilities | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="text-primary mb-4">My Utility Bills</h2>

  <div class="card shadow-sm">
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Month</th>
            <th>Electricity (kWh)</th>
            <th>Water (m³)</th>
            <th>Electricity Bill</th>
            <th>Water Bill</th>
            <th>Total Bill</th>
            <th>Status</th>
            <th>Generated</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($utilities): ?>
            <?php foreach ($utilities as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['month_year']) ?></td>
              <td><?= $u['electricity_usage'] ?></td>
              <td><?= $u['water_usage'] ?></td>
              <td>₱<?= number_format($u['electricity_bill'], 2) ?></td>
              <td>₱<?= number_format($u['water_bill'], 2) ?></td>
              <td><strong>₱<?= number_format($u['total_bill'], 2) ?></strong></td>
              <td>
                <span class="badge bg-<?= $u['status'] === 'Paid' ? 'success' : 'warning' ?>">
                  <?= $u['status'] ?>
                </span>
              </td>
              <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-muted">No utility records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
