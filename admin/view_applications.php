<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'approve') $db->approveApplication($id);
    elseif ($_GET['action'] === 'reject') $db->rejectApplication($id);
    header("Location: view_applications.php"); exit();
}

$applications = $db->getAllApplications();
?>
<!-- HTML table remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Applications | Admin</title>
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
  <h3 class="text-primary mb-4">Tenant Apartment Applications</h3>

  <?php if ($applications): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tenant</th>
          <th>Apartment</th>
          <th>Location</th>
          <th>Date Applied</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $i => $app): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($app['firstname'] . ' ' . $app['lastname']) ?> (<?= htmlspecialchars($app['tenant_username']) ?>)</td>
          <td><?= htmlspecialchars($app['apartment_name']) ?></td>
          <td><?= htmlspecialchars($app['Location']) ?></td>
          <td><?= date('M d, Y H:i', strtotime($app['date_applied'])) ?></td>
          <td>
            <?php if ($app['app_status'] === 'Pending'): ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php elseif ($app['app_status'] === 'Approved'): ?>
              <span class="badge bg-success">Approved</span>
            <?php else: ?>
              <span class="badge bg-danger">Rejected</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($app['app_status'] === 'Pending'): ?>
              <a href="?action=approve&id=<?= $app['application_id'] ?>" class="btn btn-success btn-sm mb-1">Approve</a>
              <a href="?action=reject&id=<?= $app['application_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
            <?php else: ?>
              <span class="text-muted">No action</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p class="text-muted">No applications found.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
