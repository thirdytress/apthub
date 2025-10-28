<?php

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - ApartmentHub</title>
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
            <h4 class="mb-0">Forgot Password</h4>
          </div>
          <div class="card-body p-4">
            <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            <form id="forgotPasswordForm">
              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
              <div class="text-center mt-3">
                <a href="index.php#loginModal" class="text-decoration-none">Back to Login</a>
              </div>
            </form>
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
  $('#forgotPasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    // Show loading
    Swal.fire({
      title: 'Sending...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    $.ajax({
      url: 'actions/forgot_password.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      timeout: 10000, // 10 second timeout
      success: function(data) {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Sent!',
            text: 'Password reset link has been sent to your email.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3498db'
          }).then(() => {
            window.location.href = 'index.php';
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
        console.log('XHR:', xhr);
        console.log('Status:', status);
        console.log('Error:', error);
        console.log('Response:', xhr.responseText);
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to send reset link: ' + error + '. Check console for details.',
          confirmButtonColor: '#3498db'
        });
      }
    });
  });
</script>
</body>
</html>