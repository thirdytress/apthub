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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Leases | ApartmentHub Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/css/air.css" rel="stylesheet">
<style>
:root {
  --primary-dark: #2c3e50;
  --primary-blue: #3498db;
  --accent-gold: #d4af37;
  --warm-beige: #f5f1e8;
  --soft-gray: #95a5a6;
  --deep-navy: #1a252f;
  --luxury-gold: #c9a961;
  --earth-brown: #8b7355;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-image: repeating-linear-gradient(90deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px),
                    repeating-linear-gradient(0deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px);
  z-index: 0;
  pointer-events: none;
}

.container {
  position: relative;
  z-index: 1;
  margin-top: 60px;
}

h2 {
  font-weight: 700;
  color: var(--primary-dark);
  font-size: 2.2rem;
  margin-bottom: 2rem;
  position: relative;
  display: inline-block;
}
h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 0;
  width: 80px;
  height: 4px;
  background: var(--accent-gold);
  border-radius: 2px;
}

.table-responsive {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  margin-bottom: 3rem;
  background: white;
}

.table {
  margin-bottom: 0;
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

.navbar {
  background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.navbar-brand {
  font-weight: 700;
  color: var(--accent-gold) !important;
}
.btn-logout {
  background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
  border: none;
  color: var(--deep-navy);
  border-radius: 20px;
  padding: 8px 20px;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(212,175,55,0.3);
}
.btn-logout:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(212,175,55,0.5);
}

.text-muted {
  color: var(--earth-brown) !important;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 2rem 0;
}

.floating-decoration {
  position: fixed;
  pointer-events: none;
  z-index: 0;
}
.deco-1 {
  top: 15%; left: 5%;
  width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(212,175,55,0.1), transparent);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}
.deco-2 {
  bottom: 20%; right: 8%;
  width: 200px; height: 200px;
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
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand fs-4" href="dashboard.php">
      <i class="bi bi-building me-2"></i>ApartmentHub Admin
    </a>
    <a href="../logout.php" class="btn btn-logout">
      <i class="bi bi-box-arrow-right me-1"></i> Logout
    </a>
  </div>
</nav>

<div class="container">
  <h2 class="text-primary mb-4">Active Leases</h2>

  <?php if ($leases): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Tenant</th>
          <th>Apartment</th>
          <th>Location</th>
          <th>Monthly Rate (₱)</th>
          <th>Start Date</th>
          <th>End Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leases as $i => $lease): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($lease['firstname'].' '.$lease['lastname']) ?> (<?= htmlspecialchars($lease['tenant_username']) ?>)</td>
          <td><?= htmlspecialchars($lease['apartment_name']) ?></td>
          <td><?= htmlspecialchars($lease['Location']) ?></td>
          <td><?= number_format($lease['MonthlyRate'], 2) ?></td>
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
<script src="../assets/js/theme.js"></script>
</body>
</html>

