<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $admin = $db->getAdminById($admin_id);
    if (!$admin || !password_verify($current_password, $admin['password'])) {
        echo "<script>alert('Current password is incorrect.'); window.history.back();</script>"; exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match.'); window.history.back();</script>"; exit();
    }

    $db->changeAdminPassword($admin_id, password_hash($new_password, PASSWORD_DEFAULT));
    echo "<script>alert('Password changed successfully!'); window.location.href='dashboard.php';</script>";
}
?>
<!-- HTML form remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password | ApartmentHub Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --luxury-gold: #c9a961;
      --deep-navy: #1a252f;
      --warm-beige: #f5f1e8;
    }

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
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
      background: radial-gradient(circle, rgba(212,175,55,0.15), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }
    .deco-2 {
      bottom: 15%;
      right: 10%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52,152,219,0.15), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    .card {
      position: relative;
      z-index: 1;
      border: none;
      border-radius: 25px;
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      border: 2px solid rgba(212,175,55,0.2);
      transition: all 0.4s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 70px rgba(0,0,0,0.2);
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
      color: white;
      border-radius: 25px 25px 0 0;
      border-bottom: 3px solid var(--accent-gold);
      text-align: center;
      padding: 1rem 0;
    }

    .card-header h4 {
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    label {
      font-weight: 500;
      color: var(--deep-navy);
    }

    .form-control {
      border-radius: 15px;
      padding: 10px 15px;
      border: 1px solid rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: var(--accent-gold);
      box-shadow: 0 0 10px rgba(212,175,55,0.3);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border: none;
      color: var(--deep-navy);
      border-radius: 20px;
      font-weight: 700;
      box-shadow: 0 4px 20px rgba(212,175,55,0.4);
      transition: all 0.4s ease;
    }
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(212,175,55,0.6);
    }

    .btn-secondary {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
      border: none;
      border-radius: 20px;
      font-weight: 600;
      color: white;
      transition: all 0.3s ease;
    }
    .btn-secondary:hover {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
      transform: translateY(-2px);
    }

  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container">
  <div class="card col-md-6 mx-auto">
    <div class="card-header">
      <h4><i class="bi bi-shield-lock me-2"></i>Change Password</h4>
    </div>
    <div class="card-body p-4">
      <form method="POST" action="">
        <div class="mb-3">
          <label>Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>New Password</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">
          <i class="bi bi-arrow-repeat me-2"></i>Update Password
        </button>
        <a href="dashboard.php" class="btn btn-secondary w-100 mt-3">
          <i class="bi bi-arrow-left me-2"></i>Cancel
        </a>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

