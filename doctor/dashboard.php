<?php
require_once '../includes/config.php';
requireUserType(['doctor']);

$pdo = getDBConnection();

// Get doctor information
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Get doctor's consultations count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM consultations WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$consultations_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get doctor's surgeries count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM surgeries WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$surgeries_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get doctor's diagnoses count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM diagnoses WHERE doctor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$diagnoses_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent consultations
$stmt = $pdo->prepare("SELECT c.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM consultations c 
                       JOIN patients p ON c.patient_id = p.id 
                       WHERE c.doctor_id = ? 
                       ORDER BY c.consultation_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent surgeries
$stmt = $pdo->prepare("SELECT s.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id 
                       FROM surgeries s 
                       JOIN patients p ON s.patient_id = p.id 
                       WHERE s.doctor_id = ? 
                       ORDER BY s.surgery_date DESC 
                       LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_surgeries = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-3">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/doctor-avatar.png" alt="Doctor" class="profile-avatar mb-3">
                <h5 class="text-white">Dr. <?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50"><?php echo $doctor['specialization']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users"></i>Patients
                </a>
                <a class="nav-link" href="consultations.php">
                    <i class="fas fa-stethoscope"></i>Consultations
                </a>
                <a class="nav-link" href="surgeries.php">
                    <i class="fas fa-procedures"></i>Surgeries
                </a>
                <a class="nav-link" href="diagnoses.php">
                    <i class="fas fa-notes-medical"></i>Diagnoses
                </a>
                <a class="nav-link" href="referrals.php">
                    <i class="fas fa-exchange-alt"></i>Referrals
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i>Profile
                </a>
            </nav>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2>Welcome, Dr. <?php echo $doctor['first_name']; ?>!</h2>
                    <p class="text-muted">Here's an overview of your medical practice</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $consultations_count; ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $surgeries_count; ?></h3>
                        <p>Surgeries</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $diagnoses_count; ?></h3>
                        <p>Diagnoses</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="add_consultation.php" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i>New Consultation
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_surgery.php" class="btn btn-success w-100">
                                        <i class="fas fa-procedures me-2"></i>Record Surgery
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_diagnosis.php" class="btn btn-info w-100">
                                        <i class="fas fa-notes-medical me-2"></i>Add Diagnosis
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="search_patient.php" class="btn btn-warning w-100">
                                        <i class="fas fa-search me-2"></i>Search Patient
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Consultations -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>Recent Consultations
                            </h5>
                            <a href="consultations.php" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_consultations)): ?>
                                <p class="text-muted text-center">No consultations found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Patient</th>
                                                <th>Diagnosis</th>
                                                <th>Reference ID</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_consultations as $consultation): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($consultation['consultation_date']); ?></td>
                                                    <td><?php echo $consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']; ?></td>
                                                    <td><?php echo substr($consultation['diagnosis'], 0, 50) . (strlen($consultation['diagnosis']) > 50 ? '...' : ''); ?></td>
                                                    <td><span class="badge bg-primary"><?php echo $consultation['reference_id']; ?></span></td>
                                                    <td>
                                                        <a href="view_consultation.php?id=<?php echo $consultation['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Surgeries -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-procedures me-2"></i>Recent Surgeries
                            </h5>
                            <a href="surgeries.php" class="btn btn-success btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_surgeries)): ?>
                                <p class="text-muted text-center">No surgeries found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Patient</th>
                                                <th>Surgery Type</th>
                                                <th>Reference ID</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_surgeries as $surgery): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($surgery['surgery_date']); ?></td>
                                                    <td><?php echo $surgery['patient_first_name'] . ' ' . $surgery['patient_last_name']; ?></td>
                                                    <td><?php echo $surgery['surgery_type']; ?></td>
                                                    <td><span class="badge bg-success"><?php echo $surgery['reference_id']; ?></span></td>
                                                    <td>
                                                        <a href="view_surgery.php?id=<?php echo $surgery['id']; ?>" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Patient -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>Search Patient
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="search_patient.php" method="GET">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="search_type" class="form-label">Search By</label>
                                            <select class="form-select" id="search_type" name="search_type">
                                                <option value="patient_id">Patient ID</option>
                                                <option value="name">Name</option>
                                                <option value="national_id">National ID</option>
                                                <option value="phone">Phone</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="search_term" class="form-label">Search Term</label>
                                            <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Enter search term...">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>