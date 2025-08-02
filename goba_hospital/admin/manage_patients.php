<?php
require_once '../includes/config.php';
check_session('admin');

$page_title = 'Manage Patients';
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_patient') {
        $patient_id = generate_id('PAT');
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $emergency_contact_name = sanitize_input($_POST['emergency_contact_name']);
        $emergency_contact_phone = sanitize_input($_POST['emergency_contact_phone']);
        $blood_group = $_POST['blood_group'];
        $national_id = sanitize_input($_POST['national_id']);
        $marital_status = $_POST['marital_status'];
        $occupation = sanitize_input($_POST['occupation']);
        $username = sanitize_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $db->beginTransaction();
            
            // Insert patient info
            $stmt = $db->prepare("
                INSERT INTO patient_info (
                    patient_id, first_name, last_name, date_of_birth, gender, phone, email, 
                    address, emergency_contact_name, emergency_contact_phone, blood_group, 
                    national_id, marital_status, occupation
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $patient_id, $first_name, $last_name, $date_of_birth, $gender, $phone, $email,
                $address, $emergency_contact_name, $emergency_contact_phone, $blood_group,
                $national_id, $marital_status, $occupation
            ]);
            
            // Insert login credentials
            $stmt = $db->prepare("
                INSERT INTO patient_login (patient_id, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$patient_id, $username, $password]);
            
            $db->commit();
            log_system_action($_SESSION['user_id'], 'admin', "Added new patient: $patient_id");
            $success_message = "Patient added successfully! Patient ID: $patient_id";
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Error adding patient: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_patient') {
        $id = $_POST['id'];
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $emergency_contact_name = sanitize_input($_POST['emergency_contact_name']);
        $emergency_contact_phone = sanitize_input($_POST['emergency_contact_phone']);
        $blood_group = $_POST['blood_group'];
        $marital_status = $_POST['marital_status'];
        $occupation = sanitize_input($_POST['occupation']);
        
        try {
            $stmt = $db->prepare("
                UPDATE patient_info SET 
                first_name = ?, last_name = ?, phone = ?, email = ?, address = ?, 
                emergency_contact_name = ?, emergency_contact_phone = ?, blood_group = ?, 
                marital_status = ?, occupation = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $first_name, $last_name, $phone, $email, $address,
                $emergency_contact_name, $emergency_contact_phone, $blood_group,
                $marital_status, $occupation, $id
            ]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Updated patient ID: $id");
            $success_message = "Patient updated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error updating patient: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete_patient') {
        $id = $_POST['id'];
        
        try {
            $stmt = $db->prepare("UPDATE patient_info SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Deactivated patient ID: $id");
            $success_message = "Patient deactivated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error deactivating patient: " . $e->getMessage();
        }
    }
}

// Get patients with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR patient_id LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter !== 'all') {
    $where_conditions[] = "is_active = ?";
    $params[] = $status_filter === 'active' ? 1 : 0;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM patient_info $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_patients = $stmt->fetchColumn();
$total_pages = ceil($total_patients / $per_page);

// Get patients
$sql = "
    SELECT p.*, pl.username, pl.last_login
    FROM patient_info p
    LEFT JOIN patient_login pl ON p.patient_id = pl.patient_id
    $where_clause
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-user-injured"></i> Manage Patients
                    </h1>
                    <p class="text-muted">Add, edit, and manage patient records</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                    <i class="fas fa-plus"></i> Add New Patient
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Patients</label>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, ID, or phone">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Patients</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <a href="manage_patients.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Patients List 
                        <span class="badge bg-primary"><?php echo $total_patients; ?> total</span>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm btn-export-csv" 
                                data-table="patientsTable" data-filename="patients.csv">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-outline-primary btn-sm btn-print" 
                                data-target="patientsTable">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="patientsTable">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Age/Gender</th>
                                    <th>Contact</th>
                                    <th>Blood Group</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo $patient['patient_id']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $age = date('Y') - date('Y', strtotime($patient['date_of_birth']));
                                            echo $age . ' years<br><small class="text-muted">' . $patient['gender'] . '</small>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($patient['phone']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($patient['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo $patient['blood_group'] ?: 'N/A'; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($patient['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($patient['last_login']): ?>
                                                <small class="text-muted">
                                                    <?php echo time_ago($patient['last_login']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Never</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" 
                                                        onclick="editPatient(<?php echo $patient['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($patient['is_active']): ?>
                                                    <button class="btn btn-outline-danger btn-delete" 
                                                            onclick="deletePatient(<?php echo $patient['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (empty($patients)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-injured fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No patients found.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Patients pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New Patient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_patient">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control date-picker" name="date_of_birth" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control validate-phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control validate-email" name="email">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control validate-phone" name="emergency_contact_phone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select class="form-select" name="blood_group">
                                <option value="">Select Blood Group</option>
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
                        <div class="col-md-4">
                            <label class="form-label">Marital Status</label>
                            <select class="form-select" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">National ID</label>
                            <input type="text" class="form-control" name="national_id">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation">
                        </div>
                        
                        <div class="col-12"><hr></div>
                        <div class="col-12">
                            <h6 class="text-primary">Login Credentials</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewPatient(id) {
    // Implementation for viewing patient details
    window.open(`view_patient.php?id=${id}`, '_blank');
}

function editPatient(id) {
    // Implementation for editing patient
    window.location.href = `edit_patient.php?id=${id}`;
}

function deletePatient(id) {
    if (confirm('Are you sure you want to deactivate this patient?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_patient">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-generate username based on name
document.querySelector('input[name="first_name"]').addEventListener('input', generateUsername);
document.querySelector('input[name="last_name"]').addEventListener('input', generateUsername);

function generateUsername() {
    const firstName = document.querySelector('input[name="first_name"]').value.toLowerCase();
    const lastName = document.querySelector('input[name="last_name"]').value.toLowerCase();
    
    if (firstName && lastName) {
        const username = firstName.charAt(0) + lastName + Math.floor(Math.random() * 1000);
        document.querySelector('input[name="username"]').value = username;
    }
}
</script>

<?php include '../includes/footer.php'; ?>