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
$apartments = $db->getAvailableApartments($tenant_id);
$leases = $db->getTenantLeases($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tenant Dashboard | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
}
h2 {
    font-weight: bold;
    color: #0d6efd;
}
.card {
    border-radius: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.card-img-top {
    height: 200px;
    object-fit: cover;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}
.carousel-item img {
    border-radius: 15px;
}
.btn-back {
    background: none;
    border: none;
    color: #000;
    font-weight: 500;
    text-decoration: none;
}
.btn-back:hover {
    color: #0d6efd;
}
</style>
</head>
<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">Available Apartments</h2>
        <button class="btn btn-back" onclick="history.back()">&larr; Back</button>
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
                            <p><?= htmlspecialchars($a['Location']) ?></p>
                            <p><strong>₱<?= number_format($a['MonthlyRate'], 2) ?>/month</strong></p>

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
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                $('#message-area').html('<div class="alert alert-info">'+response+'</div>');
                btn.prop('disabled', true).text('Applied');
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
