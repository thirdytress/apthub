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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="../assets/css/air.css" rel="stylesheet">
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
      overflow-x: hidden;
      position: relative;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-image:
        repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      pointer-events: none;
      z-index: 0;
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }

    .deco-1 {
      top: 15%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 20%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
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
      text-align: center;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    .card {
      border: none;
      border-radius: 25px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      padding: 2rem;
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark), var(--primary-blue), var(--accent-gold));
      transform: scaleX(0);
      transition: transform 0.5s ease;
    }

    .card:hover::before {
      transform: scaleX(1);
    }

    .form-label {
      font-weight: 600;
      color: var(--primary-dark);
    }

    .form-control {
      border-radius: 15px;
      border: 1px solid rgba(212, 175, 55, 0.4);
      padding: 0.75rem 1rem;
      transition: box-shadow 0.3s ease, transform 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--accent-gold);
      box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
      transform: scale(1.01);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.4s ease;
      box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(52, 152, 219, 0.6);
    }

    .btn-back {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.4s ease;
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
      text-decoration: none;
      display: inline-block;
    }

    .btn-back:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--soft-gray) 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149, 165, 166, 0.5);
      color: white;
    }

    .alert {
      border: none;
      border-radius: 20px;
      font-weight: 500;
      padding: 1.2rem 1.5rem;
      animation: fadeIn 0.5s ease;
    }

    .alert-success {
      background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(39, 174, 96, 0.15));
      border: 2px solid rgba(46, 204, 113, 0.3);
      color: #27ae60;
    }

    .alert-danger {
      background: linear-gradient(135deg, rgba(231, 76, 60, 0.15), rgba(192, 57, 43, 0.15));
      border: 2px solid rgba(231, 76, 60, 0.3);
      color: #e74c3c;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<!-- Unified header -->
<header class="header">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between">
      <a class="brand text-decoration-none fs-3" href="dashboard.php">ApartmentHub</a>
      <div class="d-flex align-items-center gap-2">
        <a href="view_requests.php" class="btn btn-outline-secondary d-none d-md-inline"><i class="bi bi-list-check"></i> My Requests</a>
        <a href="../logout.php" class="btn btn-dark"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
      </div>
    </div>
  </div>
</header>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container">
  <h2><i class="bi bi-tools me-2"></i>Submit Maintenance Request</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['msg']); ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card mx-auto" style="max-width: 600px;">
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

    <div class="text-center mt-4">
      <a href="dashboard.php" class="btn-back"><i class="bi bi-arrow-left me-2"></i>Back to Dashboard</a>
    </div>
  </div>
</div>
<script src="../assets/js/theme.js"></script>
</body>
</html>

