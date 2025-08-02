<?php
require_once 'includes/config.php';

$page_title = 'About Us';

include 'includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">
                <i class="fas fa-hospital"></i> About Goba Hospital
            </h1>
            <p class="lead text-muted">
                Committed to Excellence in Healthcare since 1990
            </p>
        </div>
    </div>

    <!-- Mission and Vision -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="text-primary mb-3">
                        <i class="fas fa-bullseye"></i> Our Mission
                    </h3>
                    <p class="text-muted">
                        To provide comprehensive, compassionate, and quality healthcare services to the 
                        people of Bale Zone and surrounding regions, utilizing modern technology and 
                        evidence-based medical practices while maintaining the highest standards of 
                        patient care and safety.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="text-primary mb-3">
                        <i class="fas fa-eye"></i> Our Vision
                    </h3>
                    <p class="text-muted">
                        To be the leading healthcare institution in the region, recognized for excellence 
                        in patient care, medical education, research, and community health improvement, 
                        while embracing innovative technologies and sustainable healthcare practices.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hospital Information -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-info-circle"></i> Hospital Information
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-map-marker-alt"></i> Location & Address
                            </h5>
                            <p class="text-muted">
                                Goba Hospital<br>
                                Goba, Bale Zone<br>
                                Oromia Region, Ethiopia<br>
                                P.O. Box 123, Goba
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-phone"></i> Contact Information
                            </h5>
                            <p class="text-muted">
                                <strong>Phone:</strong> +251-22-000-0000<br>
                                <strong>Emergency:</strong> +251-22-000-0001<br>
                                <strong>Email:</strong> info@gobahospital.com<br>
                                <strong>Website:</strong> www.gobahospital.com
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-calendar"></i> Establishment & Licensing
                            </h5>
                            <p class="text-muted">
                                <strong>Established:</strong> January 1, 1990<br>
                                <strong>License Number:</strong> GH-001-1990<br>
                                <strong>Accreditation:</strong> Ethiopian Hospital Alliance<br>
                                <strong>Certification:</strong> ISO 9001:2015
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-clock"></i> Operating Hours
                            </h5>
                            <p class="text-muted">
                                <strong>General Services:</strong> 24/7<br>
                                <strong>Outpatient:</strong> 8:00 AM - 6:00 PM<br>
                                <strong>Emergency:</strong> 24 hours<br>
                                <strong>Laboratory:</strong> 24/7
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-hospital-user"></i> Medical Services
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-stethoscope"></i> General Medicine
                            </h6>
                            <ul class="text-muted">
                                <li>Internal Medicine</li>
                                <li>Family Medicine</li>
                                <li>Preventive Care</li>
                                <li>Health Screenings</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-heartbeat"></i> Specialized Care
                            </h6>
                            <ul class="text-muted">
                                <li>Cardiology</li>
                                <li>Orthopedics</li>
                                <li>Pediatrics</li>
                                <li>Gynecology</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-procedures"></i> Surgical Services
                            </h6>
                            <ul class="text-muted">
                                <li>General Surgery</li>
                                <li>Emergency Surgery</li>
                                <li>Minor Procedures</li>
                                <li>Outpatient Surgery</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-vial"></i> Diagnostic Services
                            </h6>
                            <ul class="text-muted">
                                <li>Laboratory Tests</li>
                                <li>X-ray Imaging</li>
                                <li>Ultrasound</li>
                                <li>ECG/EKG</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-ambulance"></i> Emergency Services
                            </h6>
                            <ul class="text-muted">
                                <li>24/7 Emergency Care</li>
                                <li>Trauma Care</li>
                                <li>Critical Care</li>
                                <li>Ambulance Services</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <h6 class="text-success">
                                <i class="fas fa-pills"></i> Pharmacy Services
                            </h6>
                            <ul class="text-muted">
                                <li>Prescription Medications</li>
                                <li>Generic Medications</li>
                                <li>Medical Supplies</li>
                                <li>Consultation Services</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Features -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-computer"></i> Patient Record Management System Features
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-info">
                                <i class="fas fa-user-injured"></i> Patient Portal
                            </h6>
                            <ul class="text-muted">
                                <li>View complete medical history</li>
                                <li>Access consultation records</li>
                                <li>Search specific medical information</li>
                                <li>Download medical reports</li>
                                <li>Payment processing</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-info">
                                <i class="fas fa-user-md"></i> Doctor Portal
                            </h6>
                            <ul class="text-muted">
                                <li>Record patient consultations</li>
                                <li>Document surgical procedures</li>
                                <li>Enter diagnostic information</li>
                                <li>Audio recording capability</li>
                                <li>Patient referral system</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-info">
                                <i class="fas fa-user-nurse"></i> Staff Portal
                            </h6>
                            <ul class="text-muted">
                                <li>Medication dosage management</li>
                                <li>Patient care documentation</li>
                                <li>Medical information entry</li>
                                <li>Treatment tracking</li>
                                <li>Report generation</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-info">
                                <i class="fas fa-user-shield"></i> Admin Portal
                            </h6>
                            <ul class="text-muted">
                                <li>User management (doctors, patients, staff)</li>
                                <li>System configuration</li>
                                <li>Hospital information management</li>
                                <li>Data oversight and security</li>
                                <li>System logs and analytics</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0">
                        <i class="fas fa-cog"></i> Technology Stack
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <h6 class="text-warning">
                                <i class="fab fa-php"></i> Backend
                            </h6>
                            <p class="text-muted">PHP 7.4+</p>
                        </div>
                        
                        <div class="col-md-3">
                            <h6 class="text-warning">
                                <i class="fas fa-database"></i> Database
                            </h6>
                            <p class="text-muted">MySQL 5.7+</p>
                        </div>
                        
                        <div class="col-md-3">
                            <h6 class="text-warning">
                                <i class="fab fa-html5"></i> Frontend
                            </h6>
                            <p class="text-muted">HTML5, CSS3, JavaScript</p>
                        </div>
                        
                        <div class="col-md-3">
                            <h6 class="text-warning">
                                <i class="fab fa-bootstrap"></i> Framework
                            </h6>
                            <p class="text-muted">Bootstrap 5</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact CTA -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white border-0 shadow">
                <div class="card-body text-center p-5">
                    <h2 class="mb-3">
                        <i class="fas fa-phone"></i> Get in Touch
                    </h2>
                    <p class="lead mb-4">
                        For more information about our services or to schedule an appointment, 
                        please contact us.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="tel:+251220000000" class="btn btn-light btn-lg">
                            <i class="fas fa-phone"></i> Call Us
                        </a>
                        <a href="mailto:info@gobahospital.com" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-envelope"></i> Email Us
                        </a>
                        <a href="login.php" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Access Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>