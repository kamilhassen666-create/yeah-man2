<?php
require_once '../includes/functions.php';
require_login(['doctor']);

$doctor_info = get_user_info($_SESSION['user_id'], 'doctor');

// Handle actions
$action = $_GET['action'] ?? '';
$consultation_id = $_GET['view'] ?? '';
$patient_id = $_GET['patient'] ?? '';

$error_message = '';
$success_message = '';

// Handle form submission for new/edit consultation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_consultation'])) {
    $patient_ssn = sanitize_input($_POST['patient_ssn']);
    $consultation_date = sanitize_input($_POST['consultation_date']);
    $symptoms = sanitize_input($_POST['symptoms']);
    $diagnosis = sanitize_input($_POST['diagnosis']);
    $treatment = sanitize_input($_POST['treatment']);
    $notes = sanitize_input($_POST['notes']);
    $follow_up_date = sanitize_input($_POST['follow_up_date']);
    $status = sanitize_input($_POST['status']);
    
    // Validation
    if (empty($patient_ssn) || empty($consultation_date) || empty($symptoms)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Check if patient exists
        $patient_exists = getRow("SELECT ssn FROM patient WHERE ssn = ?", [$patient_ssn]);
        if (!$patient_exists) {
            $error_message = 'Patient with this ID does not exist.';
        } else {
            // Generate reference number
            $reference_number = generate_reference_number('CONS');
            
            // Insert consultation
            $query = "INSERT INTO consultation (patient_ssn, doctor_ssn, consultation_date, symptoms, diagnosis, treatment, notes, follow_up_date, status, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $follow_up_date = !empty($follow_up_date) ? $follow_up_date : null;
            
            $result = executeQuery($query, [
                $patient_ssn, $_SESSION['user_id'], $consultation_date, 
                $symptoms, $diagnosis, $treatment, $notes, 
                $follow_up_date, $status, $reference_number
            ]);
            
            if ($result) {
                log_audit($_SESSION['user_id'], 'Doctor', 'Consultation Created', 'consultation', getLastInsertId());
                redirect_with_message('consultations.php', 'Consultation recorded successfully!', 'success');
            } else {
                $error_message = 'Failed to save consultation. Please try again.';
            }
        }
    }
}

// Get consultation details if viewing
$consultation_details = null;
if (!empty($consultation_id)) {
    $consultation_details = getRow("
        SELECT c.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
               p.date_of_birth, p.gender, p.phone, p.blood_type, p.allergies
        FROM consultation c 
        JOIN patient p ON c.patient_ssn = p.ssn 
        WHERE c.id = ? AND c.doctor_ssn = ?
    ", [$consultation_id, $_SESSION['user_id']]);
}

// Get patient details if pre-selected
$selected_patient = null;
if (!empty($patient_id)) {
    $selected_patient = getRow("SELECT * FROM patient WHERE ssn = ?", [$patient_id]);
}

// Get consultations list
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search_query = $_GET['search'] ?? '';

$where_conditions = ["c.doctor_ssn = ?"];
$params = [$_SESSION['user_id']];

if (!empty($filter_status)) {
    $where_conditions[] = "c.status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_date)) {
    if ($filter_date === 'today') {
        $where_conditions[] = "DATE(c.consultation_date) = CURDATE()";
    } elseif ($filter_date === 'week') {
        $where_conditions[] = "YEARWEEK(c.consultation_date) = YEARWEEK(CURDATE())";
    } elseif ($filter_date === 'month') {
        $where_conditions[] = "YEAR(c.consultation_date) = YEAR(CURDATE()) AND MONTH(c.consultation_date) = MONTH(CURDATE())";
    }
}

if (!empty($search_query)) {
    $where_conditions[] = "(CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR p.ssn LIKE ? OR c.reference_number LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_clause = implode(' AND ', $where_conditions);

$consultations = getRows("
    SELECT c.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
           p.date_of_birth, p.gender, p.phone
    FROM consultation c 
    JOIN patient p ON c.patient_ssn = p.ssn 
    WHERE $where_clause
    ORDER BY c.consultation_date DESC
", $params) ?: [];

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - Doctor Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav doctor-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span>Dr. <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-md"></i>
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
        <aside class="sidebar doctor-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="patients.php">
                        <i class="fas fa-users"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li class="active">
                    <a href="consultations.php">
                        <i class="fas fa-stethoscope"></i>
                        <span>Consultations</span>
                    </a>
                </li>
                <li>
                    <a href="operations.php">
                        <i class="fas fa-procedures"></i>
                        <span>Operations</span>
                    </a>
                </li>
                <li>
                    <a href="diagnoses.php">
                        <i class="fas fa-diagnoses"></i>
                        <span>Diagnoses</span>
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
                        <span>Search Patients</span>
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

            <?php if ($action === 'new' || !empty($consultation_id)): ?>
                <!-- New/Edit Consultation Form -->
                <div class="page-header">
                    <h1>
                        <i class="fas fa-stethoscope"></i> 
                        <?php echo $action === 'new' ? 'New Consultation' : 'View Consultation'; ?>
                    </h1>
                    <div class="header-actions">
                        <a href="consultations.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Consultations
                        </a>
                    </div>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'new'): ?>
                    <!-- New Consultation Form -->
                    <div class="consultation-form-container">
                        <form method="POST" action="" class="consultation-form">
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
                                            <div class="allergies-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Allergies:</strong> <?php echo htmlspecialchars($selected_patient['allergies']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-section">
                                <h3><i class="fas fa-calendar"></i> Consultation Details</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="consultation_date">Consultation Date & Time <span class="required">*</span></label>
                                        <input type="datetime-local" id="consultation_date" name="consultation_date" 
                                               class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="Completed">Completed</option>
                                            <option value="Scheduled">Scheduled</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3><i class="fas fa-notes-medical"></i> Medical Information</h3>
                                
                                <div class="form-group">
                                    <label for="symptoms">Symptoms & Complaints <span class="required">*</span></label>
                                    <textarea id="symptoms" name="symptoms" class="form-control" rows="4" 
                                              placeholder="Describe the patient's symptoms and complaints..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="diagnosis">Initial Diagnosis</label>
                                    <textarea id="diagnosis" name="diagnosis" class="form-control" rows="3" 
                                              placeholder="Initial diagnosis or assessment..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="treatment">Treatment Plan</label>
                                    <textarea id="treatment" name="treatment" class="form-control" rows="4" 
                                              placeholder="Prescribed treatment, medications, or recommendations..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="notes">Additional Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" 
                                              placeholder="Any additional observations or notes..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="follow_up_date">Follow-up Date</label>
                                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control">
                                    <small class="form-help">Leave empty if no follow-up is needed</small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_consultation" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Consultation
                                </button>
                                <a href="consultations.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($consultation_details): ?>
                    <!-- View Consultation Details -->
                    <div class="consultation-view-container">
                        <div class="consultation-header">
                            <div class="consultation-info">
                                <h2>Consultation Details</h2>
                                <p class="consultation-ref">Reference: <?php echo htmlspecialchars($consultation_details['reference_number']); ?></p>
                            </div>
                            <div class="consultation-status">
                                <span class="status-badge <?php echo strtolower($consultation_details['status']); ?>">
                                    <?php echo $consultation_details['status']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="consultation-details-grid">
                            <!-- Patient Information -->
                            <div class="detail-card patient-card">
                                <h3><i class="fas fa-user"></i> Patient Information</h3>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="patient-details">
                                        <h4><?php echo htmlspecialchars($consultation_details['patient_name']); ?></h4>
                                        <p>ID: <?php echo htmlspecialchars($consultation_details['patient_ssn']); ?></p>
                                        <div class="patient-meta">
                                            <span>Age: <?php echo calculate_age($consultation_details['date_of_birth']); ?> years</span>
                                            <span>Gender: <?php echo htmlspecialchars($consultation_details['gender']); ?></span>
                                            <?php if ($consultation_details['blood_type']): ?>
                                                <span>Blood Type: <?php echo htmlspecialchars($consultation_details['blood_type']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($consultation_details['phone']): ?>
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($consultation_details['phone']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($consultation_details['allergies']): ?>
                                    <div class="allergies-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Allergies:</strong> <?php echo htmlspecialchars($consultation_details['allergies']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Consultation Information -->
                            <div class="detail-card consultation-card">
                                <h3><i class="fas fa-calendar"></i> Consultation Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Date & Time:</label>
                                        <span><?php echo format_datetime($consultation_details['consultation_date']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Status:</label>
                                        <span class="status-badge <?php echo strtolower($consultation_details['status']); ?>">
                                            <?php echo $consultation_details['status']; ?>
                                        </span>
                                    </div>
                                    <?php if ($consultation_details['follow_up_date']): ?>
                                        <div class="info-item">
                                            <label>Follow-up Date:</label>
                                            <span><?php echo format_date($consultation_details['follow_up_date']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Medical Details -->
                            <div class="detail-card medical-card full-width">
                                <h3><i class="fas fa-notes-medical"></i> Medical Details</h3>
                                
                                <div class="medical-section">
                                    <h4>Symptoms & Complaints</h4>
                                    <p><?php echo nl2br(htmlspecialchars($consultation_details['symptoms'])); ?></p>
                                </div>

                                <?php if ($consultation_details['diagnosis']): ?>
                                    <div class="medical-section">
                                        <h4>Diagnosis</h4>
                                        <p><?php echo nl2br(htmlspecialchars($consultation_details['diagnosis'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($consultation_details['treatment']): ?>
                                    <div class="medical-section">
                                        <h4>Treatment Plan</h4>
                                        <p><?php echo nl2br(htmlspecialchars($consultation_details['treatment'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($consultation_details['notes']): ?>
                                    <div class="medical-section">
                                        <h4>Additional Notes</h4>
                                        <p><?php echo nl2br(htmlspecialchars($consultation_details['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="consultation-actions">
                            <button class="btn btn-primary" onclick="location.href='diagnoses.php?action=new&patient=<?php echo $consultation_details['patient_ssn']; ?>'">
                                <i class="fas fa-diagnoses"></i> Add Diagnosis
                            </button>
                            <button class="btn btn-secondary" onclick="location.href='consultations.php?action=new&patient=<?php echo $consultation_details['patient_ssn']; ?>'">
                                <i class="fas fa-plus"></i> New Consultation
                            </button>
                            <button class="btn btn-info" onclick="location.href='patients.php?view=<?php echo $consultation_details['patient_ssn']; ?>'">
                                <i class="fas fa-user"></i> View Patient
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Consultations List -->
                <div class="page-header">
                    <h1><i class="fas fa-stethoscope"></i> Consultations</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="location.href='consultations.php?action=new'">
                            <i class="fas fa-plus"></i> New Consultation
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="consultations-controls">
                    <form method="GET" action="" class="filters-form">
                        <div class="search-section">
                            <input type="text" name="search" class="search-input" 
                                   placeholder="Search by patient name, ID, or reference..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <div class="filters-section">
                            <select name="status" class="filter-select">
                                <option value="">All Status</option>
                                <option value="Completed" <?php echo $filter_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Scheduled" <?php echo $filter_status === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="In Progress" <?php echo $filter_status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Cancelled" <?php echo $filter_status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            
                            <select name="date" class="filter-select">
                                <option value="">All Dates</option>
                                <option value="today" <?php echo $filter_date === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo $filter_date === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo $filter_date === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                            
                            <button type="submit" class="btn btn-secondary">Apply Filters</button>
                            <a href="consultations.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Consultations List -->
                <div class="consultations-results">
                    <div class="results-header">
                        <h3>Consultations (<?php echo count($consultations); ?> found)</h3>
                    </div>
                    
                    <?php if (!empty($consultations)): ?>
                        <div class="consultations-list">
                            <?php foreach ($consultations as $consultation): ?>
                                <div class="consultation-item" onclick="viewConsultation('<?php echo $consultation['id']; ?>')">
                                    <div class="consultation-time">
                                        <div class="time-info">
                                            <span class="time"><?php echo date('H:i', strtotime($consultation['consultation_date'])); ?></span>
                                            <span class="date"><?php echo format_date($consultation['consultation_date']); ?></span>
                                        </div>
                                        <span class="status-badge <?php echo strtolower($consultation['status']); ?>">
                                            <?php echo $consultation['status']; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="consultation-patient">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="patient-info">
                                            <h4><?php echo htmlspecialchars($consultation['patient_name']); ?></h4>
                                            <p>ID: <?php echo htmlspecialchars($consultation['patient_ssn']); ?></p>
                                            <span class="patient-meta">
                                                <?php echo calculate_age($consultation['date_of_birth']); ?> years, 
                                                <?php echo htmlspecialchars($consultation['gender']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="consultation-details">
                                        <div class="symptoms">
                                            <strong>Symptoms:</strong>
                                            <p><?php echo htmlspecialchars(substr($consultation['symptoms'], 0, 100) . (strlen($consultation['symptoms']) > 100 ? '...' : '')); ?></p>
                                        </div>
                                        <?php if ($consultation['diagnosis']): ?>
                                            <div class="diagnosis">
                                                <strong>Diagnosis:</strong>
                                                <p><?php echo htmlspecialchars(substr($consultation['diagnosis'], 0, 100) . (strlen($consultation['diagnosis']) > 100 ? '...' : '')); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="consultation-actions">
                                        <span class="reference-number"><?php echo htmlspecialchars($consultation['reference_number']); ?></span>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" onclick="event.stopPropagation(); viewConsultation('<?php echo $consultation['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); newConsultation('<?php echo $consultation['patient_ssn']; ?>')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-consultations">
                            <i class="fas fa-stethoscope"></i>
                            <h4>No Consultations Found</h4>
                            <p>No consultations match your current filters.</p>
                            <button class="btn btn-primary" onclick="location.href='consultations.php?action=new'">
                                <i class="fas fa-plus"></i> Record First Consultation
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .doctor-nav {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .doctor-sidebar {
            border-right: 3px solid #10b981;
        }

        .doctor-sidebar .sidebar-menu li.active a,
        .doctor-sidebar .sidebar-menu li:hover a {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .consultation-form-container {
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

        .allergies-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 0.75rem;
            border-radius: 6px;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .consultation-view-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .consultation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .consultation-header h2 {
            color: #1e293b;
            margin: 0;
        }

        .consultation-ref {
            color: #6b7280;
            margin: 0;
            font-family: 'Courier New', monospace;
        }

        .consultation-details-grid {
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

        .detail-card.full-width {
            grid-column: 1 / -1;
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

        .medical-section {
            margin-bottom: 1.5rem;
        }

        .medical-section h4 {
            color: #1e293b;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .medical-section p {
            color: #374151;
            line-height: 1.6;
        }

        .consultation-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .consultations-controls {
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
            border-color: #10b981;
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

        .consultations-results {
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

        .consultations-list {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .consultation-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .consultation-item:hover {
            border-color: #10b981;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
        }

        .consultation-time {
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

        .consultation-patient {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 200px;
        }

        .consultation-patient h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .consultation-patient p {
            color: #6b7280;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .consultation-details {
            flex: 1;
            color: #374151;
        }

        .consultation-details strong {
            color: #1e293b;
        }

        .consultation-details p {
            margin: 0.25rem 0;
            line-height: 1.4;
        }

        .consultation-actions {
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

        .status-badge.completed { background: #dcfce7; color: #166534; }
        .status-badge.scheduled { background: #dbeafe; color: #1d4ed8; }
        .status-badge.in.progress { background: #fef3c7; color: #d97706; }
        .status-badge.cancelled { background: #fef2f2; color: #dc2626; }

        .no-consultations {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .no-consultations i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-consultations h4 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        @media (max-width: 768px) {
            .consultation-details-grid {
                grid-template-columns: 1fr;
            }

            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }

            .consultation-item {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .consultation-actions {
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

        function viewConsultation(consultationId) {
            window.location.href = `consultations.php?view=${consultationId}`;
        }

        function newConsultation(patientId) {
            window.location.href = `consultations.php?action=new&patient=${patientId}`;
        }

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>