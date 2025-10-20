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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --primary-dark: #2c3e50;
    --primary-blue: #3498db;
    --accent-gold: #d4af37;
    --warm-beige: #f5f1e8;
    --luxury-gold: #c9a961;
    --deep-navy: #1a252f;
    --earth-brown: #8b7355;
  }

  body {
    background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    position: relative;
  }

  body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: repeating-linear-gradient(90deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px),
                      repeating-linear-gradient(0deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px);
    z-index: 0;
    pointer-events: none;
  }

  .navbar {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .navbar-brand {
    font-weight: 700;
    color: var(--primary-dark) !important;
  }

  .btn-logout {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    border-radius: 20px;
    padding: 8px 18px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
  }

  h3 {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: 2rem;
    position: relative;
    display: inline-block;
  }

  h3::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 80px;
    height: 4px;
    background: var(--accent-gold);
    border-radius: 2px;
  }

  .table-responsive {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    margin-top: 2rem;
    background: white;
  }

  .table thead {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
  }

  .table thead th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1.2rem 1rem;
    border: none;
    font-size: 0.9rem;
  }

  .table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(212,175,55,0.1);
  }

  .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(212,175,55,0.05), transparent);
    transform: translateX(5px);
  }

  .table tbody td {
    padding: 1.2rem 1rem;
    color: var(--earth-brown);
    font-weight: 500;
    vertical-align: middle;
  }

  .badge {
    font-weight: 600;
    border-radius: 10px;
    padding: 6px 10px;
  }

  .btn-success, .btn-danger {
    border: none;
    border-radius: 15px;
    padding: 6px 15px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
  }

  .btn-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  }

  .btn-success:hover, .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }

  .floating-decoration {
    position: fixed;
    pointer-events: none;
    z-index: 0;
  }

  .deco-1 {
    top: 15%;
    left: 5%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(212,175,55,0.1), transparent);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
  }

  .deco-2 {
    bottom: 20%;
    right: 8%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(52,152,219,0.1), transparent);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-30px); }
  }
</style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<nav class="navbar navbar-expand-lg mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-building"></i> ApartmentHub Admin</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-logout"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3>Tenant Apartment Applications</h3>

  <?php if ($applications): ?>
  <div class="table-responsive">
    <table class="table table-hover text-center align-middle mb-0">
      <thead>
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
          <td><?= htmlspecialchars($app['firstname'] . ' ' . $app['lastname']) ?> <br><small class="text-muted">(<?= htmlspecialchars($app['tenant_username']) ?>)</small></td>
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
    <p class="text-muted mt-4">No applications found.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

