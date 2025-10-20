<?php
session_start();
require_once "../classes/database.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$payments = $db->getAllPayments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Payments | ApartmentHub</title>
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
      --deep-navy: #1a252f;
      --luxury-gold: #c9a961;
      --earth-brown: #8b7355;
    }
    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      position: relative;
    }
    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2rem;
      margin-bottom: 1.5rem;
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
    .badge {
      font-size: 0.9rem;
      padding: 0.6rem 1rem;
      border-radius: 20px;
      font-weight: 600;
    }
    .bg-success {
      background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%) !important;
    }
    .bg-danger {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
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
  <div class="floating-decoration deco-1"></div>
  <div class="floating-decoration deco-2"></div>

  <div class="container mt-5">
    <h2 class="text-primary mb-4">Manage Tenant Payments</h2>

    <?php if ($payments): ?>
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead>
            <tr>
              <th>Tenant</th>
              <th>Amount</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Date Paid</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></td>
              <td>₱<?= number_format($p['amount'],2) ?></td>
              <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
              <td>
                <span class="badge bg-<?= $p['status'] === 'Paid' ? 'success' : 'danger' ?>">
                  <?= htmlspecialchars($p['status']) ?>
                </span>
              </td>
              <td><?= $p['date_paid'] ? date('M d, Y h:i A', strtotime($p['date_paid'])) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No payment records found.</p>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

