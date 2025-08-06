<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Require patient login
$auth->requireLogin('patient');

$db = getDB();
$currentUser = getCurrentUser();
$patientSSN = $currentUser['ssn'];

// Get patient information
$sql = "SELECT * FROM patient WHERE ssn = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$patientSSN]);
$patient = $stmt->fetch();

$message = '';
$messageType = '';

// Handle profile update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        $firstName = sanitizeInput($_POST['first_name']);
        $lastName = sanitizeInput($_POST['last_name']);
        $phone = sanitizeInput($_POST['phone']);
        $email = sanitizeInput($_POST['email']);
        $address = sanitizeInput($_POST['address']);
        $city = sanitizeInput($_POST['city']);
        $country = sanitizeInput($_POST['country']);
        $emergencyContactName = sanitizeInput($_POST['emergency_contact_name']);
        $emergencyContactPhone = sanitizeInput($_POST['emergency_contact_phone']);
        $bloodType = sanitizeInput($_POST['blood_type']);
        $allergies = sanitizeInput($_POST['allergies']);
        
        // Update patient information
        $sql = "UPDATE patient SET 
                first_name = ?, last_name = ?, phone = ?, email = ?, address = ?, 
                city = ?, country = ?, emergency_contact_name = ?, emergency_contact_phone = ?, 
                blood_type = ?, allergies = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE ssn = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $firstName, $lastName, $phone, $email, $address, $city, $country,
            $emergencyContactName, $emergencyContactPhone, $bloodType, $allergies, $patientSSN
        ]);
        
        if ($result) {
            // Update session data
            $_SESSION['full_name'] = $firstName . ' ' . $lastName;
            
            // Refresh patient data
            $stmt = $db->prepare("SELECT * FROM patient WHERE ssn = ?");
            $stmt->execute([$patientSSN]);
            $patient = $stmt->fetch();
            
            $message = 'Profile updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update profile. Please try again.';
            $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = 'An error occurred while updating your profile.';
        $messageType = 'danger';
        error_log("Profile update error: " . $e->getMessage());
    }
}

// Handle password change
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    try {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $sql = "SELECT password FROM patient_login WHERE patient_ssn = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$patientSSN]);
        $currentHash = $stmt->fetchColumn();
        
        if (!verifyPassword($currentPassword, $currentHash)) {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $message = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
            $messageType = 'danger';
        } else {
            // Update password
            $newHash = hashPassword($newPassword);
            $sql = "UPDATE patient_login SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE patient_ssn = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$newHash, $patientSSN])) {
                $message = 'Password changed successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to change password. Please try again.';
                $messageType = 'danger';
            }
        }
    } catch (Exception $e) {
        $message = 'An error occurred while changing your password.';
        $messageType = 'danger';
        error_log("Password change error: " . $e->getMessage());
    }
}

// Handle logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $auth->logout();
    redirect('../../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="profile.php" class="nav-link" style="background-color: rgba(255, 255, 255, 0.1);">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="records.php" class="nav-link">
                    <i class="fas fa-file-medical"></i>
                    Medical Records
                </a>
                <a href="search.php" class="nav-link">
                    <i class="fas fa-search"></i>
                    Search
                </a>
                <a href="payments.php" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    Payments
                </a>
                <div class="dropdown">
                    <a href="#" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <?php echo $currentUser['full_name']; ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            My Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="dropdown-item" style="background: none; border: none; width: 100%; text-align: left; padding: 0.75rem 1.5rem; cursor: pointer;">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Page Header -->
            <div class="dashboard-header">
                <div>
                    <h1>My Profile</h1>
                    <p>Manage your personal information and account settings</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Information -->
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-edit"></i>
                                Personal Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                                                   value="<?php echo $patient['date_of_birth']; ?>" readonly>
                                            <div class="form-text">Contact administration to change date of birth</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="gender" class="form-label">Gender</label>
                                            <input type="text" id="gender" name="gender" class="form-control" 
                                                   value="<?php echo $patient['gender']; ?>" readonly>
                                            <div class="form-text">Contact administration to change gender</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['phone']); ?>"
                                                   placeholder="+251-XX-XXXXXXX">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['email']); ?>"
                                                   placeholder="your.email@example.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="3" 
                                              placeholder="Enter your full address"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" id="city" name="city" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['city']); ?>"
                                                   placeholder="Enter your city">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" id="country" name="country" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['country']); ?>"
                                                   placeholder="Enter your country">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="mt-4 mb-3">Emergency Contact Information</h4>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['emergency_contact_name']); ?>"
                                                   placeholder="Full name of emergency contact">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($patient['emergency_contact_phone']); ?>"
                                                   placeholder="+251-XX-XXXXXXX">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="mt-4 mb-3">Medical Information</h4>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="blood_type" class="form-label">Blood Type</label>
                                            <select id="blood_type" name="blood_type" class="form-control form-select">
                                                <option value="">Select Blood Type</option>
                                                <?php 
                                                $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                                foreach ($bloodTypes as $type): 
                                                ?>
                                                    <option value="<?php echo $type; ?>" <?php echo $patient['blood_type'] === $type ? 'selected' : ''; ?>>
                                                        <?php echo $type; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="ssn" class="form-label">Patient ID (SSN)</label>
                                            <input type="text" id="ssn" name="ssn" class="form-control" 
                                                   value="<?php echo $patient['ssn']; ?>" readonly>
                                            <div class="form-text">Patient ID cannot be changed</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="allergies" class="form-label">Allergies and Medical Alerts</label>
                                    <textarea id="allergies" name="allergies" class="form-control" rows="4" 
                                              placeholder="List any allergies, medical conditions, or important medical information"><?php echo htmlspecialchars($patient['allergies']); ?></textarea>
                                    <div class="form-text">This information is critical for emergency situations. Please keep it up to date.</div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Update Profile
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                        <i class="fas fa-undo"></i>
                                        Reset Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-4">
                    <!-- Profile Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-id-card"></i>
                                Profile Summary
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="portal-icon" style="margin: 0 auto 1rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></h4>
                            <p>Patient ID: <?php echo $patient['ssn']; ?></p>
                            <p><strong>Member Since:</strong> <?php echo formatDate($patient['created_at'], 'M Y'); ?></p>
                            
                            <?php if ($patient['blood_type']): ?>
                                <div class="mt-3 p-2" style="background: var(--light-color); border-radius: var(--border-radius-md);">
                                    <strong>Blood Type:</strong> <?php echo $patient['blood_type']; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($patient['allergies']): ?>
                                <div class="mt-3 p-2" style="background: rgba(220, 53, 69, 0.1); border-radius: var(--border-radius-md); border-left: 4px solid var(--danger-color);">
                                    <strong style="color: var(--danger-color);">⚠️ Has Allergies</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lock"></i>
                                Change Password
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" 
                                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                                    <div class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning" style="width: 100%;">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i>
                                Account Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Account Created:</strong><br>
                                <?php echo formatDate($patient['created_at'], 'M j, Y g:i A'); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Last Updated:</strong><br>
                                <?php echo formatDate($patient['updated_at'], 'M j, Y g:i A'); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Account Status:</strong><br>
                                <span class="badge badge-success">Active</span>
                            </div>
                            
                            <div class="mt-3">
                                <h5>Need Help?</h5>
                                <p><i class="fas fa-phone"></i> +251-11-1234567</p>
                                <p><i class="fas fa-envelope"></i> support@gobahospital.et</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../assets/js/main.js"></script>
    <script>
        // Form validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            GobaHospital.formatPhoneNumber(this);
        });
        
        document.getElementById('emergency_contact_phone').addEventListener('input', function() {
            GobaHospital.formatPhoneNumber(this);
        });
        
        // Form submission confirmation
        document.querySelector('form[action=""]').addEventListener('submit', function(e) {
            if (e.submitter.name === 'action' && e.submitter.value === 'update_profile') {
                if (!confirm('Are you sure you want to update your profile information?')) {
                    e.preventDefault();
                }
            }
        });
        
        // Auto-save draft (simple localStorage implementation)
        const formInputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea, select');
        formInputs.forEach(input => {
            // Load saved data on page load
            const savedValue = localStorage.getItem('profile_' + input.name);
            if (savedValue && !input.readOnly) {
                input.value = savedValue;
            }
            
            // Save data on input
            input.addEventListener('input', function() {
                if (!this.readOnly) {
                    localStorage.setItem('profile_' + this.name, this.value);
                }
            });
        });
        
        // Clear localStorage on successful submit
        <?php if ($messageType === 'success'): ?>
            localStorage.clear();
        <?php endif; ?>
    </script>
</body>
</html>