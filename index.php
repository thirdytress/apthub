<?php
session_start();
require_once "classes/database.php";
$db = new Database();
$conn = $db->connect();

// Fetch ALL apartments (Available and Occupied)
$stmt = $conn->prepare("SELECT * FROM apartments ORDER BY DateAdded DESC");
$stmt->execute();
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/air.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body>

<!-- HEADER (Airbnb-like) -->
<header class="header">
  <div class="container py-2">
    <div class="d-flex align-items-center justify-content-between">
      <!-- Brand -->
      <a class="brand text-decoration-none fs-4" href="index.php">ApartmentHub</a>

      

      <!-- Right controls -->
      <div class="d-flex align-items-center gap-2">
        <a href="about.php" class="btn btn-link text-decoration-none text-dark d-none d-md-inline">About</a>
        <button class="btn btn-outline-secondary nav-login-link d-none d-md-inline" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
        <button class="btn btn-dark d-none d-md-inline" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
        <button class="btn btn-outline-secondary d-md-none" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-person"></i></button>
      </div>
    </div>
  </div>
</header>

<!-- Top spacing for content -->
<div class="pt-3"></div>

<!-- AVAILABLE APARTMENTS SECTION -->
<section class="container stays">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h5 mb-0">Explore stays</h2>
    <div class="text-muted small">Showing <?= count($apartments) ?> places</div>
  </div>
  <div id="message-area"></div>
  <div class="row g-4">

    <?php if ($apartments): ?>
      <?php foreach ($apartments as $apt): ?>
        <?php
          // Fetch images for this apartment
          $imgStmt = $conn->prepare("SELECT image_path FROM apartment_images WHERE apartment_id = :id");
          $imgStmt->bindParam(':id', $apt['ApartmentID']);
          $imgStmt->execute();
          $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
          $firstImage = $images[0]['image_path'] ?? 'images/default.jpg';
          $isOccupied = ($apt['Status'] === 'Occupied');
        ?>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
          <a class="stay-card" href="#" data-bs-toggle="modal" data-bs-target="#apartmentModal<?= $apt['ApartmentID'] ?>">
            <div class="stay-img">
              <img src="<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($apt['Name']) ?>">
              <?php if ($isOccupied): ?><span class="status-badge">Occupied</span><?php endif; ?>
            </div>
            <div class="stay-info">
              <div class="title mb-1"><?= htmlspecialchars($apt['Name']) ?></div>
              <div class="muted text-truncate mb-1"><?= htmlspecialchars($apt['Description']) ?></div>
              <div class="d-flex align-items-center justify-content-between">
                <div class="price">₱<?= number_format($apt['MonthlyRate'], 0) ?><span class="fw-normal text-muted">/month</span></div>
                <?php if ($isOccupied): ?>
                  <span class="text-muted small">Unavailable</span>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant'): ?>
                  <a href="tenant/view_apartments.php" class="apply-link" onclick="event.stopPropagation();">Apply</a>
                <?php else: ?>
                  <a href="#" class="apply-link require-login-apply" data-next="tenant/view_apartments.php" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#loginModal">Apply</a>
                <?php endif; ?>
              </div>
            </div>
          </a>
        </div>

        <!-- Apartment Modal with Carousel -->
        <div class="modal fade" id="apartmentModal<?= $apt['ApartmentID'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 shadow-lg">
              <div class="modal-header border-0">
                <h5 class="modal-title"><?= htmlspecialchars($apt['Name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-0">
                <?php if ($images): ?>
                  <div id="carousel<?= $apt['ApartmentID'] ?>" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                      <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                          <img src="<?= htmlspecialchars($img['image_path']) ?>" class="d-block w-100" style="height:400px;object-fit:cover;">
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $apt['ApartmentID'] ?>" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $apt['ApartmentID'] ?>" data-bs-slide="next">
                      <span class="carousel-control-next-icon"></span>
                    </button>
                  </div>
                <?php else: ?>
                  <img src="images/default.jpg" class="img-fluid w-100" style="height:400px;object-fit:cover;">
                <?php endif; ?>
                <div class="p-4">
                  <p><?= htmlspecialchars($apt['Description']) ?></p>
                  <p><strong>Monthly Rate:</strong> ₱<?= number_format($apt['MonthlyRate'], 2) ?></p>
                  <?php if ($isOccupied): ?>
                    <button class="btn btn-secondary w-100" disabled>Currently Occupied</button>
                  <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant'): ?>
                    <a href="tenant/view_apartments.php" class="btn btn-success w-100" onclick="event.stopPropagation();">Apply Now</a>
                  <?php else: ?>
                    <button class="btn btn-success w-100 require-login-apply" data-next="tenant/view_apartments.php" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#loginModal">Apply Now</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No apartments available right now. Please check back later.</p>
    <?php endif; ?>
  </div>
</section>


<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Login</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm">
          <input type="hidden" name="next" id="loginNext" value="">
          <div class="mb-3">
            <label>Username or Email</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <div class="input-group">
              <input type="password" class="form-control" name="password" id="loginPassword" required>
              <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                <i class="bi bi-eye" id="loginEyeIcon"></i>
              </button>
            </div>
          </div>
          
          <!-- Forgot Password Link -->
          <div class="mb-3 text-end">
            <a href="forgot_password.php" style="font-size: 14px; text-decoration: none; color: #3498db;">Forgot Password?</a>
          </div>
          
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- REGISTER MODAL + OTP -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content position-relative">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Tenant Registration</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="registerForm">
          <input type="hidden" name="action" value="register">

          <div class="row g-2">
            <div class="col-md-6">
              <label>First Name</label>
              <input type="text" class="form-control" name="firstname" required>
            </div>
            <div class="col-md-6">
              <label>Last Name</label>
              <input type="text" class="form-control" name="lastname" required>
            </div>
          </div>

          <div class="mt-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" required>
          </div>

          <div class="mt-3">
            <label>Email Address</label>
            <input type="email" class="form-control" name="email" required>
          </div>

          <div class="mt-3">
            <label>Phone Number</label>
            <input type="text" class="form-control" name="phone" required>
          </div>

          <div class="row g-2 mt-3">
            <div class="col-md-6">
              <label>Password</label>
              <div class="input-group">
                <input type="password" class="form-control" name="password" id="registerPassword" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                  <i class="bi bi-eye" id="registerEyeIcon"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label>Confirm Password</label>
              <div class="input-group">
                <!-- FIXED name here -->
                <input type="password" class="form-control" name="confirm" id="confirmPassword" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                  <i class="bi bi-eye" id="confirmEyeIcon"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-dark px-4">Register</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


        <!-- OTP OVERLAY -->
        <div id="otpOverlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.6);">
          <div class="d-flex justify-content-center align-items-center h-100">
            <form id="otpForm" class="bg-white p-4 rounded shadow">
              <h5 class="mb-3 text-center">Enter OTP</h5>
              <input type="text" class="form-control mb-3" name="otp" placeholder="Enter OTP" required>
              <input type="hidden" name="action" value="verify_otp">
              <button type="submit" class="btn btn-success w-100">Verify OTP</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="footer py-4 mt-4">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="small">&copy; 2025 ApartmentHub</div>
    <div class="small">Built with ❤️ using Bootstrap</div>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ========================
   LOGIN FUNCTIONALITY
======================== */
if (window.location.hash === '#loginModal') $('#loginModal').modal('show');

$('#loginForm').on('submit', function(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Logging in...',
    text: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  $.ajax({
    url: 'actions/login.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Login Successful!',
          text: 'Welcome back, ' + response.name + '!',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          var next = $('#loginNext').val();
          window.location.href = next && next.length ? next : response.redirect;
        });
      } else {
        Swal.fire({ icon: 'error', title: 'Login Failed', text: response.message, confirmButtonColor: '#3498db' });
      }
    },
    error: function() {
      Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', confirmButtonColor: '#3498db' });
    }
  });
});

/* ========================
   REGISTER FUNCTIONALITY
======================== */
$('#registerForm').on('submit', function(e) {
  e.preventDefault();

  Swal.fire({
    title: 'Registering...',
    text: 'Please wait while we send your OTP.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  $.ajax({
    url: 'actions/register.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(response) {
      Swal.close();
      console.log('Registration response:', response);
      if (response.status === 'success') {
        $('#otpOverlay').fadeIn();
      } else {
        Swal.fire('Error', response.message || 'Registration failed.', 'error');
      }
    },
    error: function(xhr, status, error) {
      Swal.close();
      console.error('AJAX Error:', {
        status: xhr.status,
        statusText: xhr.statusText,
        responseText: xhr.responseText,
        error: error
      });
      Swal.fire({
        icon: 'error',
        title: 'Request Failed',
        html: '<strong>Status:</strong> ' + xhr.status + '<br>' +
              '<strong>Error:</strong> ' + error + '<br>' +
              '<strong>Response:</strong><br><pre style="text-align:left;max-height:200px;overflow:auto;">' + 
              (xhr.responseText || 'No response') + '</pre>',
        width: '600px'
      });
    }
  });
});

/* ========================
   OTP VERIFICATION
======================== */
$('#otpForm').on('submit', function(e) {
  e.preventDefault();

  Swal.fire({
    title: 'Verifying OTP...',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  $.ajax({
    url: 'actions/register.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(response) {
      Swal.close();
      if (response.status === 'success') {
        $('#otpOverlay').fadeOut();
        Swal.fire({
          icon: 'success',
          title: 'Registration Complete!',
          timer: 1500,
          showConfirmButton: false
        }).then(() => window.location.href = 'index.php');
      } else {
        Swal.fire('Invalid OTP', response.message || 'Please try again.', 'error');
      }
    },
    error: function(xhr) {
      Swal.close();
      Swal.fire('Error', 'Request failed: ' + xhr.responseText, 'error');
    }
  });
});

/* ========================
   TOGGLE PASSWORD VISIBILITY
======================== */
$('#toggleLoginPassword').on('click', function() {
  const field = $('#loginPassword');
  const icon = $('#loginEyeIcon');
  field.attr('type', field.attr('type') === 'password' ? 'text' : 'password');
  icon.toggleClass('bi-eye bi-eye-slash');
});

$('#toggleRegisterPassword').on('click', function() {
  const field = $('#registerPassword');
  const icon = $('#registerEyeIcon');
  field.attr('type', field.attr('type') === 'password' ? 'text' : 'password');
  icon.toggleClass('bi-eye bi-eye-slash');
});

$('#toggleConfirmPassword').on('click', function() {
  const field = $('#confirmPassword');
  const icon = $('#confirmEyeIcon');
  field.attr('type', field.attr('type') === 'password' ? 'text' : 'password');
  icon.toggleClass('bi-eye bi-eye-slash');
});

/* ========================
   APPLY & LOGIN REDIRECT
======================== */
$(document).on('click', '.require-login-apply', function(){
  var next = $(this).data('next') || 'tenant/view_apartments.php';
  $('#loginNext').val(next);
});

$(document).on('click', '.nav-login-link', function(){
  $('#loginNext').val('');
});
</script>
</body>
</html>