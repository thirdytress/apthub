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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* your existing CSS unchanged */
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
    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    .navbar {
      background: linear-gradient(135deg, var(--deep-navy), var(--primary-dark));
      border-bottom: 3px solid var(--accent-gold);
    }
    .navbar .navbar-toggler { border-color: rgba(255,255,255,0.35); }
    .navbar-brand { color: white !important; font-weight: 700; }
    .nav-link { color: rgba(255,255,255,0.8) !important; transition: .3s; }
    .nav-link:hover { color: var(--accent-gold) !important; }
    .hero { text-align: center; padding: 100px 20px 60px; }
    .hero h1 { font-weight: 800; font-size: clamp(2rem, 3.5vw, 3rem); color: var(--primary-dark); }
    .hero p { font-size: clamp(1rem, 1.8vw, 1.3rem); color: var(--earth-brown); margin: 20px auto 24px; max-width: 900px; }
    .hero .btn {
      background: linear-gradient(135deg, var(--accent-gold), var(--luxury-gold));
      border: none; color: var(--deep-navy); font-weight: 700;
      padding: 12px 40px; border-radius: 25px;
    }
    .apartment-card {
      border-radius: 20px; overflow: hidden; transition: transform 0.2s ease-in-out;
      background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); height: 100%;
      cursor: pointer;
    }
    .apartment-card:hover { transform: scale(1.03); }
    .apartment-card img { height: 200px; width: 100%; object-fit: cover; }
    .card-body { padding: 20px; display: flex; flex-direction: column; }
    .card-title { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
    .card-text { font-size: 14px; margin-bottom: 8px; flex-grow: 1; }
    .btn-success { font-size: 14px; padding: 8px 15px; border-radius: 10px; margin-top: auto; }
    h2.text-primary { font-size: 28px; text-align: center; font-weight: 700; margin-bottom: 40px; }
    .apartment-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px; margin-bottom: 50px;
    }
    footer {
      background: linear-gradient(135deg, var(--deep-navy), var(--primary-dark));
      color: white; text-align: center; padding: 30px; margin-top: 80px;
      border-top: 3px solid var(--accent-gold);
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item mx-2"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="about.php">About</a></li>
  <li class="nav-item mx-2"><a class="nav-link nav-login-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <h1>Welcome to ApartmentHub</h1>
    <p>Find your perfect apartment with ease. Connecting tenants and property managers in one smart platform.</p>
    <a href="#" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
  </div>
</section>

<!-- AVAILABLE APARTMENTS SECTION -->
<section class="container mt-5">
  <h2 class="mb-4 text-primary fw-bold">All Apartments</h2>
  <div id="message-area"></div>
  <div class="apartment-grid">

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
        <div class="apartment-card" data-bs-toggle="modal" data-bs-target="#apartmentModal<?= $apt['ApartmentID'] ?>">
          <img src="<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($apt['Name']) ?>">
          <div class="card-body">
            <h5 class="card-title d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($apt['Name']) ?>
              <?php if ($isOccupied): ?>
                <span class="badge bg-danger">Occupied</span>
              <?php endif; ?>
            </h5>
            <p class="card-text"><?= htmlspecialchars($apt['Description']) ?></p>
            <p class="card-text"><strong>Monthly Rate:</strong> ₱<?= number_format($apt['MonthlyRate'], 2) ?></p>

            <?php if ($isOccupied): ?>
              <button class="btn btn-secondary btn-sm" disabled>Currently Occupied</button>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant'): ?>
              <a href="tenant/view_apartments.php" class="btn btn-success btn-sm" onclick="event.stopPropagation();">Apply Now</a>
            <?php else: ?>
              <button class="btn btn-success btn-sm require-login-apply" data-next="tenant/view_apartments.php" data-bs-toggle="modal" data-bs-target="#loginModal" onclick="event.stopPropagation();">Apply Now</button>
            <?php endif; ?>
          </div>
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
                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                  <i class="bi bi-eye" id="confirmEyeIcon"></i>
                </button>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 mt-3">Register</button>
        </form>

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

<footer>
  <p class="mb-0">&copy; 2025 ApartmentHub. All rights reserved.</p>
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
      if (response.status === 'success') {
        $('#otpOverlay').fadeIn();
      } else {
        Swal.fire('Error', response.message || 'Registration failed.', 'error');
      }
    },
    error: function(xhr) {
      Swal.close();
      Swal.fire('Error', 'Request failed: ' + xhr.responseText, 'error');
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