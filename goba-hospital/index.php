<?php
require_once 'config/database.php';

// Check if user is already logged in
if (isLoggedIn()) {
    $userType = $_SESSION['user_type'];
    redirect("pages/{$userType}/dashboard.php");
}

// Handle login form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'login') {
    require_once 'includes/auth.php';
    
    $userType = sanitizeInput($_POST['user_type']);
    $userId = sanitizeInput($_POST['user_id']);
    $password = $_POST['password'];
    
    if ($auth->login($userType, $userId, $password)) {
        redirect("pages/{$userType}/dashboard.php");
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}

// Get error message from URL parameter
$error = isset($_GET['error']) ? $_GET['error'] : '';
$errorMessages = [
    'login_required' => 'Please log in to access this page.',
    'access_denied' => 'Access denied. You do not have permission to view this page.',
    'session_expired' => 'Your session has expired. Please log in again.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Patient Record Management System</title>
    <meta name="description" content="Goba Hospital Patient Record Management System - Secure, efficient medical record management for patients, doctors, and medical staff.">
    <meta name="keywords" content="hospital, medical records, patient management, healthcare, Goba Hospital">
    <meta name="author" content="Goba Hospital">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1><?php echo SITE_NAME; ?></h1>
            </div>
            
            <nav class="nav">
                <a href="#home" class="nav-link">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <a href="#about" class="nav-link">
                    <i class="fas fa-info-circle"></i>
                    About
                </a>
                <a href="#services" class="nav-link">
                    <i class="fas fa-stethoscope"></i>
                    Services
                </a>
                <a href="#contact" class="nav-link">
                    <i class="fas fa-phone"></i>
                    Contact
                </a>
                <div class="dropdown">
                    <a href="#" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="openLoginModal('patient')">
                            <i class="fas fa-user"></i>
                            Patient Login
                        </a>
                        <a href="#" class="dropdown-item" onclick="openLoginModal('doctor')">
                            <i class="fas fa-user-md"></i>
                            Doctor Login
                        </a>
                        <a href="#" class="dropdown-item" onclick="openLoginModal('staff')">
                            <i class="fas fa-user-nurse"></i>
                            Staff Login
                        </a>
                        <a href="#" class="dropdown-item" onclick="openLoginModal('admin')">
                            <i class="fas fa-user-shield"></i>
                            Admin Login
                        </a>
                        <a href="#" class="dropdown-item" onclick="openLoginModal('external')">
                            <i class="fas fa-hospital-user"></i>
                            External Office
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <h1>Welcome to Goba Hospital</h1>
            <p>Advanced Patient Record Management System for comprehensive healthcare delivery</p>
            <p>Secure • Efficient • Comprehensive • Modern</p>
            <div class="hero-buttons" style="margin-top: 2rem;">
                <button class="btn btn-primary btn-lg" onclick="openLoginModal('patient')">
                    <i class="fas fa-user"></i>
                    Patient Portal
                </button>
                <button class="btn btn-outline btn-lg" onclick="openLoginModal('doctor')">
                    <i class="fas fa-user-md"></i>
                    Medical Staff Portal
                </button>
            </div>
        </div>
    </section>

    <!-- Portal Access Section -->
    <section id="portals" style="padding: 4rem 0; background: var(--white);">
        <div class="container">
            <h2 class="text-center mb-5">Access Your Portal</h2>
            <div class="portal-grid">
                <!-- Patient Portal -->
                <div class="portal-card" onclick="openLoginModal('patient')">
                    <div class="portal-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Patient Portal</h3>
                    <p>View your medical records, consultation history, prescriptions, and manage appointments. Access your complete medical history securely.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>View medical records</li>
                        <li>Search consultation history</li>
                        <li>Download prescriptions</li>
                        <li>Update personal information</li>
                        <li>Payment processing</li>
                    </ul>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access Portal
                    </button>
                </div>

                <!-- Doctor Portal -->
                <div class="portal-card" onclick="openLoginModal('doctor')">
                    <div class="portal-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Doctor Portal</h3>
                    <p>Manage patient consultations, record diagnoses, prescribe treatments, and access comprehensive patient medical histories.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>Record consultations</li>
                        <li>Manage surgeries</li>
                        <li>Document diagnoses</li>
                        <li>Search patient records</li>
                        <li>Upload medical documents</li>
                    </ul>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access Portal
                    </button>
                </div>

                <!-- Medical Staff Portal -->
                <div class="portal-card" onclick="openLoginModal('staff')">
                    <div class="portal-icon">
                        <i class="fas fa-user-nurse"></i>
                    </div>
                    <h3>Medical Staff Portal</h3>
                    <p>Record medicine administration, assist with patient care documentation, and support medical operations.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>Record medicine dosages</li>
                        <li>Patient care documentation</li>
                        <li>View assigned patients</li>
                        <li>Update personal profile</li>
                        <li>Support medical operations</li>
                    </ul>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access Portal
                    </button>
                </div>

                <!-- Admin Portal -->
                <div class="portal-card" onclick="openLoginModal('admin')">
                    <div class="portal-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admin Portal</h3>
                    <p>System administration, user management, hospital operations oversight, and comprehensive system control.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>Register new users</li>
                        <li>Manage hospital data</li>
                        <li>System oversight</li>
                        <li>Delete records</li>
                        <li>Generate reports</li>
                    </ul>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access Portal
                    </button>
                </div>

                <!-- External Health Office Portal -->
                <div class="portal-card" onclick="openLoginModal('external')">
                    <div class="portal-icon">
                        <i class="fas fa-hospital-user"></i>
                    </div>
                    <h3>External Health Office</h3>
                    <p>Inter-hospital communication, patient referrals, and document sharing between healthcare facilities.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>Upload patient information</li>
                        <li>Send patient referrals</li>
                        <li>Document sharing</li>
                        <li>Inter-hospital communication</li>
                        <li>Transfer medical records</li>
                    </ul>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access Portal
                    </button>
                </div>

                <!-- Emergency Access -->
                <div class="portal-card" style="border-left: 4px solid var(--danger-color);">
                    <div class="portal-icon" style="background: var(--danger-color);">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3>Emergency Access</h3>
                    <p>Quick access for emergency medical personnel to retrieve critical patient information during emergencies.</p>
                    <ul style="text-align: left; margin: 1rem 0;">
                        <li>Emergency patient lookup</li>
                        <li>Critical medical alerts</li>
                        <li>Allergy information</li>
                        <li>Emergency contacts</li>
                        <li>Blood type information</li>
                    </ul>
                    <button class="btn btn-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Emergency Access
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" style="padding: 4rem 0; background: var(--light-color);">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <h2>About Our System</h2>
                    <p>The Goba Hospital Patient Record Management System is a comprehensive digital solution designed to revolutionize healthcare delivery through efficient medical record management.</p>
                    <p>Our system addresses the challenges of traditional paper-based medical records by providing:</p>
                    <ul>
                        <li><strong>Secure Data Storage:</strong> All medical records are encrypted and stored securely</li>
                        <li><strong>Rapid Retrieval:</strong> Instant access to patient medical history</li>
                        <li><strong>Multi-User Access:</strong> Separate portals for patients, doctors, staff, and administrators</li>
                        <li><strong>Inter-Hospital Communication:</strong> Seamless patient referrals and data sharing</li>
                        <li><strong>Payment Integration:</strong> Support for multiple Ethiopian banks</li>
                        <li><strong>Comprehensive Records:</strong> Consultations, surgeries, diagnoses, and prescriptions</li>
                    </ul>
                </div>
                <div class="col-6">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">5</div>
                            <div class="stat-label">User Portals</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">4</div>
                            <div class="stat-label">Supported Banks</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">System Availability</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Data Security</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" style="padding: 4rem 0; background: var(--white);">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="portal-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-notes-medical" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h4>Medical Records Management</h4>
                        <p>Comprehensive digital storage and management of all patient medical records with instant access.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h4>Advanced Search</h4>
                        <p>Powerful search capabilities to find patient records by various criteria including date, doctor, and reference number.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h4>Data Security</h4>
                        <p>Bank-level security with encryption, secure authentication, and role-based access control.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-mobile-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h4>Mobile Responsive</h4>
                        <p>Access your medical records from any device - desktop, tablet, or smartphone.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" style="padding: 4rem 0; background: var(--light-color);">
        <div class="container">
            <h2 class="text-center mb-5">Contact Information</h2>
            <div class="row">
                <div class="col-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-map-marker-alt" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                            <h4>Address</h4>
                            <p>Medical District, Goba<br>Bale Zone, Oromia Region<br>Ethiopia</p>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-phone" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                            <h4>Phone</h4>
                            <p>+251-11-1234567<br>Emergency: +251-11-9876543<br>Available 24/7</p>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-envelope" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                            <h4>Email</h4>
                            <p>info@gobahospital.et<br>support@gobahospital.et<br>admin@gobahospital.et</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div class="login-card" style="position: relative; max-width: 500px;">
            <button onclick="closeLoginModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">&times;</button>
            
            <div class="login-header">
                <h2 id="modalTitle">Login</h2>
                <p>Access your secure portal</p>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <?php echo isset($errorMessages[$error]) ? $errorMessages[$error] : $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="user_type" id="userType" value="patient">

                <div class="form-group">
                    <label for="userId" class="form-label">User ID</label>
                    <input type="text" id="userId" name="user_id" class="form-control" required>
                    <div class="form-text">Enter your assigned user ID</div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p>Don't have an account? Contact the hospital administration for registration.</p>
                <p><strong>Emergency Access:</strong> Call +251-11-9876543</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Goba Hospital</h3>
                    <p>Leading healthcare provider in Bale Zone, committed to delivering exceptional medical services with advanced technology.</p>
                    <p><strong>Version:</strong> <?php echo SITE_VERSION; ?></p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="#home">Home</a></p>
                    <p><a href="#about">About</a></p>
                    <p><a href="#services">Services</a></p>
                    <p><a href="#contact">Contact</a></p>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <p><a href="#">Emergency Care</a></p>
                    <p><a href="#">General Medicine</a></p>
                    <p><a href="#">Surgery</a></p>
                    <p><a href="#">Diagnostics</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-phone"></i> +251-11-1234567</p>
                    <p><i class="fas fa-envelope"></i> info@gobahospital.et</p>
                    <p><i class="fas fa-map-marker-alt"></i> Goba, Ethiopia</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Goba Hospital. All rights reserved. | Developed for healthcare excellence</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script>
        // Login modal functions
        function openLoginModal(userType) {
            const modal = document.getElementById('loginModal');
            const modalTitle = document.getElementById('modalTitle');
            const userTypeInput = document.getElementById('userType');
            
            const titles = {
                'patient': 'Patient Login',
                'doctor': 'Doctor Login',
                'staff': 'Medical Staff Login',
                'admin': 'Administrator Login',
                'external': 'External Health Office Login'
            };
            
            modalTitle.textContent = titles[userType] || 'Login';
            userTypeInput.value = userType;
            modal.style.display = 'flex';
            
            // Focus on user ID field
            setTimeout(() => {
                document.getElementById('userId').focus();
            }, 100);
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });
        
        // Handle ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-open login modal if error parameter exists
        <?php if (isset($_GET['error'])): ?>
            setTimeout(() => {
                openLoginModal('patient');
            }, 500);
        <?php endif; ?>
    </script>
</body>
</html>