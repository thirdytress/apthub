<?php
session_start();
require_once "classes/database.php";
$db = new Database();
$conn = $db->connect();

// Fetch only available apartments
$stmt = $conn->prepare("SELECT * FROM apartments WHERE Status = 'Available' ORDER BY DateAdded DESC");
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

    .navbar-brand {
      color: white !important;
      font-weight: 700;
    }

    .nav-link {
      color: rgba(255,255,255,0.8) !important;
      transition: .3s;
    }

    .nav-link:hover {
      color: var(--accent-gold) !important;
    }

    .hero {
      text-align: center;
      padding: 120px 20px;
    }

    .hero h1 {
      font-weight: 800;
      font-size: 3rem;
      color: var(--primary-dark);
    }

    .hero p {
      font-size: 1.3rem;
      color: var(--earth-brown);
      margin: 20px 0 30px;
    }

    .hero .btn {
      background: linear-gradient(135deg, var(--accent-gold), var(--luxury-gold));
      border: none;
      color: var(--deep-navy);
      font-weight: 700;
      padding: 12px 40px;
      border-radius: 25px;
    }

    .apartment-card {
      border-radius: 20px;
      overflow: hidden;
      transition: transform 0.2s ease-in-out;
      max-width: 280px;
      margin: 0 auto;
    }

    .apartment-card:hover {
      transform: scale(1.03);
    }

    .apartment-card img {
      height: 180px;
      object-fit: cover;
    }

    .card-body {
      padding: 15px;
      font-size: 14px;
    }

    .card-title {
      font-size: 16px;
      font-weight: 600;
    }

    .card-text {
      font-size: 13px;
    }

    .btn-success {
      font-size: 13px;
      padding: 6px 10px;
      border-radius: 10px;
    }

    h2.text-primary {
      font-size: 26px;
      text-align: center;
      font-weight: 700;
    }

    .apartment-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 25px;
      justify-items: center;
    }

    section.container {
      max-width: 1000px;
    }

    footer {
      background: linear-gradient(135deg, var(--deep-navy), var(--primary-dark));
      color: white;
      text-align: center;
      padding: 30px;
      margin-top: 80px;
      border-top: 3px solid var(--accent-gold);
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item mx-2"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
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

<!-- APARTMENTS -->
<section class="container mt-5">
  <h2 class="mb-4 text-primary fw-bold">Available Apartments</h2>
  <div id="message-area"></div>
  <div class="apartment-grid">

    <?php if ($apartments): ?>
      <?php foreach ($apartments as $apt): ?>
        <div class="col-md-4 mb-4">
          <div class="card apartment-card clickable-card"
               data-bs-toggle="modal"
               data-bs-target="#apartmentModal"
               data-name="<?= htmlspecialchars($apt['Name']) ?>"
               data-description="<?= htmlspecialchars($apt['Description']) ?>"
               data-rate="<?= number_format($apt['MonthlyRate'],2) ?>"
               data-image="<?= htmlspecialchars($apt['Image'] ?: 'images/airbnb1.jpg') ?>">

            <img src="<?= htmlspecialchars($apt['Image'] ?: 'images/airbnb1.jpg') ?>" alt="<?= htmlspecialchars($apt['Name']) ?>">

            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($apt['Name']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($apt['Description']) ?></p>
              <p class="card-text"><strong>Monthly Rate:</strong> ₱<?= number_format($apt['MonthlyRate'], 2) ?></p>

              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant'): ?>
                <button class="btn btn-success btn-sm mt-auto apply-btn" data-apartment="<?= $apt['ApartmentID'] ?>">Apply Now</button>
              <?php else: ?>
                <button class="btn btn-success btn-sm mt-auto" data-bs-toggle="modal" data-bs-target="#loginModal">Apply Now</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No apartments available right now. Please check back later.</p>
    <?php endif; ?>
  </div>
</section>

<!-- APARTMENT MODAL -->
<div class="modal fade" id="apartmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="apartmentModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <img id="apartmentModalImage" src="" class="img-fluid w-100" alt="Apartment Image">
        <div class="p-4">
          <p id="apartmentModalDescription"></p>
          <p><strong>Monthly Rate:</strong> ₱<span id="apartmentModalRate"></span></p>
          <button class="btn btn-success w-100" id="modalApplyBtn">Apply Now</button>
        </div>
      </div>
    </div>
  </div>
</div>

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
// Check if URL has #loginModal hash
if (window.location.hash === '#loginModal') {
    $('#loginModal').modal('show');
}

// Login Form with SweetAlert
$('#loginForm').on('submit', function(e) {
  e.preventDefault();
  
  // Show loading
  Swal.fire({
    title: 'Logging in...',
    text: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
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
          window.location.href = response.redirect;
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Login Failed',
          text: response.message,
          confirmButtonColor: '#3498db'
        });
      }
    },
    error: function() {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred. Please try again.',
        confirmButtonColor: '#3498db'
      });
    }
  });
});

// Toggle password visibility in login modal
$('#toggleLoginPassword').on('click', function() {
  const passwordField = $('#loginPassword');
  const eyeIcon = $('#loginEyeIcon');
  
  if (passwordField.attr('type') === 'password') {
    passwordField.attr('type', 'text');
    eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
  } else {
    passwordField.attr('type', 'password');
    eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
  }
});

// Toggle password visibility in register modal
$('#toggleRegisterPassword').on('click', function() {
  const passwordField = $('#registerPassword');
  const eyeIcon = $('#registerEyeIcon');
  
  if (passwordField.attr('type') === 'password') {
    passwordField.attr('type', 'text');
    eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
  } else {
    passwordField.attr('type', 'password');
    eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
  }
});

// Toggle confirm password visibility in register modal
$('#toggleConfirmPassword').on('click', function() {
  const passwordField = $('#confirmPassword');
  const eyeIcon = $('#confirmEyeIcon');
  
  if (passwordField.attr('type') === 'password') {
    passwordField.attr('type', 'text');
    eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
  } else {
    passwordField.attr('type', 'password');
    eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
  }
});

$(function() {
  $('#registerForm').on('submit', function(e) {
    e.preventDefault();
    $.post('actions/register.php', $(this).serialize(), function(response) {
      if (response.trim() === 'OTP_SENT') {
        $('#otpOverlay').fadeIn();
      } else {
        Swal.fire('Error', response, 'error');
      }
    });
  });

  $('#otpForm').on('submit', function(e) {
    e.preventDefault();
    $.post('actions/register.php', $(this).serialize(), function(response) {
      if (response.trim() === 'OTP_VALID') {
        $('#otpOverlay').fadeOut();
        Swal.fire({ icon:'success', title:'Registration complete!', timer:1500, showConfirmButton:false })
        .then(() => window.location.href = 'index.php');
      } else {
        Swal.fire('Invalid OTP', 'Please try again.', 'error');
      }
    });
  });

  const apartmentModal = document.getElementById('apartmentModal');
  apartmentModal.addEventListener('show.bs.modal', event => {
    const card = event.relatedTarget;
    $('#apartmentModalLabel').text(card.dataset.name);
    $('#apartmentModalDescription').text(card.dataset.description);
    $('#apartmentModalRate').text(card.dataset.rate);
    $('#apartmentModalImage').attr('src', card.dataset.image);
  });
});
</script>
</body>
</html>