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
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary">My Maintenance Requests</h2>
    <a href="maintenance_request.php" class="btn btn-outline-primary">+ New Request</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
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
