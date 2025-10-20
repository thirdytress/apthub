<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../actions/login.php");
    exit();
}

$db = new Database();
$tenant = $db->getTenantProfile($_SESSION['user_id']);
$fullname = $tenant['firstname'] . ' ' . $tenant['lastname'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tenant Dashboard | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-dark: #2c3e50;
        --primary-blue: #3498db;
        --accent-gold: #d4af37;
        --luxury-gold: #c9a961;
        --earth-brown: #8b7355;
        --soft-gray: #95a5a6;
        --deep-navy: #1a252f;
    }
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
        min-height: 100vh;
        overflow-x: hidden;
    }
    .navbar {
        background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%) !important;
        box-shadow: 0 4px 30px rgba(0,0,0,0.3);
        border-bottom: 3px solid var(--accent-gold);
        padding: 1rem 0;
    }
    .navbar-brand {
        font-size: 1.8rem;
        font-weight: 700;
        color: white !important;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .navbar-brand i { color: var(--accent-gold); }
    .btn-logout {
        background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
        border-radius: 30px;
        font-weight: 700;
        color: var(--deep-navy);
        padding: 10px 30px;
        box-shadow: 0 4px 20px rgba(212,175,55,0.4);
        transition: all 0.4s ease;
    }
    .btn-logout:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(212,175,55,0.6);
    }
    .welcome {
        margin-top: 60px;
        margin-bottom: 70px;
    }
    .welcome h2 { font-size:3rem; font-weight:800; color:var(--primary-dark); }
    .welcome p { font-size:1.2rem; color:var(--earth-brown); }
    .card {
        border:none;
        border-radius:20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
        transition: all 0.4s ease;
    }
    .card:hover { transform: translateY(-8px) scale(1.02); }
    .card-body { text-align:center; padding:2rem; }
    .icon-container { font-size:3rem; margin-bottom:1rem; }
    .card-title { font-size:1.4rem; font-weight:700; margin-bottom:1rem; color:var(--primary-dark); }
    .card-text { font-size:1rem; color:var(--earth-brown); margin-bottom:1rem; }
    .btn-card { border-radius:25px; padding:10px 20px; font-weight:700; text-transform:uppercase; }

    footer {
        background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 30px 20px;
        text-align: center;
        margin-top: 100px;
        border-top: 3px solid var(--accent-gold);
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#"><i class="bi bi-building-fill"></i> ApartmentHub</a>
    <a href="../logout.php" class="btn btn-logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="container text-center">
    <div class="welcome">
        <h2>Hello, <?= htmlspecialchars($fullname) ?> 👋</h2>
        <p>Your complete apartment management dashboard</p>
    </div>

        <div class="row justify-content-center g-4 mt-4">

        <!-- Available Apartments -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-primary"><i class="bi bi-buildings"></i></div>
                    <h5 class="card-title">Available Apartments</h5>
                    <p class="card-text">Discover your perfect living space</p>
                    <a href="view_apartments.php" class="btn btn-primary btn-card w-100">Browse Units</a>
                </div>
            </div>
        </div>

        <!-- My Applications -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-warning"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <h5 class="card-title">My Applications</h5>
                    <p class="card-text">Track your rental applications</p>
                    <a href="my_applications.php" class="btn btn-warning btn-card w-100">View Applications</a>
                </div>
            </div>
        </div>

        <!-- My Leases -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-success"><i class="bi bi-key-fill"></i></div>
                    <h5 class="card-title">My Leases</h5>
                    <p class="card-text">Access your current lease agreements</p>
                    <a href="my_leases.php" class="btn btn-success btn-card w-100">View Leases</a>
                </div>
            </div>
        </div>

        <!-- Submit Maintenance Request -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-info"><i class="bi bi-tools"></i></div>
                    <h5 class="card-title">Submit Maintenance Request</h5>
                    <p class="card-text">Report issues or request repairs</p>
                    <a href="maintenance_request.php" class="btn btn-info btn-card w-100">Submit Request</a>
                </div>
            </div>
        </div>

        <!-- View My Requests -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-danger"><i class="bi bi-wrench-adjustable-circle"></i></div>
                    <h5 class="card-title">View My Requests</h5>
                    <p class="card-text">See status of your maintenance requests</p>
                    <a href="view_requests.php" class="btn btn-danger btn-card w-100">View Requests</a>
                </div>
            </div>
        </div>

        <!-- View Utilities -->
<div class="col-md-6 col-lg-3">
  <div class="card h-100">
    <div class="card-body">
      <div class="icon-container text-info"><i class="bi bi-lightning-charge"></i></div>
      <h5 class="card-title">My Utilities</h5>
      <p class="card-text">View your water and electricity bills</p>
      <a href="utilities.php" class="btn btn-info btn-card w-100">View Bills</a>
    </div>
  </div>
</div>

<!-- Pay Rent -->
<div class="col-md-6 col-lg-3">
  <div class="card h-100">
    <div class="card-body">
      <div class="icon-container text-success"><i class="bi bi-cash-stack"></i></div>
      <h5 class="card-title">Pay Rent</h5>
      <p class="card-text">Submit your rent payment</p>
      <a href="pay_rent.php" class="btn btn-success w-100">Pay Now</a>
    </div>
  </div>
</div>

<!-- View Payment History -->
<div class="col-md-6 col-lg-3">
  <div class="card h-100">
    <div class="card-body">
      <div class="icon-container text-primary"><i class="bi bi-credit-card"></i></div>
      <h5 class="card-title">Payment History</h5>
      <p class="card-text">View your past payments</p>
      <a href="view_payments.php" class="btn btn-primary w-100">View</a>
    </div>
  </div>
</div>


        <!-- Update Profile -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="icon-container text-secondary"><i class="bi bi-person-fill-gear"></i></div>
                    <h5 class="card-title">Update Profile</h5>
                    <p class="card-text">Manage your personal information</p>
                    <a href="update_profile.php" class="btn btn-secondary btn-card w-100">Edit Profile</a>
                </div>
            </div>
        </div>

    </div> <!-- ✅ closes row properly -->
</div> <!-- ✅ closes container properly -->




<footer>
  <p class="mb-0">&copy; 2025 ApartmentHub. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
