<?php
session_start();
require_once "../classes/database.php";

// Only tenant can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
// Show available apartments the same way as index.php (all with Status = 'Available')
$conn = $db->connect();
$stmt = $conn->prepare("SELECT * FROM apartments WHERE Status = 'Available' ORDER BY DateAdded DESC");
$stmt->execute();
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$leases = $db->getTenantLeases($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tenant Dashboard | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

    .container {
      position: relative;
      z-index: 1;
      margin-top: 50px;
    }

    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2.2rem;
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 80px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    .text-primary {
      color: var(--primary-dark) !important;
    }

    .btn-back {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      transition: all 0.4s ease;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .btn-back:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--soft-gray) 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149, 165, 166, 0.5);
      color: white;
    }

    #message-area {
      margin-bottom: 1.5rem;
    }

    .alert {
      border: none;
      border-radius: 20px;
      font-weight: 500;
      padding: 1.2rem 1.5rem;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .alert-info {
      background: linear-gradient(135deg, rgba(52, 152, 219, 0.15) 0%, rgba(44, 62, 80, 0.15) 100%);
      border: 2px solid rgba(52, 152, 219, 0.3);
      color: var(--primary-blue);
    }

    .alert-danger {
      background: linear-gradient(135deg, rgba(231, 76, 60, 0.15) 0%, rgba(192, 57, 43, 0.15) 100%);
      border: 2px solid rgba(231, 76, 60, 0.3);
      color: #e74c3c;
    }

    .card {
      border: none;
      border-radius: 25px;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      overflow: hidden;
      position: relative;
      border: 2px solid rgba(212, 175, 55, 0.2);
      cursor: pointer;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      transform: scaleX(0);
      transition: transform 0.5s ease;
      z-index: 1;
    }

    .card:hover::before {
      transform: scaleX(1);
    }

    .card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 0 30px 80px rgba(0,0,0,0.25);
    }

    .card-img-top {
      height: 200px;
      object-fit: cover;
      border-top-left-radius: 25px;
      border-top-right-radius: 25px;
      transition: transform 0.5s ease;
    }

    .card:hover .card-img-top {
      transform: scale(1.1);
    }

    .card-body {
      padding: 2rem;
    }

    .card-body h5 {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--primary-dark);
      margin-bottom: 1rem;
    }

    .card-body p {
      color: var(--earth-brown);
      font-weight: 500;
      line-height: 1.6;
    }

    .card .btn-primary {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.4s ease;
      box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
    }

    .card .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(52, 152, 219, 0.6);
    }

    .card .btn-primary:disabled {
      background: linear-gradient(135deg, var(--soft-gray) 0%, #7f8c8d 100%);
      opacity: 0.6;
      cursor: not-allowed;
    }

    .modal-content {
      border-radius: 30px;
      border: 2px solid rgba(212, 175, 55, 0.3);
      box-shadow: 0 30px 80px rgba(0,0,0,0.3);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      overflow: hidden;
    }

    .modal-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
    }

    .modal-header {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
      color: white;
      border-bottom: 3px solid var(--accent-gold);
      padding: 1.5rem 2rem;
    }

    .modal-title {
      font-weight: 700;
      font-size: 1.5rem;
    }

    .modal-body {
      padding: 2rem;
      background: var(--warm-beige);
    }

    .modal-body p {
      color: var(--earth-brown);
      font-weight: 500;
      margin-bottom: 0.8rem;
    }

    .modal-body strong {
      color: var(--primary-dark);
    }

    .modal-footer {
      background: var(--warm-beige);
      padding: 1.5rem 2rem;
    }

    .modal-footer .btn-secondary {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      border: none;
      border-radius: 20px;
      padding: 10px 30px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .modal-footer .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149, 165, 166, 0.5);
    }

    .modal-footer .btn-primary {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border: none;
      color: var(--deep-navy);
      border-radius: 20px;
      padding: 10px 30px;
      font-weight: 700;
      box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
    }

    .modal-footer .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
    }

    .carousel-item img {
      border-radius: 20px;
      height: 400px;
      object-fit: cover;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      background-color: rgba(44, 62, 80, 0.8);
      border-radius: 50%;
      padding: 20px;
    }

    .table-responsive {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      margin-bottom: 3rem;
      background: white;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    }

    .table thead th {
      color: white;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 1.2rem 1rem;
      border: none;
      font-size: 0.9rem;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(212, 175, 55, 0.05), transparent);
      transform: translateX(5px);
    }

    .table tbody td {
      padding: 1.2rem 1rem;
      color: var(--earth-brown);
      font-weight: 500;
      vertical-align: middle;
    }

    .table-bordered {
      border: none;
    }

    .text-muted {
      color: var(--earth-brown) !important;
      font-size: 1.1rem;
      font-weight: 500;
      padding: 2rem 0;
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
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
    }

    @media (max-width: 768px) {
      h2 {
        font-size: 1.8rem;
      }

      .card-body {
        padding: 1.5rem;
      }

      .table thead th,
      .table tbody td {
        padding: 1rem 0.5rem;
        font-size: 0.85rem;
      }
    }
</style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">Available Apartments</h2>
        <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
    </div>

    <div id="message-area"></div>
    
    <div class="row mb-5">
        <?php if ($apartments): ?>
            <?php foreach ($apartments as $a): ?>
                <?php
                    // Fetch multiple images for each apartment
                    $images = $db->getApartmentImages($a['ApartmentID']);
                    $firstImage = $images[0]['image_path'] ?? 'images/default.jpg';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#apartmentModal<?= $a['ApartmentID'] ?>">
                        <img src="../<?= htmlspecialchars($firstImage) ?>" class="card-img-top" alt="<?= htmlspecialchars($a['Name']) ?>">
            <div class="card-body d-flex flex-column">
              <h5><?= htmlspecialchars($a['Name']) ?></h5>
              <p class="mb-1 text-muted"><?= htmlspecialchars($a['Location']) ?></p>
              <p class="flex-grow-1"><?= htmlspecialchars($a['Description']) ?></p>
              <p class="mt-2"><strong>₱<?= number_format($a['MonthlyRate'], 2) ?>/month</strong></p>

                            <?php if ($_SESSION['role'] === 'tenant'): ?>
                                <button class="btn btn-primary mt-auto w-100 apply-btn" data-apartment="<?= $a['ApartmentID'] ?>">Apply</button>
                            <?php else: ?>
                                <button class="btn btn-primary mt-auto w-100" disabled title="Only tenants can apply">Apply</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal for image carousel -->
                <div class="modal fade" id="apartmentModal<?= $a['ApartmentID'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content rounded-4 shadow-lg border-0">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold"><?= htmlspecialchars($a['Name']) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if ($images && count($images) > 0): ?>
                                    <div id="carousel<?= $a['ApartmentID'] ?>" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($images as $index => $img): ?>
                                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                    <img src="../<?= htmlspecialchars($img['image_path']) ?>" class="d-block w-100" style="height:400px;object-fit:cover;">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $a['ApartmentID'] ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $a['ApartmentID'] ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <img src="../images/default.jpg" class="w-100" style="height:400px;object-fit:cover;border-radius:10px;">
                                <?php endif; ?>

                                <hr>
                                <p><strong>Type:</strong> <?= htmlspecialchars($a['Type']) ?></p>
                                <p><strong>Location:</strong> <?= htmlspecialchars($a['Location']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($a['Description']) ?></p>
                                <p><strong>Monthly Rate:</strong> ₱<?= number_format($a['MonthlyRate']) ?></p>
                            </div>
                            <div class="modal-footer border-0">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-primary apply-btn" data-apartment="<?= $a['ApartmentID'] ?>">Apply Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No available apartments at the moment.</p>
        <?php endif; ?>
    </div>

    <h2 class="text-primary mb-4">My Current Leases</h2>
    <?php if ($leases): ?>
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Apartment</th>
                        <th>Location</th>
                        <th>Monthly Rate</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leases as $i => $l): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($l['apartment_name']) ?></td>
                            <td><?= htmlspecialchars($l['Location']) ?></td>
                            <td>₱<?= number_format($l['MonthlyRate'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($l['start_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($l['end_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You currently have no active leases.</p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// AJAX Apply
$(document).ready(function(){
    $('.apply-btn').click(function(e){
        e.stopPropagation(); // prevent modal trigger
        var btn = $(this);
        var apartmentID = btn.data('apartment');

    $.ajax({
      url: 'apply_ajax.php',
      method: 'POST',
      data: { apartment_id: apartmentID },
      success: function(response){
        // On success, go to My Applications page
        window.location.href = 'my_applications.php';
      },
      error: function(){
        $('#message-area').html('<div class="alert alert-danger">Something went wrong. Try again.</div>');
      }
    });
    });
});
</script>

</body>
</html>
