<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Generate monthly billing before showing payments
$db->generateMonthlyPayments();

// Get tenant's payments
$payments = $db->getTenantPayments($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments | ApartmentHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      min-height: 100vh;
      overflow-x: hidden;
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      border-radius: 50%;
      z-index: 0;
    }

    .deco-1 {
      top: 10%;
      left: 5%;
      width: 160px;
      height: 160px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 15%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-25px); }
    }

    h3 {
      color: var(--primary-dark);
      font-weight: 700;
      font-size: 1.9rem;
      position: relative;
      display: inline-block;
    }

    h3::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 0;
      width: 70px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 3px;
    }

    .card {
      border: none;
      border-radius: 25px;
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      color: #fff;
    }

    .table tbody tr:hover {
      background-color: rgba(212, 175, 55, 0.07);
      transition: background 0.3s ease;
    }

    .badge {
      font-size: 0.9rem;
      padding: 0.45em 0.8em;
      border-radius: 10px;
    }

    .badge.bg-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    }

    .badge.bg-warning {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      color: white !important;
    }

    .table-responsive {
      border-radius: 20px;
      overflow: hidden;
    }
  </style>
</head>
<body>
  <div class="floating-decoration deco-1"></div>
  <div class="floating-decoration deco-2"></div>

  <div class="container py-5">
    <h3 class="mb-4"><i class="bi bi-credit-card me-2 text-primary"></i>My Payments</h3>

    <?php if ($payments): ?>
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Apartment</th>
                  <th>Amount (₱)</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Date Paid</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['apartment_name'] ?? 'N/A') ?></td>
                    <td><?= number_format($p['amount'], 2) ?></td>
                    <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
                    <td>
                      <span class="badge bg-<?= strtolower($p['status']) === 'paid' ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars($p['status']) ?>
                      </span>
                    </td>
                    <td><?= $p['date_paid'] ? date('M d, Y', strtotime($p['date_paid'])) : '-' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card shadow-sm p-4 text-center text-muted fs-5">
        No payment records yet.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

