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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="text-primary mb-4">Manage Utilities</h2>

  <!-- Add Form -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Add Utility Record</div>
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
        <div class="mt-3 text-end">
          <button type="submit" class="btn btn-success">Add Record</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Utilities Table -->
  <div class="card">
    <div class="card-header bg-secondary text-white">All Utility Records</div>
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
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
</div>

</body>
</html>
