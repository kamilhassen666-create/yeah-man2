<?php
require_once '../includes/config.php';
check_session('admin');

$page_title = 'Manage Staff';
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_staff') {
        $staff_id = generate_id('STF');
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $position = sanitize_input($_POST['position']);
        $department = sanitize_input($_POST['department']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $national_id = sanitize_input($_POST['national_id']);
        $hire_date = $_POST['hire_date'];
        $salary = $_POST['salary'];
        $username = sanitize_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $db->beginTransaction();
            
            // Insert staff info
            $stmt = $db->prepare("
                INSERT INTO staff_info (
                    staff_id, first_name, last_name, position, department, 
                    phone, email, address, date_of_birth, gender, 
                    national_id, hire_date, salary
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $staff_id, $first_name, $last_name, $position, $department,
                $phone, $email, $address, $date_of_birth, $gender,
                $national_id, $hire_date, $salary
            ]);
            
            // Insert login credentials
            $stmt = $db->prepare("
                INSERT INTO staff_login (staff_id, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$staff_id, $username, $password]);
            
            $db->commit();
            log_system_action($_SESSION['user_id'], 'admin', "Added new staff: $staff_id");
            $success_message = "Staff member added successfully! Staff ID: $staff_id";
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Error adding staff: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_staff') {
        $id = $_POST['id'];
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $position = sanitize_input($_POST['position']);
        $department = sanitize_input($_POST['department']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $salary = $_POST['salary'];
        
        try {
            $stmt = $db->prepare("
                UPDATE staff_info SET 
                first_name = ?, last_name = ?, position = ?, department = ?, 
                phone = ?, email = ?, address = ?, salary = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $first_name, $last_name, $position, $department,
                $phone, $email, $address, $salary, $id
            ]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Updated staff ID: $id");
            $success_message = "Staff member updated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error updating staff: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete_staff') {
        $id = $_POST['id'];
        
        try {
            $stmt = $db->prepare("UPDATE staff_info SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Deactivated staff ID: $id");
            $success_message = "Staff member deactivated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error deactivating staff: " . $e->getMessage();
        }
    }
}

// Get staff with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$department_filter = $_GET['department'] ?? 'all';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR staff_id LIKE ? OR position LIKE ?)";
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
$count_sql = "SELECT COUNT(*) FROM staff_info $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_staff = $stmt->fetchColumn();
$total_pages = ceil($total_staff / $per_page);

// Get staff
$sql = "
    SELECT s.*, sl.username, sl.last_login
    FROM staff_info s
    LEFT JOIN staff_login sl ON s.staff_id = sl.staff_id
    $where_clause
    ORDER BY s.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$staff_members = $stmt->fetchAll();

// Get departments for filter
$stmt = $db->prepare("SELECT DISTINCT department FROM staff_info WHERE department IS NOT NULL AND department != ''");
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
                        <i class="fas fa-user-nurse"></i> Manage Staff
                    </h1>
                    <p class="text-muted">Add, edit, and manage staff members</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="fas fa-plus"></i> Add New Staff
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
                            <label class="form-label">Search Staff</label>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, ID, or position">
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
                                <a href="manage_staff.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Staff List 
                        <span class="badge bg-primary"><?php echo $total_staff; ?> total</span>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm btn-export-csv" 
                                data-table="staffTable" data-filename="staff.csv">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-outline-primary btn-sm btn-print" 
                                data-target="staffTable">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="staffTable">
                            <thead>
                                <tr>
                                    <th>Staff ID</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Contact</th>
                                    <th>Hire Date</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo $staff['staff_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($staff['gender']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($staff['position']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($staff['department']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($staff['phone']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($staff['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($staff['hire_date']): ?>
                                                <?php echo date('Y-m-d', strtotime($staff['hire_date'])); ?>
                                            <?php else: ?>
                                                <small class="text-muted">Not set</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($staff['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($staff['last_login']): ?>
                                                <small class="text-muted">
                                                    <?php echo time_ago($staff['last_login']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Never</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewStaff(<?php echo $staff['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" 
                                                        onclick="editStaff(<?php echo $staff['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($staff['is_active']): ?>
                                                    <button class="btn btn-outline-danger btn-delete" 
                                                            onclick="deleteStaff(<?php echo $staff['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (empty($staff_members)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-nurse fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No staff members found.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Staff pagination">
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

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New Staff
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_staff">
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
                            <label class="form-label">Position</label>
                            <select class="form-select" name="position" required>
                                <option value="">Select Position</option>
                                <option value="Head Nurse">Head Nurse</option>
                                <option value="Registered Nurse">Registered Nurse</option>
                                <option value="Licensed Practical Nurse">Licensed Practical Nurse</option>
                                <option value="Medical Assistant">Medical Assistant</option>
                                <option value="Laboratory Technician">Laboratory Technician</option>
                                <option value="Radiologic Technologist">Radiologic Technologist</option>
                                <option value="Pharmacy Technician">Pharmacy Technician</option>
                                <option value="Medical Records Clerk">Medical Records Clerk</option>
                                <option value="Administrative Assistant">Administrative Assistant</option>
                                <option value="Security Guard">Security Guard</option>
                                <option value="Cleaner">Cleaner</option>
                                <option value="Maintenance Worker">Maintenance Worker</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Nursing">Nursing</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Radiology">Radiology</option>
                                <option value="Pharmacy">Pharmacy</option>
                                <option value="Administration">Administration</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Medical Records">Medical Records</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Security">Security</option>
                                <option value="Housekeeping">Housekeeping</option>
                            </select>
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
                            <label class="form-label">Monthly Salary (ETB)</label>
                            <input type="number" class="form-control" name="salary" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control validate-phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control validate-email" name="email">
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
                        <i class="fas fa-save"></i> Add Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewStaff(id) {
    window.open(`view_staff.php?id=${id}`, '_blank');
}

function editStaff(id) {
    window.location.href = `edit_staff.php?id=${id}`;
}

function deleteStaff(id) {
    if (confirm('Are you sure you want to deactivate this staff member?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_staff">
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