<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

    .navbar {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
      border-bottom: 3px solid var(--accent-gold);
      padding: 1rem 0;
      position: relative;
      z-index: 1000;
    }

    .navbar::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }

    .navbar-brand {
      font-size: 1.8rem;
      font-weight: 700;
      color: white !important;
      letter-spacing: 1px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .navbar-toggler {
      border-color: var(--accent-gold);
    }

    .navbar-toggler-icon {
      filter: brightness(0) invert(1);
    }

    .nav-link {
      color: rgba(255,255,255,0.8) !important;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      padding: 0.5rem 1rem !important;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 2px;
      background: var(--accent-gold);
      transition: width 0.3s ease;
    }

    .nav-link:hover::after,
    .nav-link.active::after {
      width: 80%;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--accent-gold) !important;
    }

    .about-section {
      max-width: 900px;
      margin: 120px auto;
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      padding: 60px;
      border-radius: 30px;
      box-shadow: 
        0 30px 80px rgba(0,0,0,0.2),
        inset 0 1px 0 rgba(255,255,255,0.6);
      border: 2px solid rgba(212, 175, 55, 0.3);
      position: relative;
      z-index: 1;
      animation: fadeInUp 1s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .about-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      border-radius: 30px 30px 0 0;
    }

    .about-section h2 {
      font-weight: 800;
      font-size: 3rem;
      color: var(--primary-dark);
      margin-bottom: 40px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
      position: relative;
      display: inline-block;
    }

    .about-section h2::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 4px;
      background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
      border-radius: 2px;
    }

    .about-section p {
      font-size: 1.15rem;
      line-height: 2;
      color: var(--earth-brown);
      font-weight: 500;
      margin-bottom: 25px;
      text-align: justify;
    }

    .about-section .btn {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border: none;
      color: var(--deep-navy);
      padding: 15px 50px;
      border-radius: 30px;
      font-weight: 700;
      letter-spacing: 1px;
      transition: all 0.4s ease;
      box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
      text-transform: uppercase;
      font-size: 1rem;
      margin-top: 20px;
    }

    .about-section .btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(212, 175, 55, 0.6);
      background: linear-gradient(135deg, var(--luxury-gold) 0%, var(--accent-gold) 100%);
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }

    .deco-1 {
      top: 15%;
      left: 8%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.15), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 20%;
      right: 10%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.15), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
    }

    @media (max-width: 768px) {
      .about-section {
        margin: 80px 20px;
        padding: 40px 30px;
      }

      .about-section h2 {
        font-size: 2.2rem;
      }

      .about-section p {
        font-size: 1rem;
        text-align: left;
      }
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item mx-2"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item mx-2"><a class="nav-link active" href="#">About</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="about-section">
  <h2 class="text-center mb-4">Our Story</h2>
  <p>
    ApartmentHub was established with a simple vision — to make apartment hunting and management easy,
    transparent, and convenient for everyone. From humble beginnings as a local directory, it has grown
    into a modern digital platform connecting tenants with trusted property owners.
  </p>
  <p>
    Our mission is to simplify apartment living through innovation and reliability.
    At ApartmentHub, we value community, comfort, and convenience — ensuring you find not just a space,
    but a home.
  </p>
  <p class="text-center mt-4"><a href="index.php" class="btn btn-primary">Back to Home</a></p>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>