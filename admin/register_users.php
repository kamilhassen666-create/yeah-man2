<?php
require_once '../includes/functions.php';
require_login(['admin']);

$admin_info = get_user_info($_SESSION['user_id'], 'admin');

$error_message = '';
$success_message = '';
$registration_type = $_GET['type'] ?? 'patient';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = sanitize_input($_POST['type']);
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $ssn = sanitize_input($_POST['ssn']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $validation_result = validate_required_fields([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'ssn' => $ssn,
        'username' => $username,
        'password' => $password,
        'confirm_password' => $confirm_password
    ]);
    
    if ($validation_result !== true) {
        $error_message = $validation_result;
    } elseif (!validate_ssn($ssn)) {
        $error_message = 'Please enter a valid SSN/ID number.';
    } elseif (!empty($email) && !validate_email($email)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!empty($phone) && !validate_phone($phone)) {
        $error_message = 'Please enter a valid phone number.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Check if SSN already exists
        $existing_user = null;
        switch ($type) {
            case 'patient':
                $existing_user = getRow("SELECT ssn FROM patient WHERE ssn = ?", [$ssn]);
                break;
            case 'doctor':
                $existing_user = getRow("SELECT ssn FROM doctor WHERE ssn = ?", [$ssn]);
                break;
            case 'staff':
                $existing_user = getRow("SELECT ssn FROM medical_staff WHERE ssn = ?", [$ssn]);
                break;
        }
        
        if ($existing_user) {
            $error_message = 'A user with this SSN/ID already exists.';
        } else {
            // Check if username already exists
            $existing_username = null;
            switch ($type) {
                case 'patient':
                    $existing_username = getRow("SELECT username FROM patient_login WHERE username = ?", [$username]);
                    break;
                case 'doctor':
                    $existing_username = getRow("SELECT username FROM doctor_login WHERE username = ?", [$username]);
                    break;
                case 'staff':
                    $existing_username = getRow("SELECT username FROM staff_login WHERE username = ?", [$username]);
                    break;
            }
            
            if ($existing_username) {
                $error_message = 'Username already exists. Please choose a different username.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin transaction
                try {
                    $db->beginTransaction();
                    
                    // Insert based on type
                    switch ($type) {
                        case 'patient':
                            $date_of_birth = sanitize_input($_POST['date_of_birth']);
                            $gender = sanitize_input($_POST['gender']);
                            $blood_type = sanitize_input($_POST['blood_type']);
                            $allergies = sanitize_input($_POST['allergies']);
                            $emergency_contact = sanitize_input($_POST['emergency_contact']);
                            $emergency_phone = sanitize_input($_POST['emergency_phone']);
                            
                            // Insert patient
                            $patient_query = "INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, email, phone, address, emergency_contact, emergency_phone, blood_type, allergies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $patient_result = executeQuery($patient_query, [$ssn, $first_name, $last_name, $date_of_birth, $gender, $email, $phone, $address, $emergency_contact, $emergency_phone, $blood_type, $allergies]);
                            
                            if ($patient_result) {
                                // Insert patient login
                                $login_query = "INSERT INTO patient_login (patient_ssn, username, password, full_name, email) VALUES (?, ?, ?, ?, ?)";
                                $login_result = executeQuery($login_query, [$ssn, $username, $hashed_password, $first_name . ' ' . $last_name, $email]);
                                
                                if ($login_result) {
                                    $success_message = "Patient '{$first_name} {$last_name}' registered successfully!";
                                    log_audit($_SESSION['user_id'], 'Admin', 'User Registration', 'patient', $ssn);
                                } else {
                                    throw new Exception('Failed to create patient login.');
                                }
                            } else {
                                throw new Exception('Failed to register patient.');
                            }
                            break;
                            
                        case 'doctor':
                            $specialization = sanitize_input($_POST['specialization']);
                            $license_number = sanitize_input($_POST['license_number']);
                            $department = sanitize_input($_POST['department']);
                            $hospital_id = sanitize_input($_POST['hospital_id']) ?: 1; // Default to main hospital
                            
                            // Insert doctor
                            $doctor_query = "INSERT INTO doctor (ssn, first_name, last_name, specialization, license_number, email, phone, address, department, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $doctor_result = executeQuery($doctor_query, [$ssn, $first_name, $last_name, $specialization, $license_number, $email, $phone, $address, $department, $hospital_id]);
                            
                            if ($doctor_result) {
                                // Insert doctor login
                                $login_query = "INSERT INTO doctor_login (doctor_ssn, username, password, full_name, email) VALUES (?, ?, ?, ?, ?)";
                                $login_result = executeQuery($login_query, [$ssn, $username, $hashed_password, 'Dr. ' . $first_name . ' ' . $last_name, $email]);
                                
                                if ($login_result) {
                                    $success_message = "Doctor '{$first_name} {$last_name}' registered successfully!";
                                    log_audit($_SESSION['user_id'], 'Admin', 'User Registration', 'doctor', $ssn);
                                } else {
                                    throw new Exception('Failed to create doctor login.');
                                }
                            } else {
                                throw new Exception('Failed to register doctor.');
                            }
                            break;
                            
                        case 'staff':
                            $department = sanitize_input($_POST['department']);
                            $position = sanitize_input($_POST['position']);
                            $shift = sanitize_input($_POST['shift']);
                            $hospital_id = sanitize_input($_POST['hospital_id']) ?: 1; // Default to main hospital
                            
                            // Insert staff
                            $staff_query = "INSERT INTO medical_staff (ssn, first_name, last_name, department, position, shift, email, phone, address, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $staff_result = executeQuery($staff_query, [$ssn, $first_name, $last_name, $department, $position, $shift, $email, $phone, $address, $hospital_id]);
                            
                            if ($staff_result) {
                                // Insert staff login
                                $login_query = "INSERT INTO staff_login (staff_ssn, username, password, full_name, email) VALUES (?, ?, ?, ?, ?)";
                                $login_result = executeQuery($login_query, [$ssn, $username, $hashed_password, $first_name . ' ' . $last_name, $email]);
                                
                                if ($login_result) {
                                    $success_message = "Medical staff '{$first_name} {$last_name}' registered successfully!";
                                    log_audit($_SESSION['user_id'], 'Admin', 'User Registration', 'medical_staff', $ssn);
                                } else {
                                    throw new Exception('Failed to create staff login.');
                                }
                            } else {
                                throw new Exception('Failed to register medical staff.');
                            }
                            break;
                    }
                    
                    $db->commit();
                    
                } catch (Exception $e) {
                    $db->rollback();
                    $error_message = $e->getMessage();
                }
            }
        }
    }
}

// Get hospitals for dropdown
$hospitals = getRows("SELECT id, name FROM hospital ORDER BY name") ?: [];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Users - Admin Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav admin-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital - Admin</span>
        </div>
        <div class="nav-user">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-shield"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar admin-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="user_management.php">
                        <i class="fas fa-users-cog"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="active">
                    <a href="register_users.php">
                        <i class="fas fa-user-plus"></i>
                        <span>Register Users</span>
                    </a>
                </li>
                <li>
                    <a href="hospital_management.php">
                        <i class="fas fa-building"></i>
                        <span>Hospital Management</span>
                    </a>
                </li>
                <li>
                    <a href="patient_referrals.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Patient Referrals</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="audit.php">
                        <i class="fas fa-history"></i>
                        <span>Audit Log</span>
                    </a>
                </li>
                <li>
                    <a href="system.php">
                        <i class="fas fa-server"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                <li>
                    <a href="security.php">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1><i class="fas fa-user-plus"></i> Register New User</h1>
                <div class="header-actions">
                    <a href="user_management.php" class="btn btn-outline">
                        <i class="fas fa-users-cog"></i> Manage Users
                    </a>
                </div>
            </div>

            <!-- User Type Selection -->
            <div class="user-type-selection">
                <h3>Select User Type to Register</h3>
                <div class="type-cards">
                    <div class="type-card <?php echo $registration_type === 'patient' ? 'active' : ''; ?>" 
                         onclick="selectUserType('patient')">
                        <div class="type-icon patient">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Patient</h4>
                        <p>Register a new patient with medical information</p>
                    </div>
                    
                    <div class="type-card <?php echo $registration_type === 'doctor' ? 'active' : ''; ?>" 
                         onclick="selectUserType('doctor')">
                        <div class="type-icon doctor">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h4>Doctor</h4>
                        <p>Register a medical doctor with specialization</p>
                    </div>
                    
                    <div class="type-card <?php echo $registration_type === 'staff' ? 'active' : ''; ?>" 
                         onclick="selectUserType('staff')">
                        <div class="type-icon staff">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <h4>Medical Staff</h4>
                        <p>Register nursing and medical support staff</p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="registration-form-container">
                <form method="POST" action="" class="registration-form" id="registrationForm">
                    <input type="hidden" name="type" id="userType" value="<?php echo htmlspecialchars($registration_type); ?>">
                    
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="ssn">SSN/ID Number <span class="required">*</span></label>
                                <input type="text" id="ssn" name="ssn" class="form-control" 
                                       placeholder="Enter unique ID (SSN, NID, Passport, etc.)" required>
                                <small class="form-help">This will be the unique identifier for the user</small>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Patient-specific fields -->
                    <div class="form-section patient-fields" style="display: <?php echo $registration_type === 'patient' ? 'block' : 'none'; ?>">
                        <h3><i class="fas fa-heart"></i> Medical Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender <span class="required">*</span></label>
                                <select id="gender" name="gender" class="form-control">
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="blood_type">Blood Type</label>
                                <select id="blood_type" name="blood_type" class="form-control">
                                    <option value="">Select blood type</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="allergies">Known Allergies</label>
                                <input type="text" id="allergies" name="allergies" class="form-control" 
                                       placeholder="List any known allergies">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="emergency_contact">Emergency Contact Name</label>
                                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="emergency_phone">Emergency Contact Phone</label>
                                <input type="tel" id="emergency_phone" name="emergency_phone" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Doctor-specific fields -->
                    <div class="form-section doctor-fields" style="display: <?php echo $registration_type === 'doctor' ? 'block' : 'none'; ?>">
                        <h3><i class="fas fa-stethoscope"></i> Professional Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="specialization">Specialization</label>
                                <input type="text" id="specialization" name="specialization" class="form-control" 
                                       placeholder="e.g., Cardiology, Pediatrics, General Medicine">
                            </div>
                            <div class="form-group">
                                <label for="license_number">Medical License Number</label>
                                <input type="text" id="license_number" name="license_number" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" id="department" name="department" class="form-control" 
                                       placeholder="e.g., Emergency, ICU, Outpatient">
                            </div>
                            <div class="form-group">
                                <label for="hospital_id">Hospital</label>
                                <select id="hospital_id" name="hospital_id" class="form-control">
                                    <?php foreach ($hospitals as $hospital): ?>
                                        <option value="<?php echo $hospital['id']; ?>"><?php echo htmlspecialchars($hospital['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Staff-specific fields -->
                    <div class="form-section staff-fields" style="display: <?php echo $registration_type === 'staff' ? 'block' : 'none'; ?>">
                        <h3><i class="fas fa-hospital-user"></i> Employment Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="department_staff">Department</label>
                                <select id="department_staff" name="department" class="form-control">
                                    <option value="">Select department</option>
                                    <option value="Emergency">Emergency</option>
                                    <option value="ICU">Intensive Care Unit</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Maternity">Maternity</option>
                                    <option value="Pharmacy">Pharmacy</option>
                                    <option value="Laboratory">Laboratory</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Administration">Administration</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" id="position" name="position" class="form-control" 
                                       placeholder="e.g., Registered Nurse, Nurse Practitioner, Technician">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="shift">Shift</label>
                                <select id="shift" name="shift" class="form-control">
                                    <option value="">Select shift</option>
                                    <option value="Day Shift">Day Shift (7AM - 7PM)</option>
                                    <option value="Night Shift">Night Shift (7PM - 7AM)</option>
                                    <option value="Rotating">Rotating Shifts</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="hospital_id_staff">Hospital</label>
                                <select id="hospital_id_staff" name="hospital_id" class="form-control">
                                    <?php foreach ($hospitals as $hospital): ?>
                                        <option value="<?php echo $hospital['id']; ?>"><?php echo htmlspecialchars($hospital['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Login Credentials Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-key"></i> Login Credentials</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input type="text" id="username" name="username" class="form-control" required>
                                <small class="form-help">Username must be unique across the system</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password <span class="required">*</span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <small class="form-help">Password must be at least 6 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register User
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <style>
        .admin-nav {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .admin-sidebar {
            border-right: 3px solid #6366f1;
        }

        .admin-sidebar .sidebar-menu li.active a,
        .admin-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .user-type-selection {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .user-type-selection h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .type-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .type-card {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .type-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.1);
        }

        .type-card.active {
            border-color: #6366f1;
            background: #eef2ff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
        }

        .type-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }

        .type-icon.patient { background: #3b82f6; }
        .type-icon.doctor { background: #10b981; }
        .type-icon.staff { background: #f59e0b; }

        .type-card h4 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .type-card p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .registration-form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #6366f1;
        }

        .form-help {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .type-cards {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function selectUserType(type) {
            // Update hidden field
            document.getElementById('userType').value = type;
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('type', type);
            window.history.pushState({}, '', url);
            
            // Update active card
            document.querySelectorAll('.type-card').forEach(card => {
                card.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Show/hide relevant form sections
            document.querySelectorAll('.patient-fields').forEach(el => {
                el.style.display = type === 'patient' ? 'block' : 'none';
            });
            document.querySelectorAll('.doctor-fields').forEach(el => {
                el.style.display = type === 'doctor' ? 'block' : 'none';
            });
            document.querySelectorAll('.staff-fields').forEach(el => {
                el.style.display = type === 'staff' ? 'block' : 'none';
            });
            
            // Update required fields
            updateRequiredFields(type);
        }

        function updateRequiredFields(type) {
            // Remove all type-specific required attributes
            document.querySelectorAll('.patient-fields [required], .doctor-fields [required], .staff-fields [required]').forEach(el => {
                el.removeAttribute('required');
            });
            
            // Add required attributes based on type
            if (type === 'patient') {
                document.getElementById('date_of_birth').setAttribute('required', '');
                document.getElementById('gender').setAttribute('required', '');
            }
        }

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return;
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Initialize form based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const type = urlParams.get('type') || 'patient';
            
            // Simulate click on the correct type card
            const typeCard = document.querySelector(`.type-card:nth-child(${type === 'patient' ? 1 : type === 'doctor' ? 2 : 3})`);
            if (typeCard) {
                typeCard.click();
            }
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>