<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

// --- get admin fullname dynamically ---
$fullname = $_SESSION['fullname'] ?? '';
$username = $_SESSION['username'] ?? '';

if (empty($fullname) && !empty($username)) {
    $admin = $db->getAdminByUsername($username);
    if ($admin) {
        // Use the fullname column instead of firstname/lastname
        $fullname = $admin['fullname'] ?? 'Admin';
        $_SESSION['fullname'] = $fullname;
        $_SESSION['username'] = $admin['username'] ?? '';
    }
}


// --- fetch counts for dashboard cards ---
$totalTenants = $db->countTenants();
$totalApplications = $db->countApplications();
$totalApartments = $db->countApartments();
$totalLeases = $db->countLeases();
$totalUtilities = $db->countUtilities();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ApartmentHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
    }

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Floating glow background */
    .floating-decoration {
      position: fixed;
      border-radius: 50%;
      z-index: 0;
      pointer-events: none;
    }

    .deco-1 {
      top: 8%;
      left: 5%;
      width: 180px;
      height: 180px;
      background: radial-gradient(circle, rgba(212,175,55,0.1), transparent);
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 12%;
      right: 8%;
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(52,152,219,0.1), transparent);
      animation: float 7s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-25px); }
    }

    .navbar {
      background-color: #fff !important;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      z-index: 10;
    }

    .navbar-brand {
      color: var(--primary-dark) !important;
      font-weight: 700;
      font-size: 1.4rem;
    }

    .card {
      border: none;
      border-radius: 20px;
      background: linear-gradient(145deg, #ffffff, #f8f5f0);
      border: 2px solid rgba(212,175,55,0.2);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      position: relative;
      z-index: 1;
    }

    h3, h2 {
      font-weight: 700;
      color: var(--primary-dark);
      position: relative;
      display: inline-block;
    }

    h3::after, h2::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 0;
      width: 70px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    .dashboard-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      background: linear-gradient(145deg, #fff, #f9f6f0);
      border: 1px solid rgba(212,175,55,0.2);
      border-radius: 20px;
    }

    .dashboard-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }

    .dashboard-card .icon {
      font-size: 2.2rem;
      color: var(--primary-blue);
      transition: transform 0.3s ease, color 0.3s ease;
    }

    .dashboard-card:hover .icon {
      transform: scale(1.15);
      color: var(--accent-gold);
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      color: #fff;
    }

    .table tbody tr:hover {
      background-color: rgba(212,175,55,0.07);
      transition: background 0.3s ease;
    }

    .badge.bg-warning {
      background: linear-gradient(135deg, #f39c12, #e67e22);
      color: white;
    }

    .badge.bg-info {
      background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .badge.bg-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .btn-outline-danger {
      border-color: #e74c3c;
      color: #e74c3c;
    }

    .btn-outline-danger:hover {
      background-color: #e74c3c;
      color: white;
    }

    hr {
      border: 1px solid rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg mb-4">
  <div class="container">
    <a class="navbar-brand" href="#"><i class="bi bi-building-fill me-2 text-primary"></i>ApartmentHub Admin</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<!-- MAIN DASHBOARD -->
<div class="container mb-5">
  <div class="card p-4 mb-5">
    <h3>Welcome, <?= htmlspecialchars($fullname ?: 'Admin'); ?>!</h3>
    <hr>
    <p>This is your admin dashboard. You can manage tenants, applications, apartments, utilities, and maintenance requests here.</p>

    <div class="row mt-4">
      <!-- Manage Tenants -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-people-fill icon"></i></div>
          <h5>Manage Tenants</h5>
          <p class="small text-muted"><?= $totalTenants ?> tenants</p>
          <a href="manage_tenants.php" class="btn btn-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Applications -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-file-earmark-text-fill icon"></i></div>
          <h5>View Applications</h5>
          <p class="small text-muted"><?= $totalApplications ?> applications</p>
          <a href="view_applications.php" class="btn btn-outline-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Add Apartment -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-building-fill icon"></i></div>
          <h5>Add Apartment</h5>
          <p class="small text-muted"><?= $totalApartments ?> apartments</p>
          <a href="add_apartment.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Leases -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-file-text-fill icon"></i></div>
          <h5>View Leases</h5>
          <p class="small text-muted"><?= $totalLeases ?> active leases</p>
          <a href="view_leases.php" class="btn btn-info btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Manage Payments -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-cash-coin icon"></i></div>
          <h5>Manage Payments</h5>
          <p class="small text-muted">View and update payment records</p>
          <a href="manage_payments.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Utilities -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-droplet-half icon"></i></div>
          <h5>Utilities</h5>
          <p class="small text-muted"><?= $totalUtilities ?> bills</p>
          <a href="manage_utilities.php" class="btn btn-secondary btn-sm mt-auto">Manage</a>
        </div>
      </div>

      <!-- Change Password -->
      <div class="col-md-3 mb-4">
        <div class="card text-center h-100 p-3 dashboard-card">
          <div class="mb-2"><i class="bi bi-key-fill icon"></i></div>
          <h5>Change Password</h5>
          <p class="small text-muted">Secure your account</p>
          <a href="change_password.php" class="btn btn-warning btn-sm mt-auto">Go</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MAINTENANCE REQUESTS -->
  <section class="mt-5">
    <h2><i class="bi bi-tools me-2 text-primary"></i>Maintenance Requests</h2>

    <?php
    $stmt = $db->connect()->query("
      SELECT r.*, CONCAT(t.firstname, ' ', t.lastname) AS tenant_name
      FROM maintenance_requests r
      JOIN tenants t ON r.tenant_id = t.tenant_id
      ORDER BY r.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($requests): ?>
      <div class="table-responsive shadow-sm rounded-3 mt-3">
        <table class="table table-hover table-bordered align-middle text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tenant</th>
              <th>Subject</th>
              <th>Description</th>
              <th>Status</th>
              <th>Created</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $req): ?>
              <tr>
                <td><?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['tenant_name']) ?></td>
                <td><?= htmlspecialchars($req['subject']) ?></td>
                <td><?= htmlspecialchars($req['description']) ?></td>
                <td>
                  <span class="badge bg-<?= 
                    $req['status'] === 'Pending' ? 'warning' :
                    ($req['status'] === 'In Progress' ? 'info' : 'success') ?>">
                    <?= htmlspecialchars($req['status']) ?>
                  </span>
                </td>
                <td><?= date('M d, Y h:i A', strtotime($req['created_at'])) ?></td>
                <td>
                  <form action="../actions/update_request.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <select name="status" class="form-select form-select-sm d-inline w-auto">
                      <option <?= $req['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                      <option <?= $req['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                      <option <?= $req['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                    <button class="btn btn-primary btn-sm">Update</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted mt-3">No maintenance requests yet.</p>
    <?php endif; ?>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

