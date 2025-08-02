<?php
require_once 'includes/config.php';
startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                About Goba Hospital
                            </h2>
                        </div>
                        <div class="card-body">
                            <h3>Our Mission</h3>
                            <p class="lead">
                                To provide exceptional healthcare services through innovative technology and compassionate care, 
                                ensuring the well-being of our community.
                            </p>
                            
                            <h3>Patient Record Management System</h3>
                            <p>
                                The Goba Hospital Patient Record Management System is a comprehensive digital solution designed 
                                to streamline healthcare operations and improve patient care delivery.
                            </p>
                            
                            <h4>Key Features:</h4>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Secure patient record storage</li>
                                <li><i class="fas fa-check text-success me-2"></i>Role-based access control</li>
                                <li><i class="fas fa-check text-success me-2"></i>Audio recording capabilities</li>
                                <li><i class="fas fa-check text-success me-2"></i>Comprehensive search functionality</li>
                                <li><i class="fas fa-check text-success me-2"></i>Payment processing</li>
                                <li><i class="fas fa-check text-success me-2"></i>Patient referral system</li>
                            </ul>
                            
                            <h3>Contact Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="fas fa-map-marker-alt me-2"></i><strong>Address:</strong><br>
                                    Goba, Ethiopia</p>
                                    <p><i class="fas fa-phone me-2"></i><strong>Phone:</strong><br>
                                    +251-123-456-789</p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="fas fa-envelope me-2"></i><strong>Email:</strong><br>
                                    info@gobahospital.com</p>
                                    <p><i class="fas fa-clock me-2"></i><strong>Hours:</strong><br>
                                    24/7 Emergency Services</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-hospital me-2"></i>Goba Hospital</h5>
                    <p class="mb-0">Providing quality healthcare services to our community.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Goba Hospital. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>