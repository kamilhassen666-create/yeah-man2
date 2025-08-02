<?php
require_once '../includes/config.php';
check_session('admin');

$page_title = 'Manage External Offices';
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_office') {
        $office_id = generate_id('EXT');
        $office_name = sanitize_input($_POST['office_name']);
        $hospital_name = sanitize_input($_POST['hospital_name']);
        $region = sanitize_input($_POST['region']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $username = sanitize_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $db->beginTransaction();
            
            // Insert external office info
            $stmt = $db->prepare("
                INSERT INTO external_office (
                    office_id, office_name, hospital_name, region, 
                    contact_person, phone, email, address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $office_id, $office_name, $hospital_name, $region,
                $contact_person, $phone, $email, $address
            ]);
            
            // Insert login credentials
            $stmt = $db->prepare("
                INSERT INTO external_login (office_id, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$office_id, $username, $password]);
            
            $db->commit();
            log_system_action($_SESSION['user_id'], 'admin', "Added new external office: $office_id");
            $success_message = "External office added successfully! Office ID: $office_id";
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Error adding external office: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_office') {
        $id = $_POST['id'];
        $office_name = sanitize_input($_POST['office_name']);
        $hospital_name = sanitize_input($_POST['hospital_name']);
        $region = sanitize_input($_POST['region']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        
        try {
            $stmt = $db->prepare("
                UPDATE external_office SET 
                office_name = ?, hospital_name = ?, region = ?, contact_person = ?, 
                phone = ?, email = ?, address = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $office_name, $hospital_name, $region, $contact_person,
                $phone, $email, $address, $id
            ]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Updated external office ID: $id");
            $success_message = "External office updated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error updating external office: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete_office') {
        $id = $_POST['id'];
        
        try {
            $stmt = $db->prepare("UPDATE external_office SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            log_system_action($_SESSION['user_id'], 'admin', "Deactivated external office ID: $id");
            $success_message = "External office deactivated successfully!";
            
        } catch (PDOException $e) {
            $error_message = "Error deactivating external office: " . $e->getMessage();
        }
    }
}

// Get external offices with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$region_filter = $_GET['region'] ?? 'all';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(office_name LIKE ? OR hospital_name LIKE ? OR office_id LIKE ? OR contact_person LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter !== 'all') {
    $where_conditions[] = "is_active = ?";
    $params[] = $status_filter === 'active' ? 1 : 0;
}

if ($region_filter !== 'all') {
    $where_conditions[] = "region = ?";
    $params[] = $region_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM external_office $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_offices = $stmt->fetchColumn();
$total_pages = ceil($total_offices / $per_page);

// Get external offices
$sql = "
    SELECT e.*, el.username, el.last_login
    FROM external_office e
    LEFT JOIN external_login el ON e.office_id = el.office_id
    $where_clause
    ORDER BY e.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$external_offices = $stmt->fetchAll();

// Get regions for filter
$stmt = $db->prepare("SELECT DISTINCT region FROM external_office WHERE region IS NOT NULL AND region != ''");
$stmt->execute();
$regions = $stmt->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-building"></i> Manage External Offices
                    </h1>
                    <p class="text-muted">Add, edit, and manage external health offices</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficeModal">
                    <i class="fas fa-plus"></i> Add New Office
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
                            <label class="form-label">Search Offices</label>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, ID, or contact">
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
                            <label class="form-label">Region</label>
                            <select class="form-select" name="region">
                                <option value="all">All Regions</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?php echo htmlspecialchars($region); ?>" 
                                            <?php echo $region_filter === $region ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($region); ?>
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
                                <a href="manage_external.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- External Offices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> External Offices List 
                        <span class="badge bg-primary"><?php echo $total_offices; ?> total</span>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm btn-export-csv" 
                                data-table="officesTable" data-filename="external_offices.csv">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-outline-primary btn-sm btn-print" 
                                data-target="officesTable">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="officesTable">
                            <thead>
                                <tr>
                                    <th>Office ID</th>
                                    <th>Office Name</th>
                                    <th>Hospital</th>
                                    <th>Region</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($external_offices as $office): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo $office['office_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($office['office_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($office['hospital_name']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($office['region']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($office['contact_person']); ?></strong><br>
                                            <?php echo htmlspecialchars($office['phone']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($office['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($office['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($office['last_login']): ?>
                                                <small class="text-muted">
                                                    <?php echo time_ago($office['last_login']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Never</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewOffice(<?php echo $office['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" 
                                                        onclick="editOffice(<?php echo $office['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($office['is_active']): ?>
                                                    <button class="btn btn-outline-danger btn-delete" 
                                                            onclick="deleteOffice(<?php echo $office['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (empty($external_offices)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No external offices found.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="External offices pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&region=<?php echo urlencode($region_filter); ?>">
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

<!-- Add Office Modal -->
<div class="modal fade" id="addOfficeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New External Office
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_office">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Office Name</label>
                            <input type="text" class="form-control" name="office_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hospital Name</label>
                            <input type="text" class="form-control" name="hospital_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Region</label>
                            <select class="form-select" name="region" required>
                                <option value="">Select Region</option>
                                <option value="Addis Ababa">Addis Ababa</option>
                                <option value="Amhara">Amhara</option>
                                <option value="Oromia">Oromia</option>
                                <option value="Tigray">Tigray</option>
                                <option value="SNNP">SNNP</option>
                                <option value="Somali">Somali</option>
                                <option value="Afar">Afar</option>
                                <option value="Benishangul-Gumuz">Benishangul-Gumuz</option>
                                <option value="Gambela">Gambela</option>
                                <option value="Harari">Harari</option>
                                <option value="Dire Dawa">Dire Dawa</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control validate-phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control validate-email" name="email" required>
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
                        <i class="fas fa-save"></i> Add Office
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewOffice(id) {
    window.open(`view_office.php?id=${id}`, '_blank');
}

function editOffice(id) {
    window.location.href = `edit_office.php?id=${id}`;
}

function deleteOffice(id) {
    if (confirm('Are you sure you want to deactivate this external office?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_office">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-generate username based on office name
document.querySelector('input[name="office_name"]').addEventListener('input', generateUsername);

function generateUsername() {
    const officeName = document.querySelector('input[name="office_name"]').value.toLowerCase();
    
    if (officeName) {
        const username = officeName.replace(/\s+/g, '').substring(0, 8) + Math.floor(Math.random() * 100);
        document.querySelector('input[name="username"]').value = username;
    }
}
</script>

<?php include '../includes/footer.php'; ?>