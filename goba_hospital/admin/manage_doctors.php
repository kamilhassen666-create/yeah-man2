<?php
require_once '../includes/config.php';
check_session('admin');

$page_title = 'Manage Doctors';
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_doctor') {
        $doctor_id = generate_id('DOC');
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $specialization = sanitize_input($_POST['specialization']);
        $qualification = sanitize_input($_POST['qualification']);
        $license_number = sanitize_input($_POST['license_number']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $national_id = sanitize_input($_POST['national_id']);
        $hire_date = $_POST['hire_date'];
        $department = sanitize_input($_POST['department']);
        $username = sanitize_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $db->beginTransaction();
            
            // Insert doctor info
            $stmt = $db->prepare("
                INSERT INTO doctor_info (
                    doctor_id, first_name, last_name, specialization, qualification, 
                    license_number, phone, email, address, date_of_birth, gender, 
                    national_id, hire_date, department
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $doctor_id, $first_name, $last_name, $specialization, $qualification,
                $license_number, $phone, $email, $address, $date_of_birth, $gender,
                $national_id, $hire_date, $department
            ]);
            
            // Insert login credentials
            $stmt = $db->prepare("
                INSERT INTO doctor_login (doctor_id, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$doctor_id, $username, $password]);
            
            $db->commit();
            log_system_action($_SESSION['user_id'], 'admin', "Added new doctor: $doctor_id");
            $success_message = "Doctor added successfully! Doctor ID: $doctor_id";
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Error adding doctor: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_doctor') {
        $id = $_POST['id'];
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $specialization = sanitize_input($_POST['specialization']);
        $qualification = sanitize_input($_POST['qualification']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $department = sanitize_input($_POST['department']);
        
        try {
            $stmt = $db->prepare("
                UPDATE doctor_info SET 
                first_name = ?, last_name = ?, specialization = ?, qualification = ?, 
                phone = ?, email = ?, address = ?, department = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $first_name, $last_name, $specialization, $qualification,
                $phone, $email, $address, $department, $id
            ]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Updated doctor ID: $id");
            $success_message = "Doctor updated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error updating doctor: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete_doctor') {
        $id = $_POST['id'];
        
        try {
            $stmt = $db->prepare("UPDATE doctor_info SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Deactivated doctor ID: $id");
            $success_message = "Doctor deactivated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error deactivating doctor: " . $e->getMessage();
        }
    }
}

// Get doctors with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$department_filter = $_GET['department'] ?? 'all';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR doctor_id LIKE ? OR specialization LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter !== 'all') {
    $where_conditions[] = "is_active = ?";
    $params[] = $status_filter === 'active' ? 1 : 0;
}

if ($department_filter !== 'all') {
    $where_conditions[] = "department = ?";
    $params[] = $department_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM doctor_info $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_doctors = $stmt->fetchColumn();
$total_pages = ceil($total_doctors / $per_page);

// Get doctors
$sql = "
    SELECT d.*, dl.username, dl.last_login
    FROM doctor_info d
    LEFT JOIN doctor_login dl ON d.doctor_id = dl.doctor_id
    $where_clause
    ORDER BY d.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

// Get departments for filter
$stmt = $db->prepare("SELECT DISTINCT department FROM doctor_info WHERE department IS NOT NULL AND department != ''");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-user-md"></i> Manage Doctors
                    </h1>
                    <p class="text-muted">Add, edit, and manage doctor records</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                    <i class="fas fa-plus"></i> Add New Doctor
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
                        <div class="col-md-3">
                            <label class="form-label">Search Doctors</label>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, ID, or specialization">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department">
                                <option value="all">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>" 
                                            <?php echo $department_filter === $dept ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept); ?>
                                    </option>
                                <?php endforeach; ?>
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
                                <a href="manage_doctors.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctors Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Doctors List 
                        <span class="badge bg-primary"><?php echo $total_doctors; ?> total</span>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm btn-export-csv" 
                                data-table="doctorsTable" data-filename="doctors.csv">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-outline-primary btn-sm btn-print" 
                                data-target="doctorsTable">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="doctorsTable">
                            <thead>
                                <tr>
                                    <th>Doctor ID</th>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Department</th>
                                    <th>Contact</th>
                                    <th>License</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo $doctor['doctor_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($doctor['qualification']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($doctor['department']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($doctor['phone']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($doctor['email']); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($doctor['license_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($doctor['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($doctor['last_login']): ?>
                                                <small class="text-muted">
                                                    <?php echo time_ago($doctor['last_login']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Never</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewDoctor(<?php echo $doctor['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" 
                                                        onclick="editDoctor(<?php echo $doctor['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($doctor['is_active']): ?>
                                                    <button class="btn btn-outline-danger btn-delete" 
                                                            onclick="deleteDoctor(<?php echo $doctor['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (empty($doctors)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No doctors found.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Doctors pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&department=<?php echo urlencode($department_filter); ?>">
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

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New Doctor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_doctor">
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
                            <label class="form-label">Specialization</label>
                            <select class="form-select" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="General Medicine">General Medicine</option>
                                <option value="Internal Medicine">Internal Medicine</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Orthopedics">Orthopedics</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Gynecology">Gynecology</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Emergency Medicine">Emergency Medicine</option>
                                <option value="Psychiatry">Psychiatry</option>
                                <option value="Radiology">Radiology</option>
                                <option value="Anesthesiology">Anesthesiology</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department" required>
                                <option value="">Select Department</option>
                                <option value="General Medicine">General Medicine</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Gynecology & Obstetrics">Gynecology & Obstetrics</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Radiology">Radiology</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Pharmacy">Pharmacy</option>
                                <option value="Administration">Administration</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Qualification</label>
                            <input type="text" class="form-control" name="qualification" 
                                   placeholder="e.g., MBBS, MD, PhD" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">License Number</label>
                            <input type="text" class="form-control" name="license_number" required>
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
                            <label class="form-label">Hire Date</label>
                            <input type="date" class="form-control date-picker" name="hire_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control validate-phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control validate-email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">National ID</label>
                            <input type="text" class="form-control" name="national_id">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
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
                        <i class="fas fa-save"></i> Add Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDoctor(id) {
    window.open(`view_doctor.php?id=${id}`, '_blank');
}

function editDoctor(id) {
    window.location.href = `edit_doctor.php?id=${id}`;
}

function deleteDoctor(id) {
    if (confirm('Are you sure you want to deactivate this doctor?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_doctor">
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
        const username = 'dr.' + firstName.charAt(0) + lastName + Math.floor(Math.random() * 100);
        document.querySelector('input[name="username"]').value = username;
    }
}
</script>

<?php include '../includes/footer.php'; ?>