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

    .btn-outline-secondary {
      border-radius: 20px;
      border: 2px solid var(--accent-gold);
      color: var(--accent-gold);
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
      background: var(--accent-gold);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(212,175,55,0.3);
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

    .card {
      border: none;
      border-radius: 25px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      overflow: hidden;
      position: relative;
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

    thead.table-light {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      color: white;
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(212, 175, 55, 0.08);
      transition: background 0.3s ease;
    }

    th, td {
      vertical-align: middle !important;
    }

    .badge {
      font-size: 0.9rem;
      padding: 0.5em 0.9em;
      border-radius: 12px;
    }

    .badge.bg-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    }

    .badge.bg-warning {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      color: white !important;
    }

    .text-muted {
      font-style: italic;
    }
  </style>
</head>
<body>

<!-- ✅ Back Button (Top Right Corner) -->
<a href="dashboard.php" class="btn btn-outline-secondary position-fixed top-0 end-0 m-4" style="z-index: 1000;">
  <i class="bi bi-arrow-left me-1"></i>Back
</a>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container mt-5">
  <h2 class="mb-4"><i class="bi bi-lightning-charge me-2"></i>My Utility Bills</h2>

  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered table-hover align-middle text-center mb-0">
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
            <tr>
              <td colspan="8" class="text-muted py-4">No utility records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
