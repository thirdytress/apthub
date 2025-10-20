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
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
    }

    $db->addApartment($name, $type, $location, $description, $monthly_rate, $image);
    echo "<script>alert('Apartment added successfully!'); window.location.href='dashboard.php';</script>";
}
?>




<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Apartment | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow col-md-8 mx-auto">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Add New Apartment</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Type</label>
                    <input type="text" name="type" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Monthly Rate</label>
                    <input type="number" step="0.01" name="monthly_rate" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <button type="submit" class="btn btn-success w-100">Add Apartment</button>
                <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
