<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

// Approve / Reject logic
if (isset($_GET['approve'])) {
    $db->updateApplicationStatus($_GET['approve'], 'Approved');
}
if (isset($_GET['reject'])) {
    $db->updateApplicationStatus($_GET['reject'], 'Rejected');
}

$applications = $db->getAllApplications();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applications | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/air.css" rel="stylesheet">
</head>
<body class="bg-light">

<header class="header">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between">
      <a class="brand text-decoration-none fs-3" href="dashboard.php">ApartmentHub Admin</a>
      <div class="d-flex align-items-center gap-2">
        <a href="dashboard.php" class="btn btn-outline-secondary d-none d-md-inline"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="../logout.php" class="btn btn-dark"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
      </div>
    </div>
  </div>
</header>

<div class="container mt-4">
  <h3 class="text-primary mb-4">Tenant Applications</h3>

  <table class="table table-bordered table-striped bg-white">
    <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Apartment</th>
        <th>Status</th>
        <th>Date Applied</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $a): ?>
        <tr>
          <td><?= $a['application_id'] ?></td>
          <td><?= htmlspecialchars($a['firstname'] . ' ' . $a['lastname']) ?></td>
          <td><?= htmlspecialchars($a['apartment_name']) ?></td>
          <td><?= htmlspecialchars($a['status']) ?></td>
          <td><?= htmlspecialchars($a['date_applied']) ?></td>
          <td>
            <?php if ($a['status'] === 'Pending'): ?>
              <a href="?approve=<?= $a['application_id'] ?>" class="btn btn-success btn-sm">Approve</a>
              <a href="?reject=<?= $a['application_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
            <?php else: ?>
              <span class="text-muted">No Action</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script src="../assets/js/theme.js"></script>
</body>
</html>
