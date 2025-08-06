<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goba Hospital - Patient Record Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav-container">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <span>Goba Hospital</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="#home" class="nav-link">Home</a></li>
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section id="home" class="hero">
            <div class="container">
                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 2rem;">You have been logged out successfully.</div>
                <?php endif; ?>
                <h1>Welcome to Goba Hospital</h1>
                <p>Advanced Patient Record Management System</p>
                <p>Providing efficient, secure, and comprehensive healthcare data management for patients, doctors, and medical staff.</p>
            </div>
        </section>

        <!-- Portal Access Section -->
        <section id="portals" class="portals-section">
            <div class="container">
                <h2 class="section-title">Access Your Portal</h2>
                <div class="portals-grid">
                    <!-- Patient Portal -->
                    <div class="portal-card">
                        <div class="portal-icon">👤</div>
                        <h3>Patient Portal</h3>
                        <p>Access your medical records, view consultation history, and manage your health information securely.</p>
                        <a href="patient/login.php" class="btn">Patient Login</a>
                    </div>

                    <!-- Doctor Portal -->
                    <div class="portal-card">
                        <div class="portal-icon">👨‍⚕️</div>
                        <h3>Doctor Portal</h3>
                        <p>Manage patient records, add consultation notes, schedule operations, and track medical history.</p>
                        <a href="doctor/login.php" class="btn">Doctor Login</a>
                    </div>

                    <!-- Medical Staff Portal -->
                    <div class="portal-card">
                        <div class="portal-icon">👩‍⚕️</div>
                        <h3>Medical Staff Portal</h3>
                        <p>Record medicine administration, manage dosage information, and assist in patient care.</p>
                        <a href="staff/login.php" class="btn">Staff Login</a>
                    </div>

                    <!-- Admin Portal -->
                    <div class="portal-card">
                        <div class="portal-icon">⚙️</div>
                        <h3>Admin Portal</h3>
                        <p>Manage user accounts, register new doctors and staff, and oversee system operations.</p>
                        <a href="admin/login.php" class="btn">Admin Login</a>
                    </div>

                    <!-- External Health Office Portal -->
                    <div class="portal-card">
                        <div class="portal-icon">🏢</div>
                        <h3>External Health Office</h3>
                        <p>Upload and transfer patient information between healthcare facilities and hospitals.</p>
                        <a href="external/login.php" class="btn btn-secondary">Health Office Login</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="portals-section" style="background: white;">
            <div class="container">
                <h2 class="section-title">About Our System</h2>
                <div class="portals-grid">
                    <div class="portal-card">
                        <div class="portal-icon">📊</div>
                        <h3>Comprehensive Records</h3>
                        <p>Store and manage complete patient medical histories including consultations, surgeries, and diagnoses.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🔒</div>
                        <h3>Secure & Private</h3>
                        <p>Advanced security measures ensure patient data privacy and comply with healthcare regulations.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🔍</div>
                        <h3>Quick Search</h3>
                        <p>Instantly find patient records using ID, reference numbers, or date and time filters.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">💳</div>
                        <h3>Payment Integration</h3>
                        <p>Integrated payment system supporting Commercial Bank, Awash Bank, Abyssinia Bank, and Telebirr.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🎵</div>
                        <h3>Audio Records</h3>
                        <p>Support for audio consultation records to capture detailed patient interactions.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">📱</div>
                        <h3>Mobile Friendly</h3>
                        <p>Responsive design works perfectly on all devices - desktop, tablet, and mobile.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="portals-section">
            <div class="container">
                <h2 class="section-title">Our Services</h2>
                <div class="portals-grid">
                    <div class="portal-card">
                        <div class="portal-icon">📋</div>
                        <h3>Patient Registration</h3>
                        <p>Register patients using National ID, passport, or birth certificate for comprehensive identity verification.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🩺</div>
                        <h3>Medical Consultations</h3>
                        <p>Record detailed consultation information including symptoms, diagnosis, and treatment plans.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🏥</div>
                        <h3>Surgery Management</h3>
                        <p>Track surgical procedures, complications, anesthesia details, and post-operative care.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">💊</div>
                        <h3>Medicine Administration</h3>
                        <p>Record medication dosages, track allergies, and monitor patient drug administration.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">📄</div>
                        <h3>File Management</h3>
                        <p>Upload and manage medical documents, images, and reports in PDF and image formats.</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">🔄</div>
                        <h3>Patient Transfer</h3>
                        <p>Facilitate patient transfers between healthcare facilities with complete medical documentation.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="portals-section" style="background: white;">
            <div class="container">
                <h2 class="section-title">Contact Information</h2>
                <div class="portals-grid">
                    <div class="portal-card">
                        <div class="portal-icon">📍</div>
                        <h3>Location</h3>
                        <p>Goba, Bale Zone<br>Oromia Region, Ethiopia</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">📞</div>
                        <h3>Phone</h3>
                        <p>+251-22-000-0000<br>Emergency: +251-22-000-0001</p>
                    </div>

                    <div class="portal-card">
                        <div class="portal-icon">✉️</div>
                        <h3>Email</h3>
                        <p>info@gobahospital.com<br>support@gobahospital.com</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Goba Hospital. All rights reserved. | Patient Record Management System</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Smooth scrolling for navigation links
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

        // Update active navigation link on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>