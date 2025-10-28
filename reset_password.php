<?php

session_start();
require_once "classes/database.php";

// Set timezone
date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';
$error = '';

if (empty($token)) {
  header('Location: index.php');
  exit;
}

// Verify token exists and is not expired
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM tenants WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
  // Manual expiry check
  $expiryTime = strtotime($user['reset_token_expiry']);
  $currentTime = time();
  $isValid = $currentTime < $expiryTime;
  
  if (!$isValid) {
    $error = 'Invalid or expired reset token.';
  }
} else {
  $error = 'Invalid or expired reset token.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/air.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card">
          <div class="card-header bg-dark text-white text-center py-3">
            <h4 class="mb-0">Reset Password</h4>
          </div>
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
              <a href="index.php" class="btn btn-primary w-100">Back to Home</a>
            <?php else: ?>
              <p class="text-muted mb-4">Enter your new password below.</p>
              <form id="resetPasswordForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" class="form-control" name="password" required minlength="6">
                  <small class="text-muted">Minimum 6 characters</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-success w-100">Reset Password</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/theme.js"></script>
  <script>
    $('#resetPasswordForm').on('submit', function(e) {
      e.preventDefault();
      const password = $('input[name="password"]').val();
      const confirmPassword = $('input[name="confirm_password"]').val();
      
      if (password !== confirmPassword) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Passwords do not match!',
          confirmButtonColor: '#3498db'
        });
        return;
      }

      // Show loading
      Swal.fire({
        title: 'Resetting Password...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: 'actions/reset_password.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(data) {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: 'Your password has been successfully reset.',
              confirmButtonText: 'Login Now',
              confirmButtonColor: '#28a745'
            }).then(() => {
              window.location.href = 'index.php#loginModal';
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,
              confirmButtonColor: '#3498db'
            });
          }
        },
        error: function(xhr, status, error) {
          console.log('Response:', xhr.responseText);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to reset password. Please try again.',
            confirmButtonColor: '#3498db'
          });
        }
      });
    });
  </script>
</body>
</html>