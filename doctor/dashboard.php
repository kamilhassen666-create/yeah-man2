<?php
require_once '../includes/config.php';
requireUserType(['doctor']);

$pdo = getDBConnection();

// Get doctor information
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Get doctor's recent consultations
$stmt = $pdo->prepare("SELECT c.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM consultations c 
                       JOIN patients p ON c.patient_id = p.id 
                       WHERE c.doctor_id = ? 
                       ORDER BY c.consultation_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get doctor's recent surgeries
$stmt = $pdo->prepare("SELECT s.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM surgeries s 
                       JOIN patients p ON s.patient_id = p.id 
                       WHERE s.doctor_id = ? 
                       ORDER BY s.surgery_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$surgeries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get doctor's recent diagnoses
$stmt = $pdo->prepare("SELECT d.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM diagnoses d 
                       JOIN patients p ON d.patient_id = p.id 
                       WHERE d.doctor_id = ? 
                       ORDER BY d.diagnosis_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_consultations FROM consultations WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_consultations = $stmt->fetch(PDO::FETCH_ASSOC)['total_consultations'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_surgeries FROM surgeries WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_surgeries = $stmt->fetch(PDO::FETCH_ASSOC)['total_surgeries'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_diagnoses FROM diagnoses WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_diagnoses = $stmt->fetch(PDO::FETCH_ASSOC)['total_diagnoses'];

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) as unique_patients FROM consultations WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$unique_patients = $stmt->fetch(PDO::FETCH_ASSOC)['unique_patients'];

include '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/doctor-avatar.png" alt="Doctor" class="profile-avatar mb-3">
                <h5 class="text-white">Dr. <?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50"><?php echo $doctor['specialization']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users"></i> Patients
                </a>
                <a class="nav-link" href="consultations.php">
                    <i class="fas fa-stethoscope"></i> Consultations
                </a>
                <a class="nav-link" href="surgeries.php">
                    <i class="fas fa-procedures"></i> Surgeries
                </a>
                <a class="nav-link" href="diagnoses.php">
                    <i class="fas fa-thermometer-half"></i> Diagnoses
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
                        <i class="fas fa-user-md me-2"></i>
                        Welcome, Dr. <?php echo $doctor['first_name']; ?>!
                    </h2>
                    <p class="text-muted">Manage your patients and medical records</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_consultations; ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_surgeries; ?></h3>
                        <p>Surgeries</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_diagnoses; ?></h3>
                        <p>Diagnoses</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $unique_patients; ?></h3>
                        <p>Patients</p>
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
                                    <a href="add_consultation.php" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i>
                                        New Consultation
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_surgery.php" class="btn btn-success w-100">
                                        <i class="fas fa-procedures me-2"></i>
                                        Record Surgery
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_diagnosis.php" class="btn btn-info w-100">
                                        <i class="fas fa-thermometer-half me-2"></i>
                                        Add Diagnosis
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="search_patient.php" class="btn btn-warning w-100">
                                        <i class="fas fa-search me-2"></i>
                                        Search Patient
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Consultations -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                Recent Consultations
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($consultations)): ?>
                                <p class="text-muted">No consultations found.</p>
                            <?php else: ?>
                                <?php foreach ($consultations as $consultation): ?>
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
                                            <span class="badge bg-primary"><?php echo $consultation['reference_id']; ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($consultation['diagnosis'], 0, 100) . '...'; ?></p>
                                        <a href="view_consultation.php?id=<?php echo $consultation['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </div>
                                <?php endforeach; ?>
                                <a href="consultations.php" class="btn btn-sm btn-outline-primary">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Surgeries -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-procedures me-2"></i>
                                Recent Surgeries
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($surgeries)): ?>
                                <p class="text-muted">No surgeries found.</p>
                            <?php else: ?>
                                <?php foreach ($surgeries as $surgery): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $surgery['surgery_type']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Patient: <?php echo $surgery['patient_first_name'] . ' ' . $surgery['patient_last_name']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($surgery['surgery_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-success"><?php echo $surgery['reference_id']; ?></span>
                                        </div>
                                        <a href="view_surgery.php?id=<?php echo $surgery['id']; ?>" class="btn btn-sm btn-outline-success">View Details</a>
                                    </div>
                                <?php endforeach; ?>
                                <a href="surgeries.php" class="btn btn-sm btn-outline-success">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Diagnoses -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-thermometer-half me-2"></i>
                                Recent Diagnoses
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($diagnoses)): ?>
                                <p class="text-muted">No diagnoses found.</p>
                            <?php else: ?>
                                <?php foreach ($diagnoses as $diagnosis): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $diagnosis['condition_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Patient: <?php echo $diagnosis['patient_first_name'] . ' ' . $diagnosis['patient_last_name']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($diagnosis['diagnosis_date']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-info"><?php echo $diagnosis['reference_id']; ?></span>
                                        </div>
                                        <p class="mb-1"><?php echo substr($diagnosis['description'], 0, 100) . '...'; ?></p>
                                        <a href="view_diagnosis.php?id=<?php echo $diagnosis['id']; ?>" class="btn btn-sm btn-outline-info">View Details</a>
                                    </div>
                                <?php endforeach; ?>
                                <a href="diagnoses.php" class="btn btn-sm btn-outline-info">View All</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Patient Search -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>
                                Quick Patient Search
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="search_patient.php" method="GET">
                                <div class="mb-3">
                                    <label for="search_term" class="form-label">Search by Patient ID or Name</label>
                                    <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Enter patient ID or name">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search Patient
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments (Placeholder) -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Today's Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">No appointments scheduled for today.</p>
                            <a href="schedule.php" class="btn btn-outline-primary">View Schedule</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>