<?php
require_once '../includes/functions.php';
require_login(['doctor']);

$doctor_info = get_user_info($_SESSION['user_id'], 'doctor');

// Handle actions
$action = $_GET['action'] ?? '';
$patient_id = $_GET['view'] ?? '';

// Search and filtering
$search_query = $_GET['search'] ?? '';
$age_range = $_GET['age_range'] ?? '';
$gender = $_GET['gender'] ?? '';
$blood_type = $_GET['blood_type'] ?? '';
$sort_by = $_GET['sort'] ?? 'last_visit';

// Build WHERE clause for filtering
$where_conditions = ["c.doctor_ssn = ?"];
$params = [$_SESSION['user_id']];

if (!empty($search_query)) {
    $where_conditions[] = "(CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR p.ssn LIKE ? OR p.phone LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if (!empty($gender)) {
    $where_conditions[] = "p.gender = ?";
    $params[] = $gender;
}

if (!empty($blood_type)) {
    $where_conditions[] = "p.blood_type = ?";
    $params[] = $blood_type;
}

$where_clause = implode(' AND ', $where_conditions);

// Get doctor's patients with last visit information
$patients_query = "
    SELECT DISTINCT p.*, 
           CONCAT(p.first_name, ' ', p.last_name) as full_name,
           MAX(c.consultation_date) as last_visit,
           COUNT(c.id) as total_consultations,
           (SELECT COUNT(*) FROM diagnosis d WHERE d.patient_ssn = p.ssn AND d.doctor_ssn = ?) as total_diagnoses,
           (SELECT COUNT(*) FROM operation o WHERE o.patient_ssn = p.ssn AND o.doctor_ssn = ?) as total_operations
    FROM patient p 
    JOIN consultation c ON p.ssn = c.patient_ssn 
    WHERE $where_clause
    GROUP BY p.ssn
";

// Add age range filtering if specified
if (!empty($age_range)) {
    switch ($age_range) {
        case 'child':
            $patients_query .= " HAVING TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) < 18";
            break;
        case 'adult':
            $patients_query .= " HAVING TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) BETWEEN 18 AND 64";
            break;
        case 'senior':
            $patients_query .= " HAVING TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) >= 65";
            break;
    }
}

// Add sorting
switch ($sort_by) {
    case 'name':
        $patients_query .= " ORDER BY p.first_name, p.last_name";
        break;
    case 'age':
        $patients_query .= " ORDER BY p.date_of_birth DESC";
        break;
    case 'consultations':
        $patients_query .= " ORDER BY total_consultations DESC";
        break;
    default:
        $patients_query .= " ORDER BY last_visit DESC";
}

$params[] = $_SESSION['user_id']; // For diagnoses count
$params[] = $_SESSION['user_id']; // For operations count

$patients = getRows($patients_query, $params) ?: [];

// Get specific patient details if viewing
$patient_details = null;
$patient_consultations = [];
$patient_diagnoses = [];
$patient_operations = [];

if (!empty($patient_id)) {
    $patient_details = getRow("SELECT * FROM patient WHERE ssn = ?", [$patient_id]);
    
    if ($patient_details) {
        // Get patient's consultations with this doctor
        $patient_consultations = getRows("
            SELECT c.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name
            FROM consultation c 
            JOIN doctor d ON c.doctor_ssn = d.ssn 
            WHERE c.patient_ssn = ? AND c.doctor_ssn = ?
            ORDER BY c.consultation_date DESC
        ", [$patient_id, $_SESSION['user_id']]) ?: [];
        
        // Get patient's diagnoses from this doctor
        $patient_diagnoses = getRows("
            SELECT d.*, CONCAT(doc.first_name, ' ', doc.last_name) as doctor_name
            FROM diagnosis d 
            JOIN doctor doc ON d.doctor_ssn = doc.ssn 
            WHERE d.patient_ssn = ? AND d.doctor_ssn = ?
            ORDER BY d.diagnosis_date DESC
        ", [$patient_id, $_SESSION['user_id']]) ?: [];
        
        // Get patient's operations from this doctor
        $patient_operations = getRows("
            SELECT o.*, CONCAT(doc.first_name, ' ', doc.last_name) as doctor_name
            FROM operation o 
            JOIN doctor doc ON o.doctor_ssn = doc.ssn 
            WHERE o.patient_ssn = ? AND o.doctor_ssn = ?
            ORDER BY o.operation_date DESC
        ", [$patient_id, $_SESSION['user_id']]) ?: [];
    }
}

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients - Doctor Portal</title>
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
                <li class="active">
                    <a href="patients.php">
                        <i class="fas fa-users"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li>
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

            <?php if (!empty($patient_id) && $patient_details): ?>
                <!-- Patient Details View -->
                <div class="patient-detail-header">
                    <div class="patient-info">
                        <div class="patient-avatar-large">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="patient-meta">
                            <h1><?php echo htmlspecialchars($patient_details['first_name'] . ' ' . $patient_details['last_name']); ?></h1>
                            <p class="patient-id">Patient ID: <?php echo htmlspecialchars($patient_details['ssn']); ?></p>
                            <div class="patient-stats">
                                <span class="stat-item">
                                    <i class="fas fa-birthday-cake"></i>
                                    <?php echo calculate_age($patient_details['date_of_birth']); ?> years old
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-venus-mars"></i>
                                    <?php echo htmlspecialchars($patient_details['gender']); ?>
                                </span>
                                <?php if ($patient_details['blood_type']): ?>
                                <span class="stat-item">
                                    <i class="fas fa-tint"></i>
                                    <?php echo htmlspecialchars($patient_details['blood_type']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="patient-actions">
                        <button class="btn btn-primary" onclick="location.href='consultations.php?action=new&patient=<?php echo $patient_details['ssn']; ?>'">
                            <i class="fas fa-stethoscope"></i> New Consultation
                        </button>
                        <button class="btn btn-secondary" onclick="location.href='diagnoses.php?action=new&patient=<?php echo $patient_details['ssn']; ?>'">
                            <i class="fas fa-diagnoses"></i> Add Diagnosis
                        </button>
                        <a href="patients.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Patients
                        </a>
                    </div>
                </div>

                <!-- Patient Records Tabs -->
                <div class="patient-records-tabs">
                    <div class="tab-buttons">
                        <button class="tab-btn active" data-tab="overview">
                            <i class="fas fa-info-circle"></i> Overview
                        </button>
                        <button class="tab-btn" data-tab="consultations">
                            <i class="fas fa-stethoscope"></i> Consultations (<?php echo count($patient_consultations); ?>)
                        </button>
                        <button class="tab-btn" data-tab="diagnoses">
                            <i class="fas fa-diagnoses"></i> Diagnoses (<?php echo count($patient_diagnoses); ?>)
                        </button>
                        <button class="tab-btn" data-tab="operations">
                            <i class="fas fa-procedures"></i> Operations (<?php echo count($patient_operations); ?>)
                        </button>
                    </div>

                    <!-- Overview Tab -->
                    <div class="tab-content active" id="overview">
                        <div class="overview-grid">
                            <div class="overview-card patient-info-card">
                                <h3><i class="fas fa-user"></i> Personal Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Full Name:</label>
                                        <span><?php echo htmlspecialchars($patient_details['first_name'] . ' ' . $patient_details['last_name']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Date of Birth:</label>
                                        <span><?php echo format_date($patient_details['date_of_birth']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Gender:</label>
                                        <span><?php echo htmlspecialchars($patient_details['gender']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Blood Type:</label>
                                        <span><?php echo htmlspecialchars($patient_details['blood_type'] ?: 'Not specified'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Phone:</label>
                                        <span><?php echo htmlspecialchars($patient_details['phone'] ?: 'Not provided'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Email:</label>
                                        <span><?php echo htmlspecialchars($patient_details['email'] ?: 'Not provided'); ?></span>
                                    </div>
                                </div>
                                <?php if ($patient_details['address']): ?>
                                <div class="info-item full-width">
                                    <label>Address:</label>
                                    <span><?php echo htmlspecialchars($patient_details['address']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="overview-card emergency-card">
                                <h3><i class="fas fa-phone"></i> Emergency Contact</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Contact Name:</label>
                                        <span><?php echo htmlspecialchars($patient_details['emergency_contact'] ?: 'Not provided'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Contact Phone:</label>
                                        <span><?php echo htmlspecialchars($patient_details['emergency_phone'] ?: 'Not provided'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="overview-card allergies-card">
                                <h3><i class="fas fa-exclamation-triangle"></i> Allergies & Medical Conditions</h3>
                                <?php if ($patient_details['allergies']): ?>
                                    <div class="allergies-list">
                                        <?php echo nl2br(htmlspecialchars($patient_details['allergies'])); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-data">No known allergies recorded.</p>
                                <?php endif; ?>
                            </div>

                            <div class="overview-card summary-card">
                                <h3><i class="fas fa-chart-bar"></i> Medical Summary</h3>
                                <div class="summary-stats">
                                    <div class="summary-stat">
                                        <div class="stat-number"><?php echo count($patient_consultations); ?></div>
                                        <div class="stat-label">Consultations</div>
                                    </div>
                                    <div class="summary-stat">
                                        <div class="stat-number"><?php echo count($patient_diagnoses); ?></div>
                                        <div class="stat-label">Diagnoses</div>
                                    </div>
                                    <div class="summary-stat">
                                        <div class="stat-number"><?php echo count($patient_operations); ?></div>
                                        <div class="stat-label">Operations</div>
                                    </div>
                                </div>
                                <?php if (!empty($patient_consultations)): ?>
                                <p class="last-visit">Last visit: <?php echo format_datetime($patient_consultations[0]['consultation_date']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Consultations Tab -->
                    <div class="tab-content" id="consultations">
                        <div class="records-header">
                            <h3>Consultation History</h3>
                            <button class="btn btn-primary" onclick="location.href='consultations.php?action=new&patient=<?php echo $patient_details['ssn']; ?>'">
                                <i class="fas fa-plus"></i> New Consultation
                            </button>
                        </div>
                        
                        <?php if (!empty($patient_consultations)): ?>
                            <div class="records-timeline">
                                <?php foreach ($patient_consultations as $consultation): ?>
                                    <div class="timeline-item consultation-item">
                                        <div class="timeline-marker consultation-marker">
                                            <i class="fas fa-stethoscope"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="record-header">
                                                <h4>Consultation</h4>
                                                <span class="record-date"><?php echo format_datetime($consultation['consultation_date']); ?></span>
                                            </div>
                                            <div class="record-body">
                                                <?php if ($consultation['symptoms']): ?>
                                                    <p><strong>Symptoms:</strong> <?php echo htmlspecialchars($consultation['symptoms']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($consultation['diagnosis']): ?>
                                                    <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($consultation['diagnosis']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($consultation['treatment']): ?>
                                                    <p><strong>Treatment:</strong> <?php echo htmlspecialchars($consultation['treatment']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="record-footer">
                                                <span class="reference">Ref: <?php echo htmlspecialchars($consultation['reference_number']); ?></span>
                                                <button class="btn btn-sm btn-info" onclick="viewConsultation('<?php echo $consultation['id']; ?>')">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-stethoscope"></i>
                                <h4>No Consultations</h4>
                                <p>No consultation records found for this patient.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Diagnoses Tab -->
                    <div class="tab-content" id="diagnoses">
                        <div class="records-header">
                            <h3>Diagnosis History</h3>
                            <button class="btn btn-primary" onclick="location.href='diagnoses.php?action=new&patient=<?php echo $patient_details['ssn']; ?>'">
                                <i class="fas fa-plus"></i> New Diagnosis
                            </button>
                        </div>
                        
                        <?php if (!empty($patient_diagnoses)): ?>
                            <div class="records-timeline">
                                <?php foreach ($patient_diagnoses as $diagnosis): ?>
                                    <div class="timeline-item diagnosis-item">
                                        <div class="timeline-marker diagnosis-marker">
                                            <i class="fas fa-diagnoses"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="record-header">
                                                <h4><?php echo htmlspecialchars($diagnosis['diagnosis_name']); ?></h4>
                                                <span class="record-date"><?php echo format_datetime($diagnosis['diagnosis_date']); ?></span>
                                            </div>
                                            <div class="record-body">
                                                <div class="severity-badge <?php echo strtolower($diagnosis['severity']); ?>">
                                                    <?php echo $diagnosis['severity']; ?>
                                                </div>
                                                <?php if ($diagnosis['description']): ?>
                                                    <p><?php echo htmlspecialchars($diagnosis['description']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($diagnosis['icd_code']): ?>
                                                    <p><strong>ICD Code:</strong> <?php echo htmlspecialchars($diagnosis['icd_code']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="record-footer">
                                                <span class="reference">Ref: <?php echo htmlspecialchars($diagnosis['reference_number']); ?></span>
                                                <button class="btn btn-sm btn-info" onclick="viewDiagnosis('<?php echo $diagnosis['id']; ?>')">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-diagnoses"></i>
                                <h4>No Diagnoses</h4>
                                <p>No diagnosis records found for this patient.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Operations Tab -->
                    <div class="tab-content" id="operations">
                        <div class="records-header">
                            <h3>Surgical History</h3>
                            <button class="btn btn-primary" onclick="location.href='operations.php?action=new&patient=<?php echo $patient_details['ssn']; ?>'">
                                <i class="fas fa-plus"></i> Schedule Operation
                            </button>
                        </div>
                        
                        <?php if (!empty($patient_operations)): ?>
                            <div class="records-timeline">
                                <?php foreach ($patient_operations as $operation): ?>
                                    <div class="timeline-item operation-item">
                                        <div class="timeline-marker operation-marker">
                                            <i class="fas fa-procedures"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="record-header">
                                                <h4><?php echo htmlspecialchars($operation['operation_type']); ?></h4>
                                                <span class="record-date"><?php echo format_datetime($operation['operation_date']); ?></span>
                                            </div>
                                            <div class="record-body">
                                                <div class="status-badge <?php echo strtolower($operation['status']); ?>">
                                                    <?php echo $operation['status']; ?>
                                                </div>
                                                <?php if ($operation['description']): ?>
                                                    <p><?php echo htmlspecialchars($operation['description']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($operation['complications']): ?>
                                                    <p><strong>Complications:</strong> <?php echo htmlspecialchars($operation['complications']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="record-footer">
                                                <span class="reference">Ref: <?php echo htmlspecialchars($operation['reference_number']); ?></span>
                                                <button class="btn btn-sm btn-info" onclick="viewOperation('<?php echo $operation['id']; ?>')">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-procedures"></i>
                                <h4>No Operations</h4>
                                <p>No surgical records found for this patient.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Patients List View -->
                <div class="page-header">
                    <h1><i class="fas fa-users"></i> My Patients</h1>
                    <p>Manage and view your patient records</p>
                </div>

                <!-- Search and Filters -->
                <div class="patients-controls">
                    <form method="GET" action="" class="patients-search-form">
                        <div class="search-section">
                            <div class="search-input-group">
                                <input type="text" name="search" class="search-input" 
                                       placeholder="Search by name, ID, or phone..." 
                                       value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="filters-section">
                            <select name="age_range" class="filter-select">
                                <option value="">All Ages</option>
                                <option value="child" <?php echo $age_range === 'child' ? 'selected' : ''; ?>>Children (0-17)</option>
                                <option value="adult" <?php echo $age_range === 'adult' ? 'selected' : ''; ?>>Adults (18-64)</option>
                                <option value="senior" <?php echo $age_range === 'senior' ? 'selected' : ''; ?>>Seniors (65+)</option>
                            </select>
                            
                            <select name="gender" class="filter-select">
                                <option value="">All Genders</option>
                                <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            
                            <select name="blood_type" class="filter-select">
                                <option value="">All Blood Types</option>
                                <option value="A+" <?php echo $blood_type === 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo $blood_type === 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo $blood_type === 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo $blood_type === 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo $blood_type === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo $blood_type === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo $blood_type === 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo $blood_type === 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                            
                            <select name="sort" class="filter-select">
                                <option value="last_visit" <?php echo $sort_by === 'last_visit' ? 'selected' : ''; ?>>Sort by Last Visit</option>
                                <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                                <option value="age" <?php echo $sort_by === 'age' ? 'selected' : ''; ?>>Sort by Age</option>
                                <option value="consultations" <?php echo $sort_by === 'consultations' ? 'selected' : ''; ?>>Sort by Consultations</option>
                            </select>
                            
                            <button type="submit" class="btn btn-secondary">Apply Filters</button>
                            <a href="patients.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Patients Grid -->
                <div class="patients-results">
                    <div class="results-header">
                        <h3>Patients (<?php echo count($patients); ?> found)</h3>
                    </div>
                    
                    <?php if (!empty($patients)): ?>
                        <div class="patients-grid">
                            <?php foreach ($patients as $patient): ?>
                                <div class="patient-card" onclick="viewPatientDetails('<?php echo $patient['ssn']; ?>')">
                                    <div class="patient-card-header">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="patient-basic-info">
                                            <h4><?php echo htmlspecialchars($patient['full_name']); ?></h4>
                                            <p class="patient-id">ID: <?php echo htmlspecialchars($patient['ssn']); ?></p>
                                        </div>
                                        <div class="patient-age">
                                            <span class="age-number"><?php echo calculate_age($patient['date_of_birth']); ?></span>
                                            <span class="age-label">years</span>
                                        </div>
                                    </div>
                                    
                                    <div class="patient-card-body">
                                        <div class="patient-details">
                                            <div class="detail-item">
                                                <i class="fas fa-venus-mars"></i>
                                                <span><?php echo htmlspecialchars($patient['gender']); ?></span>
                                            </div>
                                            <?php if ($patient['blood_type']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-tint"></i>
                                                <span><?php echo htmlspecialchars($patient['blood_type']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="detail-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($patient['phone'] ?: 'No phone'); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="patient-stats">
                                            <div class="stat-item">
                                                <span class="stat-number"><?php echo $patient['total_consultations']; ?></span>
                                                <span class="stat-label">Consultations</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number"><?php echo $patient['total_diagnoses']; ?></span>
                                                <span class="stat-label">Diagnoses</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number"><?php echo $patient['total_operations']; ?></span>
                                                <span class="stat-label">Operations</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="patient-card-footer">
                                        <div class="last-visit">
                                            <i class="fas fa-calendar"></i>
                                            Last visit: <?php echo format_date($patient['last_visit']); ?>
                                        </div>
                                        <div class="patient-actions">
                                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); newConsultation('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-stethoscope"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="event.stopPropagation(); viewPatientDetails('<?php echo $patient['ssn']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-patients">
                            <i class="fas fa-user-friends"></i>
                            <h4>No Patients Found</h4>
                            <p>No patients match your current search criteria.</p>
                            <button class="btn btn-primary" onclick="location.href='consultations.php?action=new'">
                                <i class="fas fa-plus"></i> Add First Patient
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

        .patient-detail-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .patient-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .patient-meta h1 {
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .patient-id {
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .patient-stats {
            display: flex;
            gap: 2rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .stat-item i {
            color: #2563eb;
        }

        .patient-actions {
            display: flex;
            gap: 1rem;
        }

        .patient-records-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 1rem 1.5rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #6b7280;
        }

        .tab-btn:hover,
        .tab-btn.active {
            color: #10b981;
            border-bottom-color: #10b981;
            background: #f0fdf4;
        }

        .tab-content {
            display: none;
            padding: 2rem;
        }

        .tab-content.active {
            display: block;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .overview-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .overview-card h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .info-item label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .info-item span {
            color: #1e293b;
        }

        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 1rem;
        }

        .summary-stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .records-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .records-timeline {
            position: relative;
        }

        .records-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 60px;
        }

        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .consultation-marker { background: #10b981; }
        .diagnosis-marker { background: #f59e0b; }
        .operation-marker { background: #ef4444; }

        .timeline-content {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .record-header h4 {
            color: #1e293b;
            margin: 0;
        }

        .record-date {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .record-body {
            margin-bottom: 1rem;
        }

        .record-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reference {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .severity-badge, .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .severity-badge.mild { background: #ecfdf5; color: #059669; }
        .severity-badge.moderate { background: #fef3c7; color: #d97706; }
        .severity-badge.severe { background: #fee2e2; color: #dc2626; }
        .severity-badge.critical { background: #fef2f2; color: #991b1b; }

        .status-badge.completed { background: #dcfce7; color: #166534; }
        .status-badge.scheduled { background: #dbeafe; color: #1d4ed8; }
        .status-badge.cancelled { background: #fef2f2; color: #dc2626; }

        .patients-controls {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .patients-search-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .search-section {
            flex: 1;
        }

        .search-input-group {
            display: flex;
            gap: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #10b981;
        }

        .search-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
        }

        .filters-section {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: white;
        }

        .patients-results {
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

        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .patient-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .patient-card:hover {
            border-color: #10b981;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
        }

        .patient-card-header {
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

        .patient-basic-info {
            flex: 1;
        }

        .patient-basic-info h4 {
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        .patient-age {
            text-align: center;
        }

        .age-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }

        .age-label {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .patient-card-body {
            margin-bottom: 1rem;
        }

        .patient-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .detail-item i {
            color: #10b981;
        }

        .patient-stats {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
        }

        .patient-stats .stat-item {
            text-align: center;
            flex-direction: column;
            gap: 0.25rem;
        }

        .patient-stats .stat-number {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .patient-stats .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .patient-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .last-visit {
            font-size: 0.875rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .last-visit i {
            color: #10b981;
        }

        .patient-actions {
            display: flex;
            gap: 0.5rem;
        }

        .no-patients, .no-records {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .no-patients i, .no-records i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-patients h4, .no-records h4 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .no-data {
            color: #6b7280;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .patient-detail-header {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .patient-info {
                flex-direction: column;
                text-align: center;
            }

            .patient-actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .overview-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .patients-search-form {
                gap: 1rem;
            }

            .filters-section {
                flex-direction: column;
            }

            .patients-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .patient-stats {
                flex-direction: column;
                gap: 0.5rem;
            }

            .patient-card-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.getAttribute('data-tab');
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        function viewPatientDetails(patientId) {
            window.location.href = `patients.php?view=${patientId}`;
        }

        function newConsultation(patientId) {
            window.location.href = `consultations.php?action=new&patient=${patientId}`;
        }

        function viewConsultation(consultationId) {
            window.location.href = `consultations.php?view=${consultationId}`;
        }

        function viewDiagnosis(diagnosisId) {
            window.location.href = `diagnoses.php?view=${diagnosisId}`;
        }

        function viewOperation(operationId) {
            window.location.href = `operations.php?view=${operationId}`;
        }
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>