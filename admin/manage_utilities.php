<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$conn = $db->connect();

// Fetch tenants
$tenants = $conn->query("SELECT tenant_id, CONCAT(firstname, ' ', lastname) AS fullname FROM tenants ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding utility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_id'])) {
  $tenant_id = $_POST['tenant_id'];
  $due_date = $_POST['due_date'];
  $electricity_bill = $_POST['electricity_bill'];
  $water_bill = $_POST['water_bill'];

  if (!empty($electricity_bill)) {
    $stmt = $conn->prepare("INSERT INTO utilities (tenant_id, type, amount, due_date, status) VALUES (?, 'Electricity', ?, ?, 'Unpaid')");
    $stmt->execute([$tenant_id, $electricity_bill, $due_date]);
  }

  if (!empty($water_bill)) {
    $stmt = $conn->prepare("INSERT INTO utilities (tenant_id, type, amount, due_date, status) VALUES (?, 'Water', ?, ?, 'Unpaid')");
    $stmt->execute([$tenant_id, $water_bill, $due_date]);
  }

  header("Location: manage_utilities.php?msg=Added+Successfully");
  exit();
}

// Fetch utilities with tenant names
$utilities = $conn->query("SELECT u.*, CONCAT(t.firstname, ' ', t.lastname) AS tenant_name 
                           FROM utilities u 
                           JOIN tenants t ON u.tenant_id = t.tenant_id 
                           ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Utilities | Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --luxury-gold: #c9a961;
      --warm-beige: #f5f1e8;
      --soft-gray: #95a5a6;
      --deep-navy: #1a252f;
    }
    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
    }
    h2 {
      font-weight: 700;
      color: var(--primary-dark);
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
    .btn-back {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.4s ease;
    }
    .btn-back:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--soft-gray) 100%);
    }
    .card {
      border-radius: 25px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      background: white;
      border: 1px solid rgba(212,175,55,0.2);
    }
    .card-header {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
      color: white;
      font-weight: 600;
      border-radius: 20px 20px 0 0;
    }
    .btn-success {
      background: linear-gradient(135deg, var(--accent-gold), var(--luxury-gold));
      border: none;
      color: var(--deep-navy);
      font-weight: 700;
      border-radius: 20px;
    }
    .table thead {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
      color: white;
    }
    .table tbody tr:hover {
      background: rgba(212,175,55,0.05);
    }
    .badge {
      border-radius: 20px;
      padding: 0.6rem 1rem;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Utilities</h2>
    <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
  </div>

  <!-- Add Utility Form -->
  <div class="card mb-5">
    <div class="card-header">Add Utility Record</div>
    <div class="card-body">
      <form method="POST">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Tenant</label>
            <select name="tenant_id" class="form-select" required>
              <option value="">Select Tenant</option>
              <?php foreach ($tenants as $t): ?>
                <option value="<?= $t['tenant_id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Electricity Bill (₱)</label>
            <input type="number" step="0.01" name="electricity_bill" class="form-control" placeholder="e.g. 1200.50">
          </div>
          <div class="col-md-3">
            <label class="form-label">Water Bill (₱)</label>
            <input type="number" step="0.01" name="water_bill" class="form-control" placeholder="e.g. 450.00">
          </div>
        </div>
        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success px-5">Add Record</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Utilities Table -->
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead>
        <tr>
          <th>Tenant</th>
          <th>Type</th>
          <th>Amount (₱)</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Payment Mode</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($utilities as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['tenant_name']) ?></td>
          <td><?= htmlspecialchars($u['type']) ?></td>
          <td><strong>₱<?= number_format($u['amount'], 2) ?></strong></td>
          <td><?= htmlspecialchars($u['due_date']) ?></td>
          <td>
            <span class="badge bg-<?= $u['status'] == 'Paid' ? 'success' : ($u['status'] == 'Pending' ? 'warning' : 'secondary') ?>">
              <?= htmlspecialchars($u['status']) ?>
            </span>
          </td>
          <td><?= $u['payment_mode'] ? htmlspecialchars($u['payment_mode']) : '<span class="text-muted">—</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
