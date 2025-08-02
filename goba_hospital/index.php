<?php
require_once 'includes/config.php';

$page_title = 'Home';

// Redirect logged-in users to their dashboard
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_SESSION['user_type'] . '/dashboard.php';
    header("Location: $redirect_url");
    exit();
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-hospital"></i> Goba Hospital
                </h1>
                <p class="lead mb-4">
                    Advanced Patient Record Management System for comprehensive healthcare delivery.
                    Empowering healthcare professionals with efficient digital solutions.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="login.php" class="btn btn-light btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Access Portal
                    </a>
                    <a href="about.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-hospital-alt" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">System Features</h2>
            <p class="lead text-muted">Comprehensive healthcare management capabilities</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-injured fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Patient Management</h5>
                    <p class="card-text text-muted">
                        Complete patient records management with medical history, 
                        personal information, and treatment tracking.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-stethoscope fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Medical Records</h5>
                    <p class="card-text text-muted">
                        Digital consultation records, surgery notes, diagnoses, 
                        and prescription management with audio recording support.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-md fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Doctor Portal</h5>
                    <p class="card-text text-muted">
                        Dedicated interface for doctors to manage patients, 
                        record consultations, and access medical histories.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-pills fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Medication Management</h5>
                    <p class="card-text text-muted">
                        Track medication dosages, administration schedules, 
                        and prescription management by medical staff.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-credit-card fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Payment Processing</h5>
                    <p class="card-text text-muted">
                        Integrated payment system supporting multiple Ethiopian banks 
                        including Telebirr, Commercial Bank, and more.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Patient Referrals</h5>
                    <p class="card-text text-muted">
                        Seamless patient referral system for transferring patients 
                        between hospitals and healthcare facilities.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Portals Section -->
<div class="bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold text-primary">Access Portals</h2>
                <p class="lead text-muted">Secure access for different user types</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-shield fa-4x text-primary mb-3"></i>
                        <h5 class="card-title">Admin Portal</h5>
                        <p class="card-text text-muted mb-3">
                            System administration and user management
                        </p>
                        <a href="login.php?type=admin" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Admin Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-md fa-4x text-success mb-3"></i>
                        <h5 class="card-title">Doctor Portal</h5>
                        <p class="card-text text-muted mb-3">
                            Patient records and medical management
                        </p>
                        <a href="login.php?type=doctor" class="btn btn-success">
                            <i class="fas fa-sign-in-alt"></i> Doctor Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-injured fa-4x text-info mb-3"></i>
                        <h5 class="card-title">Patient Portal</h5>
                        <p class="card-text text-muted mb-3">
                            View medical history and manage payments
                        </p>
                        <a href="login.php?type=patient" class="btn btn-info">
                            <i class="fas fa-sign-in-alt"></i> Patient Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-nurse fa-4x text-warning mb-3"></i>
                        <h5 class="card-title">Staff Portal</h5>
                        <p class="card-text text-muted mb-3">
                            Medication management and patient care
                        </p>
                        <a href="login.php?type=staff" class="btn btn-warning">
                            <i class="fas fa-sign-in-alt"></i> Staff Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hospital Information -->
<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">
                            <i class="fas fa-hospital"></i> About Goba Hospital
                        </h3>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </h6>
                            <p class="text-muted">
                                Goba, Bale Zone<br>
                                Oromia Region, Ethiopia
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-phone"></i> Contact
                            </h6>
                            <p class="text-muted">
                                Phone: +251-22-000-0000<br>
                                Email: info@gobahospital.com
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-calendar"></i> Established
                            </h6>
                            <p class="text-muted">
                                January 1, 1990<br>
                                License: GH-001-1990
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-globe"></i> Website
                            </h6>
                            <p class="text-muted">
                                www.gobahospital.com<br>
                                Patient Portal Available 24/7
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Committed to providing quality healthcare services with modern technology 
                            and compassionate care for the community of Bale Zone and beyond.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <i class="fas fa-user-injured fa-3x mb-3"></i>
                <h3 class="fw-bold">1000+</h3>
                <p>Patients Served</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-user-md fa-3x mb-3"></i>
                <h3 class="fw-bold">50+</h3>
                <p>Medical Professionals</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-procedures fa-3x mb-3"></i>
                <h3 class="fw-bold">500+</h3>
                <p>Successful Procedures</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                <h3 class="fw-bold">24/7</h3>
                <p>Emergency Services</p>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary-color), #0a58ca);
}

.card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}

.stats-section {
    background: linear-gradient(135deg, var(--primary-color), #0a58ca);
}
</style>

<?php include 'includes/footer.php'; ?>