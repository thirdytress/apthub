<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Request | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="text-center text-primary mb-4"><i class="bi bi-tools"></i> Submit Maintenance Request</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['msg']); ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
    <form action="../actions/submit_request.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" placeholder="Short summary (e.g., Broken Aircon)" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required></textarea>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-fill me-2"></i>Submit Request</button>
      </div>
    </form>

    <div class="text-center mt-3">
      <a href="dashboard.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
  </div>
</div>

</body>
</html>
