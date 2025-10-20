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
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
<div class="container mt-5">
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
</body>
</html>
