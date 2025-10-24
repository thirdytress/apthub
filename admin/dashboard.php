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
  <link href="../assets/css/air.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body{font-family:'Poppins',sans-serif}
    /* Admin cards aligned to site style */
    .dashboard-card{border:1px solid var(--ah-border);border-radius:16px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:transform .2s ease, box-shadow .2s;padding:1rem}
    .dashboard-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
    .dashboard-card .icon{font-size:1.8rem;color:var(--ah-brand)}
    .table thead th{background:#f7f7f7}
    .table tbody tr:hover{background:#fafafa}
  </style>
</head>
<body>

<!-- HEADER (unified with site) -->
<header class="header">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between">
      <a class="brand text-decoration-none fs-3" href="dashboard.php">ApartmentHub Admin</a>
      <div class="d-flex align-items-center gap-2">
        <a href="change_password.php" class="btn btn-outline-secondary d-none d-md-inline"><i class="bi bi-key"></i> Password</a>
        <a href="../logout.php" class="btn btn-dark"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </div>
    </div>
  </div>
  <div class="pt-2"></div>
  
</header>

<!-- MAIN DASHBOARD -->
<div class="container mb-5">
  <div class="p-3 p-md-4 mb-4 border rounded-3" style="background:#fff;border-color: var(--ah-border) !important;">
    <h3 class="h4 mb-2">Welcome, <?= htmlspecialchars($fullname ?: 'Admin'); ?>!</h3>
    <p class="text-muted mb-0">Manage tenants, applications, apartments, utilities, leases, and more.</p>

    <div class="row mt-3">
      <!-- Manage Tenants -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-people-fill icon"></i></div>
          <h5>Manage Tenants</h5>
          <p class="small text-muted"><?= $totalTenants ?> tenants</p>
          <a href="manage_tenants.php" class="btn btn-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Applications -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-file-earmark-text-fill icon"></i></div>
          <h5>View Applications</h5>
          <p class="small text-muted"><?= $totalApplications ?> applications</p>
          <a href="view_applications.php" class="btn btn-outline-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Add Apartment -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-building-fill icon"></i></div>
          <h5>Add Apartment</h5>
          <p class="small text-muted"><?= $totalApartments ?> apartments</p>
          <a href="add_apartment.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Leases -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-file-text-fill icon"></i></div>
          <h5>View Leases</h5>
          <p class="small text-muted"><?= $totalLeases ?> active leases</p>
          <a href="view_leases.php" class="btn btn-info btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Manage Payments -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-cash-coin icon"></i></div>
          <h5>Manage Payments</h5>
          <p class="small text-muted">View and update payment records</p>
          <a href="manage_payments.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Utilities -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
          <div class="mb-2"><i class="bi bi-droplet-half icon"></i></div>
          <h5>Utilities</h5>
          <p class="small text-muted"><?= $totalUtilities ?> bills</p>
          <a href="manage_utilities.php" class="btn btn-secondary btn-sm mt-auto">Manage</a>
        </div>
      </div>

      <!-- Change Password -->
      <div class="col-md-3 mb-4">
        <div class="text-center h-100 dashboard-card">
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

<footer class="footer py-5 mt-4">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>&copy; 2025 ApartmentHub Admin</div>
    <div>Unified UI with ApartmentHub</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

