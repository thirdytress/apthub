<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Mark as read
$db->markNotificationsRead($tenant_id);

// Fetch notifications
$notifications = $db->getNotifications($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Notifications | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/air.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%); font-family: 'Poppins', sans-serif; }
    .card { border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); border: 1px solid rgba(201,169,97,.25) }
  </style>
</head>
<body>
<header class="header">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between">
      <a class="brand text-decoration-none fs-3" href="dashboard.php">ApartmentHub</a>
      <div class="d-flex align-items-center gap-2">
        <a href="dashboard.php" class="btn btn-outline-secondary d-none d-md-inline"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="../logout.php" class="btn btn-dark"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
      </div>
    </div>
  </div>
</header>

<div class="container mt-4">
  <h3 class="text-primary mb-4">My Notifications</h3>

  <?php if ($notifications): ?>
    <?php foreach ($notifications as $n): ?>
      <div class="card mb-3 p-3 <?= $n['status'] === 'Unread' ? 'border-primary' : '' ?>">
        <p class="mb-1"><?= htmlspecialchars($n['message']) ?></p>
        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-muted">No notifications yet.</p>
  <?php endif; ?>
</div>
<script src="../assets/js/theme.js"></script>
</body>
</html>
