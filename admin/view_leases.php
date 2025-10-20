<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$leases = $db->getAllLeases();
?>
<!-- HTML table remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Leases | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
.navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="dashboard.php">ApartmentHub Admin</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <h3 class="text-primary mb-4">Active Leases</h3>

  <?php if ($leases): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tenant</th>
          <th>Apartment</th>
          <th>Location</th>
          <th>Monthly Rate</th>
          <th>Start Date</th>
          <th>End Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leases as $i => $lease): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($lease['firstname'] . ' ' . $lease['lastname']) ?> (<?= htmlspecialchars($lease['tenant_username']) ?>)</td>
          <td><?= htmlspecialchars($lease['apartment_name']) ?></td>
          <td><?= htmlspecialchars($lease['Location']) ?></td>
          <td>$<?= number_format($lease['MonthlyRate'],2) ?></td>
          <td><?= date('M d, Y', strtotime($lease['start_date'])) ?></td>
          <td><?= date('M d, Y', strtotime($lease['end_date'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p class="text-muted">No active leases found.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
