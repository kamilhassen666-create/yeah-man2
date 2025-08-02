<?php
require_once '../includes/config.php';
requireUserType(['admin']);

$pdo = getDBConnection();

// Get system statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM patients");
$stmt->execute();
$patients_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM doctors");
$stmt->execute();
$doctors_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM staff");
$stmt->execute();
$staff_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM consultations");
$stmt->execute();
$consultations_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM surgeries");
$stmt->execute();
$surgeries_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE status = 'Completed'");
$stmt->execute();
$payments_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent patients
$stmt = $pdo->prepare("SELECT * FROM patients ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent doctors
$stmt = $pdo->prepare("SELECT * FROM doctors ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-3">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/admin-avatar.png" alt="Admin" class="profile-avatar mb-3">
                <h5 class="text-white">Administrator</h5>
                <p class="text-white-50">System Management</p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users"></i>Manage Patients
                </a>
                <a class="nav-link" href="doctors.php">
                    <i class="fas fa-user-md"></i>Manage Doctors
                </a>
                <a class="nav-link" href="staff.php">
                    <i class="fas fa-user-nurse"></i>Manage Staff
                </a>
                <a class="nav-link" href="hospitals.php">
                    <i class="fas fa-hospital"></i>Manage Hospitals
                </a>
                <a class="nav-link" href="referrals.php">
                    <i class="fas fa-exchange-alt"></i>Referrals
                </a>
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i>Reports
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i>Settings
                </a>
            </nav>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2>System Overview</h2>
                    <p class="text-muted">Complete overview of the hospital management system</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $patients_count; ?></h3>
                        <p>Patients</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $doctors_count; ?></h3>
                        <p>Doctors</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $staff_count; ?></h3>
                        <p>Staff</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $consultations_count; ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $surgeries_count; ?></h3>
                        <p>Surgeries</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="dashboard-card">
                        <h3><?php echo $payments_count; ?></h3>
                        <p>Payments</p>
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
                                    <a href="add_patient.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Add Patient
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_doctor.php" class="btn btn-success w-100">
                                        <i class="fas fa-user-md me-2"></i>Add Doctor
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_staff.php" class="btn btn-info w-100">
                                        <i class="fas fa-user-nurse me-2"></i>Add Staff
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="add_hospital.php" class="btn btn-warning w-100">
                                        <i class="fas fa-hospital me-2"></i>Add Hospital
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Patients -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Recent Patients
                            </h5>
                            <a href="patients.php" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_patients)): ?>
                                <p class="text-muted text-center">No patients found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Patient ID</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Gender</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_patients as $patient): ?>
                                                <tr>
                                                    <td><span class="badge bg-primary"><?php echo $patient['patient_id']; ?></span></td>
                                                    <td><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></td>
                                                    <td><?php echo $patient['phone']; ?></td>
                                                    <td><?php echo $patient['gender']; ?></td>
                                                    <td><?php echo formatDate($patient['created_at']); ?></td>
                                                    <td>
                                                        <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
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
            
            <!-- Recent Doctors -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-md me-2"></i>Recent Doctors
                            </h5>
                            <a href="doctors.php" class="btn btn-success btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_doctors)): ?>
                                <p class="text-muted text-center">No doctors found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Doctor ID</th>
                                                <th>Name</th>
                                                <th>Specialization</th>
                                                <th>Phone</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_doctors as $doctor): ?>
                                                <tr>
                                                    <td><span class="badge bg-success"><?php echo $doctor['doctor_id']; ?></span></td>
                                                    <td>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></td>
                                                    <td><?php echo $doctor['specialization']; ?></td>
                                                    <td><?php echo $doctor['phone']; ?></td>
                                                    <td><?php echo formatDate($doctor['created_at']); ?></td>
                                                    <td>
                                                        <a href="view_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2"></i>System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-database fa-2x text-success mb-2"></i>
                                        <h6>Database</h6>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-server fa-2x text-success mb-2"></i>
                                        <h6>Server</h6>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                        <h6>Security</h6>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                        <h6>Uptime</h6>
                                        <span class="badge bg-info">99.9%</span>
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