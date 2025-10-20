<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $monthly_rate = trim($_POST['monthly_rate']);

    // Insert apartment first
    $apartment_id = $db->addApartment($name, $type, $location, $description, $monthly_rate);

    // Handle multiple image uploads
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['name'] as $key => $filename) {
            if ($_FILES['images']['error'][$key] == 0) {
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '.' . $ext;
                $imagePath = 'uploads/' . $uniqueName;
                move_uploaded_file($_FILES['images']['tmp_name'][$key], '../' . $imagePath);

                // Save each image to apartment_images table
                $db->addApartmentImage($apartment_id, $imagePath);
            }
        }
    }

    echo "<script>alert('Apartment and images added successfully!'); window.location.href='dashboard.php';</script>";
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Apartment | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
      --luxury-gold: #c9a961;
      --deep-navy: #1a252f;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: repeating-linear-gradient(90deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px),
                        repeating-linear-gradient(0deg, rgba(212,175,55,0.03) 0px, transparent 1px, transparent 40px, rgba(212,175,55,0.03) 41px);
      z-index: 0;
      pointer-events: none;
    }

    .container {
      position: relative;
      z-index: 2;
    }

    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      background: white;
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
      color: white;
      border: none;
      text-align: center;
      padding: 1.5rem;
    }

    .card-header h4 {
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .form-label {
      font-weight: 600;
      color: var(--primary-dark);
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,0.1);
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: var(--accent-gold);
      box-shadow: 0 0 0 0.2rem rgba(212,175,55,0.2);
    }

    .btn-primary, .btn-success {
      background: linear-gradient(135deg, var(--accent-gold), #b6932c);
      border: none;
      border-radius: 30px;
      padding: 10px 20px;
      font-weight: 600;
      color: #fff;
      transition: all 0.3s ease;
    }

    .btn-primary:hover, .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(212,175,55,0.4);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #95a5a6, #7f8c8d);
      border: none;
      border-radius: 30px;
      padding: 10px 20px;
      font-weight: 600;
      color: #fff;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(127,140,141,0.4);
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
      background: radial-gradient(circle, rgba(212,175,55,0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 20%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52,152,219,0.1), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
    }

  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container py-5">
  <div class="card col-lg-8 mx-auto">
    <div class="card-header">
      <h4><i class="bi bi-building-add me-2"></i>Add New Apartment</h4>
    </div>

    <div class="card-body p-4">
      <form method="POST" action="" enctype="multipart/form-data">

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Type</label>
          <input type="text" name="type" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Monthly Rate</label>
          <input type="number" step="0.01" name="monthly_rate" class="form-control" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Upload Apartment Images</label>
          <input type="file" name="images[]" class="form-control" multiple required>
          <small class="text-muted">You can select multiple images</small>
        </div>

        <button type="submit" class="btn btn-success w-100 mb-2">
          <i class="bi bi-check-circle me-2"></i>Add Apartment
        </button>

        <a href="dashboard.php" class="btn btn-secondary w-100">
          <i class="bi bi-arrow-left-circle me-2"></i>Cancel
        </a>

      </form>
    </div>
  </div>
</div>

</body>
</html>

