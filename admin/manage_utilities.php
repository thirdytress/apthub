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

// Handle insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tenant_id = $_POST['tenant_id'];
  $month_year = $_POST['month_year'];
  $electricity_usage = $_POST['electricity_usage'];
  $water_usage = $_POST['water_usage'];
  $electricity_bill = $_POST['electricity_bill'];
  $water_bill = $_POST['water_bill'];
  $total_bill = $electricity_bill + $water_bill;

  $stmt = $conn->prepare("INSERT INTO utilities (tenant_id, month_year, electricity_usage, water_usage, electricity_bill, water_bill, total_bill)
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$tenant_id, $month_year, $electricity_usage, $water_usage, $electricity_bill, $water_bill, $total_bill]);
  header("Location: manage_utilities.php?msg=Added+Successfully");
  exit();
}

// Fetch utilities
$utilities = $conn->query("SELECT u.*, CONCAT(t.firstname, ' ', t.lastname) AS tenant_name 
                           FROM utilities u 
                           JOIN tenants t ON u.tenant_id = t.tenant_id 
                           ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Utilities | ApartmentHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
      overflow-x: hidden;
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }
    .deco-1 {
      top: 10%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212,175,55,0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }
    .deco-2 {
      bottom: 15%;
      right: 10%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52,152,219,0.1), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }
    @keyframes float {
      0%,100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
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

    .btn-back {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.4s ease;
      box-shadow: 0 4px 15px rgba(149,165,166,0.3);
    }
    .btn-back:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--soft-gray) 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149,165,166,0.5);
    }

    .card {
      border: none;
      border-radius: 25px;
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      border: 2px solid rgba(212,175,55,0.2);
      transition: all 0.4s ease;
    }
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 30px 80px rgba(0,0,0,0.25);
    }
    .card-header {
      font-weight: 600;
      font-size: 1.1rem;
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
      color: white;
      border-radius: 20px 20px 0 0;
      border-bottom: 3px solid var(--accent-gold);
    }

    label {
      font-weight: 500;
      color: var(--deep-navy);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border: none;
      color: var(--deep-navy);
      border-radius: 20px;
      font-weight: 700;
      box-shadow: 0 4px 20px rgba(212,175,55,0.4);
      transition: all 0.4s ease;
    }
    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(212,175,55,0.6);
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
      padding: 1rem;
      border: none;
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
      color: var(--earth-brown);
      font-weight: 500;
      padding: 1rem;
    }

    .badge {
      padding: 0.6rem 1rem;
      border-radius: 20px;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Utilities</h2>
    <button class="btn btn-back" onclick="history.back()">
      <i class="bi bi-arrow-left"></i> Back
    </button>
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
            <label class="form-label">Month-Year</label>
            <input type="text" name="month_year" class="form-control" placeholder="e.g. October 2025" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Electricity (kWh)</label>
            <input type="number" step="0.01" name="electricity_usage" class="form-control" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Water (m³)</label>
            <input type="number" step="0.01" name="water_usage" class="form-control" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Electricity Bill</label>
            <input type="number" step="0.01" name="electricity_bill" class="form-control" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Water Bill</label>
            <input type="number" step="0.01" name="water_bill" class="form-control" required>
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
    <table class="table table-bordered align-middle text-center">
      <thead>
        <tr>
          <th>Tenant</th>
          <th>Month</th>
          <th>Electricity (kWh)</th>
          <th>Water (m³)</th>
          <th>Electricity Bill</th>
          <th>Water Bill</th>
          <th>Total</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($utilities as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['tenant_name']) ?></td>
          <td><?= htmlspecialchars($u['month_year']) ?></td>
          <td><?= $u['electricity_usage'] ?></td>
          <td><?= $u['water_usage'] ?></td>
          <td>₱<?= number_format($u['electricity_bill'], 2) ?></td>
          <td>₱<?= number_format($u['water_bill'], 2) ?></td>
          <td><strong>₱<?= number_format($u['total_bill'], 2) ?></strong></td>
          <td>
            <span class="badge bg-<?= $u['status'] == 'Paid' ? 'success' : 'warning' ?>">
              <?= $u['status'] ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

