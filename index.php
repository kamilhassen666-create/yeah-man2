<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goba Hospital - Patient Record Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <i class="fas fa-hospital"></i>
                    <span>Goba Hospital</span>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#home" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="#about" class="nav-link">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="#services" class="nav-link">Services</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact" class="nav-link">Contact</a>
                    </li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Welcome to Goba Hospital</h1>
                <h2>Patient Record Management System</h2>
                <p>Modern, secure, and efficient patient record management for better healthcare delivery in Ethiopia.</p>
            </div>
            <div class="portal-cards">
                <div class="portal-card">
                    <div class="card-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <h3>Patient Portal</h3>
                    <p>Access your medical records, view consultation history, and manage your health information.</p>
                    <a href="auth/login.php?type=patient" class="btn btn-primary">Patient Login</a>
                </div>
                
                <div class="portal-card">
                    <div class="card-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Doctor Portal</h3>
                    <p>Manage patient records, consultations, diagnoses, and surgical information.</p>
                    <a href="auth/login.php?type=doctor" class="btn btn-primary">Doctor Login</a>
                </div>
                
                <div class="portal-card">
                    <div class="card-icon">
                        <i class="fas fa-user-nurse"></i>
                    </div>
                    <h3>Staff Portal</h3>
                    <p>Record medicine administration, manage patient care, and support medical operations.</p>
                    <a href="auth/login.php?type=staff" class="btn btn-primary">Staff Login</a>
                </div>
                
                <div class="portal-card">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admin Portal</h3>
                    <p>System administration, user management, and hospital data oversight.</p>
                    <a href="auth/login.php?type=admin" class="btn btn-primary">Admin Login</a>
                </div>
                
                <div class="portal-card">
                    <div class="card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>External Health Office</h3>
                    <p>Upload patient information and coordinate care between healthcare facilities.</p>
                    <a href="auth/login.php?type=external_office" class="btn btn-primary">Office Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2>About Our System</h2>
                <p>Modern healthcare demands modern solutions</p>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3>Digital Healthcare Records</h3>
                    <p>Our Patient Record Management System is designed to replace traditional paper-based systems with a secure, efficient, and comprehensive digital solution. Built specifically for Ethiopian healthcare facilities, it supports multiple languages and integrates with local banking systems.</p>
                    
                    <div class="features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Secure & Private</h4>
                                <p>Advanced encryption and secure access controls protect patient data</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Real-time Access</h4>
                                <p>Instant access to patient records when and where you need them</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-search"></i>
                            <div>
                                <h4>Quick Search</h4>
                                <p>Find patient information using ID, date, or reference number</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <div>
                                <h4>Mobile Friendly</h4>
                                <p>Access the system from any device, anywhere</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="assets/images/hospital-tech.jpg" alt="Hospital Technology" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>System Features</h2>
                <p>Comprehensive healthcare management solutions</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-notes-medical"></i>
                    <h3>Consultation Records</h3>
                    <p>Record and manage patient consultations with audio support and detailed documentation.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-procedures"></i>
                    <h3>Surgery Management</h3>
                    <p>Track surgical procedures, complications, and post-operative care instructions.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-diagnoses"></i>
                    <h3>Diagnosis Tracking</h3>
                    <p>Comprehensive diagnosis management with ICD codes and severity tracking.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-pills"></i>
                    <h3>Medication Administration</h3>
                    <p>Track medicine dosages, allergies, and administration by medical staff.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>Hospital Transfers</h3>
                    <p>Coordinate patient transfers between healthcare facilities with complete medical history.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-credit-card"></i>
                    <h3>Payment Integration</h3>
                    <p>Integrated payment processing with Ethiopian banks including CBE, Awash, Abyssinia, and Telebirr.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Contact Information</h2>
                <p>Get in touch with Goba Hospital</p>
            </div>
            <div class="contact-info">
                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Address</h3>
                    <p>Goba, Bale Zone<br>Oromia Region, Ethiopia</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>+251-22-661-0001</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>info@gobahospital.et</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-clock"></i>
                    <h3>Emergency</h3>
                    <p>24/7 Emergency Services Available</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-hospital"></i> Goba Hospital</h3>
                    <p>Providing quality healthcare services with modern technology and compassionate care.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Patient Portals</h4>
                    <ul>
                        <li><a href="auth/login.php?type=patient">Patient Login</a></li>
                        <li><a href="auth/login.php?type=doctor">Doctor Login</a></li>
                        <li><a href="auth/login.php?type=staff">Staff Login</a></li>
                        <li><a href="auth/login.php?type=admin">Admin Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>System Information</h4>
                    <p>Patient Record Management System v1.0</p>
                    <p>Built for Ethiopian Healthcare</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Goba Hospital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>