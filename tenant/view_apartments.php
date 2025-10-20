<?php
session_start();
require_once "../classes/database.php";

// Server-side restriction: Only tenant can access
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
/* (Insert your existing CSS here) */
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
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($a['Image'])): ?>
                            <img src="../<?= htmlspecialchars($a['Image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                        <?php endif; ?>
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
    $('.apply-btn').click(function(){
        var btn = $(this);
        var apartmentID = btn.data('apartment');

        $.ajax({
            url: 'apply_ajax.php',
            method: 'POST',
            data: { apartment_id: apartmentID },
            success: function(response){
                $('#message-area').html('<div class="alert alert-info">'+response+'</div>');
                // Optionally, disable button after apply
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
