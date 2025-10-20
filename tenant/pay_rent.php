<?php
session_start();
require_once "../classes/database.php";

// Restrict to tenant only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();
$tenant_id = $_SESSION['user_id'];

// Get payment data for tenant
$stmt = $conn->prepare("
    SELECT 
        p.payment_id, 
        a.Name AS apartment_name, 
        p.due_date, 
        p.amount, 
        p.status, 
        p.payment_method, 
        p.reference_number
    FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    JOIN apartments a ON l.apartment_id = a.ApartmentID
    WHERE p.tenant_id = :tenant_id
    ORDER BY p.due_date DESC
");


$stmt->bindParam(':tenant_id', $tenant_id);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pay Rent | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2 class="mb-4 text-primary fw-bold">My Rent Payments</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">✅ Payment successful!</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-danger">❌ Payment failed. Please try again.</div>
    <?php endif; ?>

    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Apartment</th>
          <th>Due Date</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Payment Method</th>
          <th>Reference #</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($payments): ?>
          <?php foreach ($payments as $pay): ?>
            <tr>
              <td><?= htmlspecialchars($pay['apartment_name']) ?></td>
              <td><?= htmlspecialchars($pay['due_date']) ?></td>
              <td>₱<?= number_format($pay['amount'], 2) ?></td>
              <td>
                <?php if ($pay['status'] === 'Paid'): ?>
                  <span class="badge bg-success">Paid</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($pay['payment_method'] ?? '-') ?></td>
              <td><?= htmlspecialchars($pay['reference_number'] ?? '-') ?></td>
              <td>
                <?php if ($pay['status'] !== 'Paid'): ?>
                  <button class="btn btn-primary btn-sm pay-btn"
                          data-id="<?= $pay['payment_id'] ?>"
                          data-amount="<?= $pay['amount'] ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#paymentModal">
                    Pay
                  </button>
                <?php else: ?>
                  <button class="btn btn-success btn-sm" disabled>Paid</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted">No payment records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Payment Modal -->
  <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="../actions/process_payment.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="paymentModalLabel">Pay Rent</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="rent_id" id="rent_id">

            <div class="mb-3">
              <label class="form-label">Amount to Pay</label>
              <input type="text" class="form-control" id="amount" name="amount" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <select class="form-select" name="payment_method" id="payment_method" required>
                <option value="">Select method</option>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>

            <div id="gcashFields" style="display:none;">
              <div class="mb-3">
                <label class="form-label">GCash Reference Number</label>
                <input type="text" class="form-control" name="reference_number" placeholder="Enter GCash Ref No.">
              </div>

              <div class="mb-3">
                <label class="form-label">Upload GCash Receipt (optional)</label>
                <input type="file" class="form-control" name="gcash_receipt" accept="image/*">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Confirm Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const payButtons = document.querySelectorAll('.pay-btn');
      const rentIdInput = document.getElementById('rent_id');
      const amountInput = document.getElementById('amount');
      const paymentMethod = document.getElementById('payment_method');
      const gcashFields = document.getElementById('gcashFields');

      // Fill modal data when Pay is clicked
      payButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          rentIdInput.value = btn.dataset.id;
          amountInput.value = btn.dataset.amount;
          paymentMethod.value = "";
          gcashFields.style.display = "none";
        });
      });

      // Show/hide GCash fields dynamically
      paymentMethod.addEventListener('change', function() {
        gcashFields.style.display = this.value === 'GCash' ? 'block' : 'none';
      });
    });
  </script>
</body>
</html>
