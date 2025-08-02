<?php
require_once '../includes/config.php';
requireUserType(['staff']);

$pdo = getDBConnection();

// Get staff information
$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

// Get staff's recent medication dosages
$stmt = $pdo->prepare("SELECT md.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM medication_dosages md 
                       JOIN patients p ON md.patient_id = p.id 
                       WHERE md.staff_id = ? 
                       ORDER BY md.created_at DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$medications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_medications FROM medication_dosages WHERE staff_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_medications = $stmt->fetch(PDO::FETCH_ASSOC)['total_medications'];

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) as unique_patients FROM medication_dosages WHERE staff_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$unique_patients = $stmt->fetch(PDO::FETCH_ASSOC)['unique_patients'];

// Get recent patient activities
$stmt = $pdo->prepare("SELECT c.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM consultations c 
                       JOIN patients p ON c.patient_id = p.id 
                       ORDER BY c.created_at DESC 
                       LIMIT 5");
$stmt->execute();
$recent_consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/staff-avatar.png" alt="Staff" class="profile-avatar mb-3">
                <h5 class="text-white"><?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50"><?php echo $staff['position']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="medications.php">
                    <i class="fas fa-pills"></i> Medications
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users"></i> Patients
                </a>
                <a class="nav-link" href="add_medication.php">
                    <i class="fas fa-plus"></i> Add Medication
                </a>
                <a class="nav-link" href="search_patient.php">
                    <i class="fas fa-search"></i> Search Patient
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-3">
                        <i class="fas fa-user-nurse me-2"></i>
                        Welcome, <?php echo $staff['first_name']; ?>!
                    </h2>
                    <p class="text-muted">Manage medications and patient information</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo $total_medications; ?></h3>
                        <p>Medications</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo $unique_patients; ?></h3>
                        <p>Patients</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <h3><?php echo count($recent_consultations); ?></h3>
                        <p>Recent Activities</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="add_medication.php" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i>
                                        Add Medication
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="search_patient.php" class="btn btn-success w-100">
                                        <i class="fas fa-search me-2"></i>
                                        Search Patient
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="medications.php" class="btn btn-info w-100">
                                        <i class="fas fa-pills me-2"></i>
                                        View Medications
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="patients.php" class="btn btn-warning w-100">
                                        <i class="fas fa-users me-2"></i>
                                        View Patients
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Medications -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-pills me-2"></i>
                                Recent Medications
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($medications)): ?>
                                <p class="text-muted">No recent medications found.</p>
                            <?php else: ?>
                                <?php foreach ($medications as $medication): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $medication['medication_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Patient: <?php echo $medication['patient_first_name'] . ' ' . $medication['patient_last_name']; ?>
                                                    <br>
                                                    Dosage: <?php echo $medication['dosage']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($medication['created_at']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-info"><?php echo $medication['frequency']; ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($medication['notes'], 0, 100) . '...'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <a href="medications.php" class="btn btn-sm btn-outline-info">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Patient Activities -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                Recent Patient Activities
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_consultations)): ?>
                                <p class="text-muted">No recent patient activities found.</p>
                            <?php else: ?>
                                <?php foreach ($recent_consultations as $consultation): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    ID: <?php echo $consultation['patient_id']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($consultation['consultation_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">Consultation</span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($consultation['diagnosis'], 0, 100) . '...'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <a href="patients.php" class="btn btn-sm btn-outline-primary">View All Patients</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Patient Search -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>
                                Quick Patient Search
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="search_patient.php" method="GET">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Enter patient ID or name">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Search Patient
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medication Management -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Today's Medication Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">No medications scheduled for today.</p>
                            <a href="medications.php" class="btn btn-outline-primary">View All Medications</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Medication Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">No medication alerts at this time.</p>
                            <a href="medications.php" class="btn btn-outline-warning">View Alerts</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>