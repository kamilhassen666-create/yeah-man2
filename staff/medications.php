<?php
require_once '../includes/functions.php';
require_login(['staff']);

$staff_info = get_user_info($_SESSION['user_id'], 'staff');

// Handle actions
$action = $_GET['action'] ?? '';
$medication_id = $_GET['view'] ?? '';
$patient_id = $_GET['patient'] ?? '';

$error_message = '';
$success_message = '';

// Handle form submission for new medication
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_medication'])) {
    $patient_ssn = sanitize_input($_POST['patient_ssn']);
    $medication_name = sanitize_input($_POST['medication_name']);
    $dosage = sanitize_input($_POST['dosage']);
    $frequency = sanitize_input($_POST['frequency']);
    $duration = sanitize_input($_POST['duration']);
    $administration_date = sanitize_input($_POST['administration_date']);
    $notes = sanitize_input($_POST['notes']);
    $allergies_checked = isset($_POST['allergies_checked']) ? 1 : 0;
    $status = sanitize_input($_POST['status']);
    
    // Validation
    if (empty($patient_ssn) || empty($medication_name) || empty($dosage) || empty($administration_date)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Check if patient exists and get allergies
        $patient = getRow("SELECT ssn, allergies FROM patient WHERE ssn = ?", [$patient_ssn]);
        if (!$patient) {
            $error_message = 'Patient with this ID does not exist.';
        } else {
            // Generate reference number
            $reference_number = generate_reference_number('MED');
            
            // Insert medication administration
            $query = "INSERT INTO medical_administration (patient_ssn, staff_ssn, medication_name, dosage, frequency, duration, administration_date, notes, allergies_checked, status, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $result = executeQuery($query, [
                $patient_ssn, $_SESSION['user_id'], $medication_name, 
                $dosage, $frequency, $duration, $administration_date, 
                $notes, $allergies_checked, $status, $reference_number
            ]);
            
            if ($result) {
                log_audit($_SESSION['user_id'], 'Staff', 'Medication Recorded', 'medical_administration', getLastInsertId());
                redirect_with_message('medications.php', 'Medication recorded successfully!', 'success');
            } else {
                $error_message = 'Failed to save medication. Please try again.';
            }
        }
    }
}

// Handle medication administration
if ($action === 'administer' && !empty($_GET['id'])) {
    $med_id = $_GET['id'];
    $result = executeQuery("UPDATE medical_administration SET status = 'Administered', actual_administration_date = NOW() WHERE id = ? AND staff_ssn = ?", [$med_id, $_SESSION['user_id']]);
    
    if ($result) {
        log_audit($_SESSION['user_id'], 'Staff', 'Medication Administered', 'medical_administration', $med_id);
        redirect_with_message('medications.php', 'Medication marked as administered!', 'success');
    } else {
        redirect_with_message('medications.php', 'Failed to update medication status.', 'error');
    }
}

// Get medication details if viewing
$medication_details = null;
if (!empty($medication_id)) {
    $medication_details = getRow("
        SELECT m.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
               p.date_of_birth, p.gender, p.phone, p.blood_type, p.allergies
        FROM medical_administration m 
        JOIN patient p ON m.patient_ssn = p.ssn 
        WHERE m.id = ? AND m.staff_ssn = ?
    ", [$medication_id, $_SESSION['user_id']]);
}

// Get patient details if pre-selected
$selected_patient = null;
if (!empty($patient_id)) {
    $selected_patient = getRow("SELECT * FROM patient WHERE ssn = ?", [$patient_id]);
}

// Get medications list
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search_query = $_GET['search'] ?? '';

$where_conditions = ["m.staff_ssn = ?"];
$params = [$_SESSION['user_id']];

if (!empty($filter_status)) {
    $where_conditions[] = "m.status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_date)) {
    if ($filter_date === 'today') {
        $where_conditions[] = "DATE(m.administration_date) = CURDATE()";
    } elseif ($filter_date === 'week') {
        $where_conditions[] = "YEARWEEK(m.administration_date) = YEARWEEK(CURDATE())";
    } elseif ($filter_date === 'month') {
        $where_conditions[] = "YEAR(m.administration_date) = YEAR(CURDATE()) AND MONTH(m.administration_date) = MONTH(CURDATE())";
    }
}

if (!empty($search_query)) {
    $where_conditions[] = "(CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR p.ssn LIKE ? OR m.medication_name LIKE ? OR m.reference_number LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_clause = implode(' AND ', $where_conditions);

$medications = getRows("
    SELECT m.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.date_of_birth, p.gender, p.phone, p.allergies
    FROM medical_administration m 
    JOIN patient p ON m.patient_ssn = p.ssn 
    WHERE $where_clause
    ORDER BY m.administration_date DESC
", $params) ?: [];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - Staff Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav staff-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-nurse"></i>
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
        <aside class="sidebar staff-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="medications.php">
                        <i class="fas fa-pills"></i>
                        <span>Medications</span>
                    </a>
                </li>
                <li>
                    <a href="patients.php">
                        <i class="fas fa-users"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li>
                    <a href="vitals.php">
                        <i class="fas fa-heartbeat"></i>
                        <span>Vital Signs</span>
                    </a>
                </li>
                <li>
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="schedule.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
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

            <?php if ($action === 'new' || !empty($medication_id)): ?>
                <!-- New/View Medication Form -->
                <div class="page-header">
                    <h1>
                        <i class="fas fa-pills"></i> 
                        <?php echo $action === 'new' ? 'Record Medication' : 'Medication Details'; ?>
                    </h1>
                    <div class="header-actions">
                        <a href="medications.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Medications
                        </a>
                    </div>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'new'): ?>
                    <!-- New Medication Form -->
                    <div class="medication-form-container">
                        <form method="POST" action="" class="medication-form" id="medicationForm">
                            <div class="form-section">
                                <h3><i class="fas fa-user"></i> Patient Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="patient_ssn">Patient ID <span class="required">*</span></label>
                                        <div class="patient-search-group">
                                            <input type="text" id="patient_ssn" name="patient_ssn" class="form-control" 
                                                   value="<?php echo htmlspecialchars($selected_patient['ssn'] ?? ''); ?>" 
                                                   placeholder="Enter Patient ID" required>
                                            <button type="button" class="btn btn-secondary" onclick="searchPatient()">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                        </div>
                                        <div id="patient_info" class="patient-info-display" style="display: none;">
                                            <!-- Patient info will be displayed here -->
                                        </div>
                                    </div>
                                </div>

                                <?php if ($selected_patient): ?>
                                    <div class="selected-patient-info">
                                        <h4><?php echo htmlspecialchars($selected_patient['first_name'] . ' ' . $selected_patient['last_name']); ?></h4>
                                        <div class="patient-details">
                                            <span>Age: <?php echo calculate_age($selected_patient['date_of_birth']); ?> years</span>
                                            <span>Gender: <?php echo htmlspecialchars($selected_patient['gender']); ?></span>
                                            <?php if ($selected_patient['blood_type']): ?>
                                                <span>Blood Type: <?php echo htmlspecialchars($selected_patient['blood_type']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($selected_patient['allergies']): ?>
                                            <div class="allergies-alert">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>ALLERGIES ALERT:</strong> <?php echo htmlspecialchars($selected_patient['allergies']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-section">
                                <h3><i class="fas fa-pills"></i> Medication Details</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="medication_name">Medication Name <span class="required">*</span></label>
                                        <input type="text" id="medication_name" name="medication_name" class="form-control" 
                                               placeholder="Enter medication name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dosage">Dosage <span class="required">*</span></label>
                                        <input type="text" id="dosage" name="dosage" class="form-control" 
                                               placeholder="e.g., 500mg, 2 tablets" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="frequency">Frequency</label>
                                        <select id="frequency" name="frequency" class="form-control">
                                            <option value="">Select frequency</option>
                                            <option value="Once daily">Once daily</option>
                                            <option value="Twice daily">Twice daily</option>
                                            <option value="Three times daily">Three times daily</option>
                                            <option value="Four times daily">Four times daily</option>
                                            <option value="Every 4 hours">Every 4 hours</option>
                                            <option value="Every 6 hours">Every 6 hours</option>
                                            <option value="Every 8 hours">Every 8 hours</option>
                                            <option value="Every 12 hours">Every 12 hours</option>
                                            <option value="As needed">As needed (PRN)</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="duration">Duration</label>
                                        <input type="text" id="duration" name="duration" class="form-control" 
                                               placeholder="e.g., 7 days, 2 weeks">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="administration_date">Administration Date & Time <span class="required">*</span></label>
                                    <input type="datetime-local" id="administration_date" name="administration_date" 
                                           class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3><i class="fas fa-shield-alt"></i> Safety & Administration</h3>
                                
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Administered">Administered</option>
                                        <option value="Cancelled">Cancelled</option>
                                        <option value="Delayed">Delayed</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="allergies_checked" name="allergies_checked" value="1">
                                            <span class="checkmark"></span>
                                            <strong>I have verified patient allergies and confirm this medication is safe</strong>
                                        </label>
                                    </div>
                                    <small class="form-help safety-note">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        This checkbox is required before administering any medication
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="notes">Administration Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="4" 
                                              placeholder="Any special instructions, observations, or notes about the medication administration..."></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_medication" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Record Medication
                                </button>
                                <a href="medications.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($medication_details): ?>
                    <!-- View Medication Details -->
                    <div class="medication-view-container">
                        <div class="medication-header">
                            <div class="medication-info">
                                <h2><?php echo htmlspecialchars($medication_details['medication_name']); ?></h2>
                                <p class="medication-ref">Reference: <?php echo htmlspecialchars($medication_details['reference_number']); ?></p>
                            </div>
                            <div class="medication-status">
                                <span class="status-badge <?php echo strtolower($medication_details['status']); ?>">
                                    <?php echo $medication_details['status']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="medication-details-grid">
                            <!-- Patient Information -->
                            <div class="detail-card patient-card">
                                <h3><i class="fas fa-user"></i> Patient Information</h3>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="patient-details">
                                        <h4><?php echo htmlspecialchars($medication_details['patient_name']); ?></h4>
                                        <p>ID: <?php echo htmlspecialchars($medication_details['patient_ssn']); ?></p>
                                        <div class="patient-meta">
                                            <span>Age: <?php echo calculate_age($medication_details['date_of_birth']); ?> years</span>
                                            <span>Gender: <?php echo htmlspecialchars($medication_details['gender']); ?></span>
                                            <?php if ($medication_details['blood_type']): ?>
                                                <span>Blood Type: <?php echo htmlspecialchars($medication_details['blood_type']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($medication_details['phone']): ?>
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($medication_details['phone']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($medication_details['allergies']): ?>
                                    <div class="allergies-alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>ALLERGIES:</strong> <?php echo htmlspecialchars($medication_details['allergies']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Medication Information -->
                            <div class="detail-card medication-card">
                                <h3><i class="fas fa-pills"></i> Medication Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Medication:</label>
                                        <span><?php echo htmlspecialchars($medication_details['medication_name']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Dosage:</label>
                                        <span><?php echo htmlspecialchars($medication_details['dosage']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Frequency:</label>
                                        <span><?php echo htmlspecialchars($medication_details['frequency'] ?: 'Not specified'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Duration:</label>
                                        <span><?php echo htmlspecialchars($medication_details['duration'] ?: 'Not specified'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Scheduled Time:</label>
                                        <span><?php echo format_datetime($medication_details['administration_date']); ?></span>
                                    </div>
                                    <?php if ($medication_details['actual_administration_date']): ?>
                                        <div class="info-item">
                                            <label>Actual Administration:</label>
                                            <span><?php echo format_datetime($medication_details['actual_administration_date']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Safety Information -->
                            <div class="detail-card safety-card">
                                <h3><i class="fas fa-shield-alt"></i> Safety Information</h3>
                                <div class="safety-checks">
                                    <div class="safety-item">
                                        <div class="check-status <?php echo $medication_details['allergies_checked'] ? 'checked' : 'unchecked'; ?>">
                                            <i class="fas fa-<?php echo $medication_details['allergies_checked'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                        </div>
                                        <div class="check-info">
                                            <strong>Allergy Check</strong>
                                            <p><?php echo $medication_details['allergies_checked'] ? 'Allergies verified before administration' : 'Allergies not checked'; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($medication_details['notes']): ?>
                                    <div class="notes-section">
                                        <h4>Administration Notes</h4>
                                        <p><?php echo nl2br(htmlspecialchars($medication_details['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="medication-actions">
                            <?php if ($medication_details['status'] === 'Scheduled'): ?>
                                <button class="btn btn-success" onclick="administerMedication('<?php echo $medication_details['id']; ?>')">
                                    <i class="fas fa-check"></i> Mark as Administered
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-primary" onclick="location.href='medications.php?action=new&patient=<?php echo $medication_details['patient_ssn']; ?>'">
                                <i class="fas fa-plus"></i> New Medication
                            </button>
                            <button class="btn btn-info" onclick="location.href='patients.php?view=<?php echo $medication_details['patient_ssn']; ?>'">
                                <i class="fas fa-user"></i> View Patient
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Medications List -->
                <div class="page-header">
                    <h1><i class="fas fa-pills"></i> Medication Administration</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="location.href='medications.php?action=new'">
                            <i class="fas fa-plus"></i> Record Medication
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="medications-controls">
                    <form method="GET" action="" class="filters-form">
                        <div class="search-section">
                            <input type="text" name="search" class="search-input" 
                                   placeholder="Search by patient name, ID, medication, or reference..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <div class="filters-section">
                            <select name="status" class="filter-select">
                                <option value="">All Status</option>
                                <option value="Scheduled" <?php echo $filter_status === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="Administered" <?php echo $filter_status === 'Administered' ? 'selected' : ''; ?>>Administered</option>
                                <option value="Cancelled" <?php echo $filter_status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Delayed" <?php echo $filter_status === 'Delayed' ? 'selected' : ''; ?>>Delayed</option>
                            </select>
                            
                            <select name="date" class="filter-select">
                                <option value="">All Dates</option>
                                <option value="today" <?php echo $filter_date === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo $filter_date === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo $filter_date === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                            
                            <button type="submit" class="btn btn-secondary">Apply Filters</button>
                            <a href="medications.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Medications List -->
                <div class="medications-results">
                    <div class="results-header">
                        <h3>Medication Records (<?php echo count($medications); ?> found)</h3>
                    </div>
                    
                    <?php if (!empty($medications)): ?>
                        <div class="medications-list">
                            <?php foreach ($medications as $medication): ?>
                                <div class="medication-item" onclick="viewMedication('<?php echo $medication['id']; ?>')">
                                    <div class="medication-time">
                                        <div class="time-info">
                                            <span class="time"><?php echo date('H:i', strtotime($medication['administration_date'])); ?></span>
                                            <span class="date"><?php echo format_date($medication['administration_date']); ?></span>
                                        </div>
                                        <span class="status-badge <?php echo strtolower($medication['status']); ?>">
                                            <?php echo $medication['status']; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="medication-patient">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="patient-info">
                                            <h4><?php echo htmlspecialchars($medication['patient_name']); ?></h4>
                                            <p>ID: <?php echo htmlspecialchars($medication['patient_ssn']); ?></p>
                                            <span class="patient-meta">
                                                <?php echo calculate_age($medication['date_of_birth']); ?> years, 
                                                <?php echo htmlspecialchars($medication['gender']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="medication-details">
                                        <div class="medication-name">
                                            <strong><?php echo htmlspecialchars($medication['medication_name']); ?></strong>
                                        </div>
                                        <div class="dosage-info">
                                            <span class="dosage"><?php echo htmlspecialchars($medication['dosage']); ?></span>
                                            <?php if ($medication['frequency']): ?>
                                                <span class="frequency"> • <?php echo htmlspecialchars($medication['frequency']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($medication['allergies']): ?>
                                            <div class="allergy-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Patient has allergies</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="medication-safety">
                                        <div class="safety-check <?php echo $medication['allergies_checked'] ? 'checked' : 'unchecked'; ?>">
                                            <i class="fas fa-<?php echo $medication['allergies_checked'] ? 'shield-alt' : 'exclamation-triangle'; ?>"></i>
                                            <span><?php echo $medication['allergies_checked'] ? 'Allergies Checked' : 'Check Required'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="medication-actions">
                                        <span class="reference-number"><?php echo htmlspecialchars($medication['reference_number']); ?></span>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" onclick="event.stopPropagation(); viewMedication('<?php echo $medication['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($medication['status'] === 'Scheduled'): ?>
                                                <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); administerMedication('<?php echo $medication['id']; ?>')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); newMedication('<?php echo $medication['patient_ssn']; ?>')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-medications">
                            <i class="fas fa-pills"></i>
                            <h4>No Medications Found</h4>
                            <p>No medication records match your current filters.</p>
                            <button class="btn btn-primary" onclick="location.href='medications.php?action=new'">
                                <i class="fas fa-plus"></i> Record First Medication
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .staff-nav {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .staff-sidebar {
            border-right: 3px solid #f59e0b;
        }

        .staff-sidebar .sidebar-menu li.active a,
        .staff-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .medication-form-container {
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

        .patient-search-group {
            display: flex;
            gap: 1rem;
        }

        .patient-search-group input {
            flex: 1;
        }

        .selected-patient-info {
            background: #f0fdf4;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .selected-patient-info h4 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .patient-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }

        .allergies-alert {
            background: #fef2f2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 6px;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #fecaca;
        }

        .checkbox-group {
            margin-bottom: 0.5rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            font-size: 1rem;
        }

        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #f59e0b;
        }

        .safety-note {
            color: #dc2626;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .medication-view-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .medication-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .medication-header h2 {
            color: #1e293b;
            margin: 0;
        }

        .medication-ref {
            color: #6b7280;
            margin: 0;
            font-family: 'Courier New', monospace;
        }

        .medication-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .detail-card h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .patient-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .patient-details h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .patient-meta {
            display: flex;
            gap: 1rem;
            color: #6b7280;
            font-size: 0.875rem;
            flex-wrap: wrap;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
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

        .safety-checks {
            margin-bottom: 1.5rem;
        }

        .safety-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .check-status {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .check-status.checked {
            background: #dcfce7;
            color: #059669;
        }

        .check-status.unchecked {
            background: #fef2f2;
            color: #dc2626;
        }

        .notes-section {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .notes-section h4 {
            color: #1e293b;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .notes-section p {
            color: #374151;
            line-height: 1.6;
        }

        .medication-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .medications-controls {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filters-form {
            display: flex;
            gap: 1.5rem;
            align-items: end;
        }

        .search-section {
            flex: 1;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #f59e0b;
        }

        .filters-section {
            display: flex;
            gap: 1rem;
        }

        .filter-select {
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }

        .medications-results {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .results-header {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .results-header h3 {
            margin: 0;
            color: #1e293b;
        }

        .medications-list {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .medication-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .medication-item:hover {
            border-color: #f59e0b;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.1);
        }

        .medication-time {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
        }

        .time-info {
            text-align: center;
        }

        .time {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: block;
        }

        .date {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .medication-patient {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 200px;
        }

        .medication-patient h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .medication-patient p {
            color: #6b7280;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .medication-details {
            flex: 1;
            color: #374151;
        }

        .medication-name {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .dosage-info {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .allergy-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
            width: fit-content;
        }

        .medication-safety {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .safety-check {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            text-align: center;
        }

        .safety-check.checked {
            background: #dcfce7;
            color: #059669;
        }

        .safety-check.unchecked {
            background: #fef2f2;
            color: #dc2626;
        }

        .medication-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .reference-number {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.scheduled { background: #dbeafe; color: #1d4ed8; }
        .status-badge.administered { background: #dcfce7; color: #166534; }
        .status-badge.cancelled { background: #fef2f2; color: #dc2626; }
        .status-badge.delayed { background: #fef3c7; color: #d97706; }

        .no-medications {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .no-medications i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-medications h4 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        @media (max-width: 768px) {
            .medication-details-grid {
                grid-template-columns: 1fr;
            }

            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }

            .medication-item {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .medication-actions {
                flex-direction: column;
            }

            .patient-search-group {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function searchPatient() {
            const patientId = document.getElementById('patient_ssn').value;
            if (patientId) {
                // In a real application, this would make an AJAX call to search for the patient
                alert('Patient search functionality would be implemented here.');
            }
        }

        function viewMedication(medicationId) {
            window.location.href = `medications.php?view=${medicationId}`;
        }

        function newMedication(patientId) {
            window.location.href = `medications.php?action=new&patient=${patientId}`;
        }

        function administerMedication(medicationId) {
            if (confirm('Mark this medication as administered?\n\nThis action will record the current time as the administration time.')) {
                window.location.href = `medications.php?action=administer&id=${medicationId}`;
            }
        }

        // Form validation
        document.getElementById('medicationForm')?.addEventListener('submit', function(e) {
            const allergiesChecked = document.getElementById('allergies_checked').checked;
            const status = document.getElementById('status').value;
            
            if (status === 'Administered' && !allergiesChecked) {
                e.preventDefault();
                alert('You must verify patient allergies before marking medication as administered.');
                return;
            }
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Patient search functionality (placeholder)
        document.getElementById('patient_ssn')?.addEventListener('blur', function() {
            const patientId = this.value;
            if (patientId && patientId.length >= 3) {
                // In a real application, this would make an AJAX call to get patient info
                // For now, we'll just show a placeholder
                console.log('Would search for patient:', patientId);
            }
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>