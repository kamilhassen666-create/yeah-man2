<?php
require_once '../includes/config.php';
requireUserType(['admin']);

$pdo = getDBConnection();

// Get system statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_patients FROM patients");
$stmt->execute();
$total_patients = $stmt->fetch(PDO::FETCH_ASSOC)['total_patients'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_doctors FROM doctors");
$stmt->execute();
$total_doctors = $stmt->fetch(PDO::FETCH_ASSOC)['total_doctors'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_staff FROM staff");
$stmt->execute();
$total_staff = $stmt->fetch(PDO::FETCH_ASSOC)['total_staff'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_consultations FROM consultations");
$stmt->execute();
$total_consultations = $stmt->fetch(PDO::FETCH_ASSOC)['total_consultations'];

// Get recent registrations
$stmt = $pdo->prepare("SELECT * FROM patients ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM doctors ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
$stmt = $pdo->prepare("SELECT c.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                              d.first_name as doctor_first_name, d.last_name as doctor_last_name 
                       FROM consultations c 
                       JOIN patients p ON c.patient_id = p.id 
                       JOIN doctors d ON c.doctor_id = d.id 
                       ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute();
$recent_consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/admin-avatar.png" alt="Admin" class="profile-avatar mb-3">
                <h5 class="text-white">Administrator</h5>
                <p class="text-white-50">System Management</p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users"></i> User Management
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-user-injured"></i> Patients
                </a>
                <a class="nav-link" href="doctors.php">
                    <i class="fas fa-user-md"></i> Doctors
                </a>
                <a class="nav-link" href="staff.php">
                    <i class="fas fa-user-nurse"></i> Staff
                </a>
                <a class="nav-link" href="hospitals.php">
                    <i class="fas fa-hospital"></i> Hospitals
                </a>
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i> Settings
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
                        <i class="fas fa-user-shield me-2"></i>
                        Admin Dashboard
                    </h2>
                    <p class="text-muted">System overview and management</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_patients; ?></h3>
                        <p>Patients</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_doctors; ?></h3>
                        <p>Doctors</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_staff; ?></h3>
                        <p>Staff</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h3><?php echo $total_consultations; ?></h3>
                        <p>Consultations</p>
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
                                    <a href="add_patient.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Add Patient
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_doctor.php" class="btn btn-success w-100">
                                        <i class="fas fa-user-md me-2"></i>
                                        Add Doctor
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_staff.php" class="btn btn-info w-100">
                                        <i class="fas fa-user-nurse me-2"></i>
                                        Add Staff
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="reports.php" class="btn btn-warning w-100">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Patients -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-injured me-2"></i>
                                Recent Patient Registrations
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_patients)): ?>
                                <p class="text-muted">No recent patient registrations.</p>
                            <?php else: ?>
                                <?php foreach ($recent_patients as $patient): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    ID: <?php echo $patient['patient_id']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($patient['created_at']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $patient['gender']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="patients.php" class="btn btn-sm btn-outline-primary">View All Patients</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Doctors -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                Recent Doctor Registrations
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_doctors)): ?>
                                <p class="text-muted">No recent doctor registrations.</p>
                            <?php else: ?>
                                <?php foreach ($recent_doctors as $doctor): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    ID: <?php echo $doctor['doctor_id']; ?>
                                                    <br>
                                                    <?php echo $doctor['specialization']; ?>
                                                    <br>
                                                    <?php echo formatDateTime($doctor['created_at']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-success"><?php echo $doctor['specialization']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="doctors.php" class="btn btn-sm btn-outline-success">View All Doctors</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Consultations -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                Recent Consultations
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_consultations)): ?>
                                <p class="text-muted">No recent consultations.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Date</th>
                                                <th>Reference ID</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_consultations as $consultation): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo $consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']; ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?php echo $consultation['patient_id']; ?></small>
                                                    </td>
                                                    <td>
                                                        Dr. <?php echo $consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']; ?>
                                                    </td>
                                                    <td><?php echo formatDateTime($consultation['consultation_date']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $consultation['reference_id']; ?></span>
                                                    </td>
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

            <!-- System Status -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2"></i>
                                System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <i class="fas fa-database fa-2x text-success mb-2"></i>
                                        <h6>Database</h6>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                        <h6>Security</h6>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Quick Stats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <h4 class="text-primary"><?php echo $total_patients; ?></h4>
                                        <small class="text-muted">Total Patients</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h4 class="text-success"><?php echo $total_consultations; ?></h4>
                                        <small class="text-muted">Total Consultations</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>