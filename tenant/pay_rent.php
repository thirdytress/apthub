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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="../assets/css/air.css" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
    }

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    /* Floating decorations */
    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
      border-radius: 50%;
    }

    .deco-1 {
      top: 10%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 15%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-25px); }
    }

    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2rem;
      position: relative;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
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
      overflow: hidden;
      position: relative;
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

    .table thead {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      color: white;
    }

    .table tbody tr:hover {
      background: rgba(212, 175, 55, 0.08);
      transition: background 0.3s ease;
    }

    .badge {
      font-size: 0.9rem;
      padding: 0.5em 0.9em;
      border-radius: 12px;
    }

    .badge.bg-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    }

    .badge.bg-warning {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      color: white !important;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
      border: none;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
    }

    /* ✅ Aesthetic Back Button */
    .btn-back {
      position: fixed;
      top: 25px;
      right: 30px;
      z-index: 1000;
      background: white;
      border: 2px solid var(--accent-gold);
      color: var(--accent-gold);
      border-radius: 30px;
      font-weight: 600;
      padding: 10px 18px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      background: var(--accent-gold);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(212,175,55,0.4);
    }
  </style>
</head>
<body>

  <!-- ✅ Floating Aesthetic Back Button -->
  <a href="dashboard.php" class="btn btn-back">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>

  <div class="floating-decoration deco-1"></div>
  <div class="floating-decoration deco-2"></div>

  <div class="container mt-5">
    <h2 class="mb-4">My Rent Payments</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success shadow-sm border-0">✅ Payment submitted! Awaiting admin confirmation.</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-danger shadow-sm border-0">❌ Payment failed. Please try again.</div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body p-0">
        <table class="table table-bordered align-middle mb-0 text-center">
          <thead>
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
                    <?php elseif ($pay['status'] === 'Pending'): ?>
                      <span class="badge bg-warning text-light">Pending</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Unpaid</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($pay['payment_method'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($pay['reference_number'] ?? '-') ?></td>
                  <td>
                    <?php if ($pay['status'] === 'Unpaid'): ?>
                      <button class="btn btn-primary btn-sm pay-btn"
                              data-id="<?= $pay['payment_id'] ?>"
                              data-amount="<?= $pay['amount'] ?>"
                              data-bs-toggle="modal"
                              data-bs-target="#paymentModal">
                        Pay
                      </button>
                    <?php elseif ($pay['status'] === 'Pending'): ?>
                      <button class="btn btn-outline-secondary btn-sm" disabled>Pending Review</button>
                    <?php else: ?>
                      <button class="btn btn-success btn-sm" disabled>Paid</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No payment records found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Receipt Preview Modal (shown after successful payment) -->
  <?php $last = $_SESSION['last_payment'] ?? null; if ($last): ?>
  <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title">Payment Receipt</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <ul class="list-unstyled mb-3">
            <li><strong>Payment ID:</strong> <?= htmlspecialchars($last['payment_id']) ?></li>
            <li><strong>Amount:</strong> ₱<?= htmlspecialchars($last['amount']) ?></li>
            <li><strong>Method:</strong> <?= htmlspecialchars($last['method']) ?></li>
            <li><strong>Reference #:</strong> <?= htmlspecialchars($last['reference'] ?: '-') ?></li>
            <li><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($last['time'])) ?></li>
          </ul>
          <?php if (!empty($last['receipt'])): ?>
            <div class="text-center">
              <img src="../<?= htmlspecialchars($last['receipt']) ?>" alt="Proof of Payment" class="img-fluid rounded" style="max-height:360px;object-fit:contain;">
              <div class="mt-2"><a class="btn btn-outline-secondary btn-sm" href="../<?= htmlspecialchars($last['receipt']) ?>" target="_blank">Open Original</a></div>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No proof of payment was uploaded.</p>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
        </div>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['last_payment']); endif; ?>

  <!-- Payment Modal -->
  <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <form action="../actions/process_payment.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-gradient text-white" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));">
            <h5 class="modal-title fw-semibold" id="paymentModalLabel">Pay Rent</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="rent_id" id="rent_id">

            <div class="mb-3">
              <label class="form-label fw-semibold">Amount to Pay</label>
              <input type="text" class="form-control" id="amount" name="amount" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Payment Method</label>
              <select class="form-select" name="payment_method" id="payment_method" required>
                <option value="">Select method</option>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>

            <div id="gcashFields" style="display:none;">
              <div class="mb-3">
                <label class="form-label fw-semibold">GCash Reference Number</label>
                <input type="text" class="form-control" name="reference_number" placeholder="Enter GCash Ref No.">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Upload GCash Receipt (optional)</label>
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
  <script src="../assets/js/theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const payButtons = document.querySelectorAll('.pay-btn');
      const rentIdInput = document.getElementById('rent_id');
      const amountInput = document.getElementById('amount');
      const paymentMethod = document.getElementById('payment_method');
      const gcashFields = document.getElementById('gcashFields');

      payButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          rentIdInput.value = btn.dataset.id;
          amountInput.value = btn.dataset.amount;
          paymentMethod.value = "";
          gcashFields.style.display = "none";
        });
      });

      paymentMethod.addEventListener('change', function() {
        gcashFields.style.display = this.value === 'GCash' ? 'block' : 'none';
      });

      // If a payment just occurred and session data exists, show receipt modal
      <?php if (isset($_GET['success']) && $last): ?>
      const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
      receiptModal.show();
      <?php endif; ?>
    });
  </script>
</body>
</html>

