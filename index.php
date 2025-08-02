<?php
require_once 'includes/config.php';
startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
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

    <!-- Hero Section -->
    <section class="hero-section py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-hospital me-3"></i>
                        Goba Hospital
                    </h1>
                    <h2 class="h3 mb-4">Patient Record Management System</h2>
                    <p class="lead mb-4">
                        Streamlining healthcare delivery through comprehensive patient record management. 
                        Access medical information securely and efficiently.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="login.php" class="btn btn-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="about.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/images/hospital-hero.svg" alt="Hospital" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Selection -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold mb-3">Access Your Portal</h2>
                    <p class="lead text-muted">Choose your role to access the appropriate portal</p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Patient Portal -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-injured fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Patient Portal</h5>
                            <p class="card-text">View your medical records, manage appointments, and process payments.</p>
                            <a href="patient/" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>Access Portal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Doctor Portal -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-md fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Doctor Portal</h5>
                            <p class="card-text">Record consultations, surgeries, and access patient medical history.</p>
                            <a href="doctor/" class="btn btn-success">
                                <i class="fas fa-arrow-right me-2"></i>Access Portal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Staff Portal -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-nurse fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Staff Portal</h5>
                            <p class="card-text">Manage medication dosages and patient information.</p>
                            <a href="staff/" class="btn btn-info text-white">
                                <i class="fas fa-arrow-right me-2"></i>Access Portal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Admin Portal -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-shield fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Admin Portal</h5>
                            <p class="card-text">Manage users, hospitals, and system administration.</p>
                            <a href="admin/" class="btn btn-warning text-dark">
                                <i class="fas fa-arrow-right me-2"></i>Access Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold mb-3">System Features</h2>
                    <p class="lead text-muted">Comprehensive healthcare management solutions</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-database fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Secure Records</h5>
                            <p class="card-text">All patient records are securely stored and encrypted for privacy protection.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-search fa-2x text-success"></i>
                            </div>
                            <h5 class="card-title">Quick Search</h5>
                            <p class="card-text">Find patient information quickly using ID, name, or reference numbers.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-microphone fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title">Audio Recording</h5>
                            <p class="card-text">Record consultation audio for better documentation and review.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-credit-card fa-2x text-warning"></i>
                            </div>
                            <h5 class="card-title">Payment Processing</h5>
                            <p class="card-text">Multiple payment methods including banks and mobile money services.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-exchange-alt fa-2x text-danger"></i>
                            </div>
                            <h5 class="card-title">Referral System</h5>
                            <p class="card-text">Efficient patient referral system between hospitals and departments.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">Analytics</h5>
                            <p class="card-text">Comprehensive reporting and analytics for better decision making.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card">
                        <h3>500+</h3>
                        <p>Patients Served</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card">
                        <h3>50+</h3>
                        <p>Medical Staff</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card">
                        <h3>1000+</h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card">
                        <h3>99%</h3>
                        <p>Satisfaction Rate</p>
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
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>