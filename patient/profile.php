<?php
require_once '../includes/functions.php';
require_login(['patient']);

$patient_info = get_user_info($_SESSION['user_id'], 'patient');
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $emergency_contact = sanitize_input($_POST['emergency_contact']);
    $emergency_phone = sanitize_input($_POST['emergency_phone']);
    $allergies = sanitize_input($_POST['allergies']);
    
    // Validation
    $validation_result = validate_required_fields([
        'first_name' => $first_name,
        'last_name' => $last_name
    ]);
    
    if ($validation_result !== true) {
        $error_message = $validation_result;
    } elseif (!empty($email) && !validate_email($email)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!empty($phone) && !validate_phone($phone)) {
        $error_message = 'Please enter a valid phone number.';
    } else {
        // Update patient information
        $query = "UPDATE patient SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, emergency_contact = ?, emergency_phone = ?, allergies = ?, updated_at = NOW() WHERE ssn = ?";
        $result = executeQuery($query, [$first_name, $last_name, $email, $phone, $address, $emergency_contact, $emergency_phone, $allergies, $_SESSION['user_id']]);
        
        if ($result) {
            // Log the update
            log_audit($_SESSION['user_id'], 'Patient', 'Profile Update', 'patient', $_SESSION['user_id']);
            
            // Update session name if changed
            if ($first_name . ' ' . $last_name !== $_SESSION['full_name']) {
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;
            }
            
            $success_message = 'Profile updated successfully!';
            
            // Refresh patient info
            $patient_info = get_user_info($_SESSION['user_id'], 'patient');
        } else {
            $error_message = 'Failed to update profile. Please try again.';
        }
    }
}

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Patient Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="search.php">
                        <i class="fas fa-search"></i>
                        <span>Search Records</span>
                    </a>
                </li>
                <li>
                    <a href="payments.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="appointments.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
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
                <h1><i class="fas fa-user"></i> My Profile</h1>
                <p>Manage your personal information and contact details</p>
            </div>

            <div class="profile-container">
                <!-- Profile Summary Card -->
                <div class="profile-summary">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']); ?></h2>
                        <p class="patient-id">Patient ID: <?php echo htmlspecialchars($patient_info['ssn']); ?></p>
                        <p class="member-since">Member since: <?php echo format_date($patient_info['created_at']); ?></p>
                        <div class="health-info">
                            <span class="health-item">
                                <i class="fas fa-birthday-cake"></i>
                                Age: <?php echo calculate_age($patient_info['date_of_birth']); ?> years
                            </span>
                            <span class="health-item">
                                <i class="fas fa-venus-mars"></i>
                                <?php echo htmlspecialchars($patient_info['gender']); ?>
                            </span>
                            <?php if (!empty($patient_info['blood_type'])): ?>
                            <span class="health-item">
                                <i class="fas fa-tint"></i>
                                Blood Type: <?php echo htmlspecialchars($patient_info['blood_type']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="profile-form-container">
                    <h3><i class="fas fa-edit"></i> Edit Profile Information</h3>
                    
                    <form method="POST" action="" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required
                                       value="<?php echo htmlspecialchars($patient_info['first_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required
                                       value="<?php echo htmlspecialchars($patient_info['last_name']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="<?php echo htmlspecialchars($patient_info['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       value="<?php echo htmlspecialchars($patient_info['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($patient_info['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="emergency_contact">Emergency Contact Name</label>
                                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control"
                                       value="<?php echo htmlspecialchars($patient_info['emergency_contact'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="emergency_phone">Emergency Contact Phone</label>
                                <input type="tel" id="emergency_phone" name="emergency_phone" class="form-control"
                                       value="<?php echo htmlspecialchars($patient_info['emergency_phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="allergies">Known Allergies</label>
                            <textarea id="allergies" name="allergies" class="form-control" rows="3" 
                                      placeholder="List any known allergies or medical conditions..."><?php echo htmlspecialchars($patient_info['allergies'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Read-only Information -->
                <div class="readonly-info">
                    <h3><i class="fas fa-info-circle"></i> Medical Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Patient ID:</label>
                            <span><?php echo htmlspecialchars($patient_info['ssn']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth:</label>
                            <span><?php echo format_date($patient_info['date_of_birth']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Gender:</label>
                            <span><?php echo htmlspecialchars($patient_info['gender']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Blood Type:</label>
                            <span><?php echo htmlspecialchars($patient_info['blood_type'] ?: 'Not specified'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Registration Date:</label>
                            <span><?php echo format_date($patient_info['created_at']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Last Updated:</label>
                            <span><?php echo format_datetime($patient_info['updated_at']); ?></span>
                        </div>
                    </div>
                    <p class="note">
                        <i class="fas fa-exclamation-triangle"></i>
                        To update medical information like blood type or date of birth, please contact the hospital administration.
                    </p>
                </div>
            </div>
        </main>
    </div>

    <style>
        .profile-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .profile-summary {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            text-align: center;
        }

        .profile-avatar i {
            font-size: 6rem;
            color: #6b7280;
        }

        .profile-info h2 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .patient-id {
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .member-since {
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .health-info {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .health-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
        }

        .health-item i {
            color: #2563eb;
        }

        .profile-form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-form-container h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .readonly-info {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .readonly-info h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-item label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .info-item span {
            color: #1e293b;
        }

        .note {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .note i {
            color: #d97706;
        }

        @media (max-width: 768px) {
            .profile-summary {
                flex-direction: column;
                text-align: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .health-info {
                justify-content: center;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>

    <script src="../assets/js/script.js"></script>
</body>
</html>