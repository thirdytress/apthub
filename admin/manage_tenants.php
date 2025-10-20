<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if (isset($_GET['delete_id'])) {
    $db->deleteTenant($_GET['delete_id']);
    echo "<script>alert('Tenant deleted successfully!'); window.location.href='manage_tenants.php';</script>";
}

$tenants = $db->getAllTenants();
?>
<!-- HTML table remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Tenants | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
.card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
</style>
</head>
<body>
<div class="container mt-4">
    <div class="card p-4">
        <h3 class="text-primary mb-3">Manage Tenants</h3>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tenants as $i => $t): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($t['firstname'] . ' ' . $t['lastname']) ?></td>
                        <td><?= htmlspecialchars($t['username']) ?></td>
                        <td><?= htmlspecialchars($t['email']) ?></td>
                        <td><?= htmlspecialchars($t['phone']) ?></td>
                        <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                        <td>
                            <a href="manage_tenants.php?delete_id=<?= $t['tenant_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this tenant?');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($tenants) === 0): ?>
                    <tr><td colspan="7" class="text-center">No tenants found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
