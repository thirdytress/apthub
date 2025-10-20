<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

$stmt = $db->connect()->prepare("
  SELECT id, subject, description, status, created_at 
  FROM maintenance_requests 
  WHERE tenant_id = ? 
  ORDER BY created_at DESC
");
$stmt->execute([$tenant_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Maintenance Requests | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
      --soft-gray: #95a5a6;
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
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-image:
        repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      pointer-events: none;
      z-index: 0;
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
      border-radius: 50%;
    }

    .deco-1 {
      top: 15%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 20%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-30px); }
    }

    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2rem;
      position: relative;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 80px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    .btn-outline-primary {
      border-radius: 20px;
      border: 2px solid var(--primary-blue);
      color: var(--primary-blue);
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
      background: var(--primary-blue);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(52,152,219,0.3);
    }

    .card {
      border: none;
      border-radius: 25px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark), var(--primary-blue), var(--accent-gold));
      transform: scaleX(0);
      transition: transform 0.5s ease;
    }

    .card:hover::before {
      transform: scaleX(1);
    }

    table {
      border-radius: 15px;
      overflow: hidden;
    }

    thead.table-primary {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      color: white;
    }

    tbody tr:hover {
      background: rgba(212, 175, 55, 0.08);
      transition: background 0.3s ease;
    }

    .badge {
      font-size: 0.85rem;
      padding: 0.5em 0.8em;
      border-radius: 12px;
    }

    .badge.bg-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    }

    .badge.bg-warning {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      color: #fff !important;
    }

    .badge.bg-secondary {
      background: linear-gradient(135deg, #95a5a6, #7f8c8d) !important;
    }

    td, th {
      vertical-align: middle;
    }

    .text-muted {
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-wrench-adjustable me-2"></i>My Maintenance Requests</h2>
    <a href="maintenance_request.php" class="btn btn-outline-primary">
      <i class="bi bi-plus-lg me-1"></i>New Request
    </a>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Status</th>
            <th>Submitted On</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests): ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['subject']) ?></td>
                <td><?= htmlspecialchars($r['description']) ?></td>
                <td>
                  <span class="badge bg-<?=
                    $r['status'] === 'Completed' ? 'success' :
                    ($r['status'] === 'In Progress' ? 'warning' : 'secondary') ?>">
                    <?= $r['status'] ?>
                  </span>
                </td>
                <td><?= date("M d, Y h:i A", strtotime($r['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                You haven’t submitted any maintenance requests yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>

