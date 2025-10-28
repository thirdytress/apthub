<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if (isset($_GET['delete_id'])) {
    $db->deleteTenant($_GET['delete_id']);
    echo "<script>alert('Tenant deleted successfully!'); window.location.href='manage_tenants.php';</script>";
}

$tenants = $db->getAllTenants();
?>
<!-- HTML table remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Tenants | ApartmentHub Admin</title>

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
      background-image: repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
                        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      z-index: 0;
      pointer-events: none;
    }

    .container { position: relative; z-index: 1; margin-top: 60px; }

    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2.2rem;
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

    .btn-add {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      color: var(--deep-navy);
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
      transition: all 0.4s ease;
    }

    .btn-add:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
      color: var(--deep-navy);
    }

    .table-responsive {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
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
      border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(212, 175, 55, 0.05), transparent);
      transform: translateX(5px);
    }

    .table tbody td {
      padding: 1.2rem 1rem;
      color: var(--earth-brown);
      font-weight: 500;
      vertical-align: middle;
    }

    .btn-danger {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      border: none;
      border-radius: 15px;
      padding: 6px 15px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
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
      top: 15%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 20%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
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
  <div class="floating-decoration deco-1"></div>
  <div class="floating-decoration deco-2"></div>

  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Manage Tenants</h2>
      <a href="add_tenant.php" class="btn btn-add"><i class="bi bi-person-plus"></i> Add Tenant</a>
    </div>

    <div class="table-responsive">
      <table class="table text-center align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($tenants)): ?>
            <?php foreach ($tenants as $i => $t): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($t['firstname'] . ' ' . $t['lastname']) ?></td>
                <td><?= htmlspecialchars($t['username']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= htmlspecialchars($t['phone']) ?></td>
                <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                <td>
                  <a href="manage_tenants.php?delete_id=<?= $t['tenant_id'] ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('Are you sure you want to delete this tenant?');">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-muted text-center">No tenants found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/theme.js"></script>
</body>
</html>


