<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// 🧭 Fetch tenant profile
$tenant = $db->getTenantProfile($tenant_id);
$message = "";

// Define variables for the form
$firstname = $tenant['firstname'];
$lastname  = $tenant['lastname'];
$username  = $tenant['username'];
$email     = $tenant['email'];
$phone     = $tenant['phone'];

// 📝 Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname  = $_POST['lastname'] ?? '';
    $username  = $tenant['username']; // keep read-only
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $password  = $_POST['password'] ?? '';

    $result = $db->updateTenantProfile($tenant_id, $firstname, $lastname, $username, $email, $phone, $password);

    if ($result === true) {
        $message = "✅ Profile updated successfully!";
        $tenant = $db->getTenantProfile($tenant_id); // refresh data

        // Update form variables after refresh
        $firstname = $tenant['firstname'];
        $lastname  = $tenant['lastname'];
        $username  = $tenant['username'];
        $email     = $tenant['email'];
        $phone     = $tenant['phone'];
    } else {
        $message = "⚠️ " . $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Profile | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      z-index: 0;
      pointer-events: none;
    }

    .navbar {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
      border-bottom: 3px solid var(--accent-gold);
      padding: 1rem 0;
      position: relative;
      z-index: 1000;
      margin-bottom: 0 !important;
    }

    .navbar::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }

    .navbar-brand {
      font-size: 1.8rem;
      font-weight: 700;
      color: white !important;
      letter-spacing: 1px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .navbar .btn-outline-secondary {
      border: 2px solid rgba(255,255,255,0.3);
      color: white;
      font-weight: 600;
      border-radius: 20px;
      padding: 8px 20px;
      transition: all 0.3s ease;
    }

    .navbar .btn-outline-secondary:hover {
      background: rgba(255,255,255,0.2);
      border-color: rgba(255,255,255,0.5);
      transform: translateY(-2px);
    }

    .navbar .btn-outline-danger {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      border: 2px solid rgba(255,255,255,0.2);
      color: white;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .navbar .btn-outline-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5);
    }

    .container {
      position: relative;
      z-index: 1;
      margin-top: 50px;
    }

    .card {
      border: none;
      border-radius: 30px;
      box-shadow: 
        0 30px 80px rgba(0,0,0,0.2),
        inset 0 1px 0 rgba(255,255,255,0.6);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.3);
      position: relative;
      animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      border-radius: 30px 30px 0 0;
    }

    .card h3 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2rem;
      margin-bottom: 1rem;
    }

    .card h3 i {
      color: var(--accent-gold);
      filter: drop-shadow(0 2px 4px rgba(212, 175, 55, 0.3));
    }

    .text-primary {
      color: var(--primary-dark) !important;
    }

    .text-muted {
      color: var(--earth-brown) !important;
      font-weight: 500;
      margin-bottom: 2rem !important;
    }

    .form-label {
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .form-control {
      border: 2px solid rgba(212, 175, 55, 0.3);
      border-radius: 15px;
      padding: 12px 20px;
      transition: all 0.3s ease;
      background: white;
      color: var(--earth-brown);
      font-weight: 500;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
      border-color: var(--accent-gold);
      background: white;
    }

    .form-control:read-only {
      background: linear-gradient(135deg, #f8f5f0 0%, #f0ebe0 100%);
      color: var(--soft-gray);
      cursor: not-allowed;
    }

    .btn {
      border-radius: 20px;
      padding: 12px 35px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.4s ease;
      border: none;
    }

    .btn-outline-secondary {
      border: 2px solid var(--soft-gray);
      color: var(--primary-dark);
      background: transparent;
    }

    .btn-outline-secondary:hover {
      background: var(--soft-gray);
      color: white;
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(149, 165, 166, 0.3);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      color: var(--deep-navy);
      box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
    }

    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
      background: linear-gradient(135deg, var(--luxury-gold) 0%, var(--accent-gold) 100%);
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }

    .deco-1 {
      top: 20%;
      left: 5%;
      width: 120px;
      height: 120px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 25%;
      right: 8%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
    }

    @media (max-width: 768px) {
      .card {
        padding: 2rem !important;
      }

      .card h3 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Tenant</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="col-md-8 mx-auto">
    <div class="card p-4">
      <h3 class="text-primary mb-3"><i class="bi bi-person-circle me-2"></i>Update Profile</h3>

      <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="update_profile.php">
        <div class="mb-3">
          <label class="form-label">First Name</label>
          <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($firstname) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Last Name</label>
          <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($lastname) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">New Password (optional)</label>
          <input type="password" name="password" class="form-control" placeholder="Enter a new password if you want to change it">
        </div>

        <div class="d-flex justify-content-between">
          <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>