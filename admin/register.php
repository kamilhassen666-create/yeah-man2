<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireLogin(['admin']);

$database = new Database();
$db = $database->getConnection();

$userType = isset($_GET['type']) ? $_GET['type'] : 'patient';
$validTypes = ['patient', 'doctor', 'staff'];

if (!in_array($userType, $validTypes)) {
    $userType = 'patient';
}

$error = '';
$success = '';

// Get hospitals for dropdown
try {
    $stmt = $db->query("SELECT id, name FROM hospital ORDER BY name");
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $hospitals = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [];
    $registrationType = $_POST['user_type'];
    
    // Common fields
    $userData['ssn'] = trim($_POST['ssn']);
    $userData['first_name'] = trim($_POST['first_name']);
    $userData['last_name'] = trim($_POST['last_name']);
    $userData['email'] = trim($_POST['email']);
    $userData['phone'] = trim($_POST['phone']);
    $userData['address'] = trim($_POST['address']);
    $userData['user_id'] = trim($_POST['user_id']);
    $userData['password'] = $_POST['password'];
    
    // Validation
    if (empty($userData['ssn']) || empty($userData['first_name']) || empty($userData['last_name']) || 
        empty($userData['user_id']) || empty($userData['password'])) {
        $error = 'Please fill in all required fields';
    } else {
        // Type-specific fields
        switch ($registrationType) {
            case 'patient':
                $userData['date_of_birth'] = $_POST['date_of_birth'];
                $userData['gender'] = $_POST['gender'];
                $userData['emergency_contact'] = trim($_POST['emergency_contact']);
                $userData['emergency_phone'] = trim($_POST['emergency_phone']);
                $userData['blood_type'] = $_POST['blood_type'];
                $userData['allergies'] = trim($_POST['allergies']);
                break;
                
            case 'doctor':
                $userData['specialization'] = trim($_POST['specialization']);
                $userData['license_number'] = trim($_POST['license_number']);
                $userData['hospital_id'] = $_POST['hospital_id'];
                break;
                
            case 'staff':
                $userData['position'] = trim($_POST['position']);
                $userData['hospital_id'] = $_POST['hospital_id'];
                break;
        }
        
        // Register user
        $result = $auth->registerUser($userData, $registrationType);
        
        if ($result['success']) {
            $success = $result['message'];
            // Clear form data
            $_POST = [];
        } else {
            $error = $result['message'];
        }
    }
}

function getUserTypeDisplay($type) {
    switch ($type) {
        case 'patient': return 'Patient';
        case 'doctor': return 'Doctor';
        case 'staff': return 'Medical Staff';
        default: return 'User';
    }
}

function getUserTypeIcon($type) {
    switch ($type) {
        case 'patient': return 'fas fa-user-injured';
        case 'doctor': return 'fas fa-user-md';
        case 'staff': return 'fas fa-user-nurse';
        default: return 'fas fa-user';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register <?php echo getUserTypeDisplay($userType); ?> - Goba Hospital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <i class="fas fa-hospital"></i>
                    <span>Goba Hospital - Admin</span>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link">Register Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-users.php" class="nav-link">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/logout.php" class="nav-link">Logout</a>
                    </li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="dashboard">
        <div class="container">
            <!-- Page Header -->
            <div class="dashboard-header">
                <div class="user-info">
                    <div class="user-details">
                        <h2>Register New <?php echo getUserTypeDisplay($userType); ?></h2>
                        <p>Add a new <?php echo strtolower(getUserTypeDisplay($userType)); ?> to the system</p>
                    </div>
                    <div class="user-actions">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Type Selector -->
            <div class="table-container" style="margin-bottom: 2rem;">
                <div class="table-header">
                    <h3>Select User Type</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php foreach ($validTypes as $type): ?>
                            <a href="?type=<?php echo $type; ?>" 
                               class="btn <?php echo $type === $userType ? 'btn-primary' : 'btn-secondary'; ?>">
                                <i class="<?php echo getUserTypeIcon($type); ?>"></i> 
                                <?php echo getUserTypeDisplay($type); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="table-container">
                <div class="table-header">
                    <h3>
                        <i class="<?php echo getUserTypeIcon($userType); ?>"></i>
                        Register New <?php echo getUserTypeDisplay($userType); ?>
                    </h3>
                </div>
                <div style="padding: 2rem;">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="registration-form">
                        <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($userType); ?>">
                        
                        <!-- Common Fields -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <div class="form-group">
                                <label for="ssn" class="form-label">
                                    SSN/ID Number <span style="color: var(--danger);">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="ssn" 
                                    name="ssn" 
                                    class="form-control" 
                                    placeholder="National ID, Passport, or Birth Certificate Number"
                                    value="<?php echo isset($_POST['ssn']) ? htmlspecialchars($_POST['ssn']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="user_id" class="form-label">
                                    User ID <span style="color: var(--danger);">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="user_id" 
                                    name="user_id" 
                                    class="form-control" 
                                    placeholder="Username for login"
                                    value="<?php echo isset($_POST['user_id']) ? htmlspecialchars($_POST['user_id']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="first_name" class="form-label">
                                    First Name <span style="color: var(--danger);">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    class="form-control" 
                                    placeholder="Enter first name"
                                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="last_name" class="form-label">
                                    Last Name <span style="color: var(--danger);">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    class="form-control" 
                                    placeholder="Enter last name"
                                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-control" 
                                    placeholder="Enter email address"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    class="form-control" 
                                    placeholder="+251 or 09XXXXXXXX"
                                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                >
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="address" class="form-label">Address</label>
                                <textarea 
                                    id="address" 
                                    name="address" 
                                    class="form-control" 
                                    placeholder="Enter full address"
                                    rows="3"
                                ><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Password <span style="color: var(--danger);">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control" 
                                    placeholder="Enter password (min 6 characters)"
                                    required
                                    minlength="6"
                                >
                            </div>
                        </div>

                        <!-- Type-specific Fields -->
                        <?php if ($userType === 'patient'): ?>
                            <hr style="margin: 2rem 0;">
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Patient Information</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                                <div class="form-group">
                                    <label for="date_of_birth" class="form-label">
                                        Date of Birth <span style="color: var(--danger);">*</span>
                                    </label>
                                    <input 
                                        type="date" 
                                        id="date_of_birth" 
                                        name="date_of_birth" 
                                        class="form-control" 
                                        value="<?php echo isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : ''; ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        Gender <span style="color: var(--danger);">*</span>
                                    </label>
                                    <select id="gender" name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="blood_type" class="form-label">Blood Type</label>
                                    <select id="blood_type" name="blood_type" class="form-control">
                                        <option value="">Select Blood Type</option>
                                        <option value="A+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'A+') ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'A-') ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'B+') ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'B-') ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                        <option value="O+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'O+') ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] === 'O-') ? 'selected' : ''; ?>>O-</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                                    <input 
                                        type="text" 
                                        id="emergency_contact" 
                                        name="emergency_contact" 
                                        class="form-control" 
                                        placeholder="Emergency contact person"
                                        value="<?php echo isset($_POST['emergency_contact']) ? htmlspecialchars($_POST['emergency_contact']) : ''; ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                                    <input 
                                        type="tel" 
                                        id="emergency_phone" 
                                        name="emergency_phone" 
                                        class="form-control" 
                                        placeholder="Emergency contact phone"
                                        value="<?php echo isset($_POST['emergency_phone']) ? htmlspecialchars($_POST['emergency_phone']) : ''; ?>"
                                    >
                                </div>

                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="allergies" class="form-label">Known Allergies</label>
                                    <textarea 
                                        id="allergies" 
                                        name="allergies" 
                                        class="form-control" 
                                        placeholder="List any known allergies"
                                        rows="3"
                                    ><?php echo isset($_POST['allergies']) ? htmlspecialchars($_POST['allergies']) : ''; ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($userType === 'doctor'): ?>
                            <hr style="margin: 2rem 0;">
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Doctor Information</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                                <div class="form-group">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <input 
                                        type="text" 
                                        id="specialization" 
                                        name="specialization" 
                                        class="form-control" 
                                        placeholder="e.g., Cardiology, Pediatrics"
                                        value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="license_number" class="form-label">Medical License Number</label>
                                    <input 
                                        type="text" 
                                        id="license_number" 
                                        name="license_number" 
                                        class="form-control" 
                                        placeholder="Enter license number"
                                        value="<?php echo isset($_POST['license_number']) ? htmlspecialchars($_POST['license_number']) : ''; ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="hospital_id" class="form-label">Hospital</label>
                                    <select id="hospital_id" name="hospital_id" class="form-control">
                                        <option value="">Select Hospital</option>
                                        <?php foreach ($hospitals as $hospital): ?>
                                            <option value="<?php echo $hospital['id']; ?>" 
                                                <?php echo (isset($_POST['hospital_id']) && $_POST['hospital_id'] == $hospital['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hospital['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($userType === 'staff'): ?>
                            <hr style="margin: 2rem 0;">
                            <h4 style="color: var(--primary-green); margin-bottom: 1rem;">Staff Information</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                                <div class="form-group">
                                    <label for="position" class="form-label">Position/Role</label>
                                    <input 
                                        type="text" 
                                        id="position" 
                                        name="position" 
                                        class="form-control" 
                                        placeholder="e.g., Nurse, Lab Technician"
                                        value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="hospital_id" class="form-label">Hospital</label>
                                    <select id="hospital_id" name="hospital_id" class="form-control">
                                        <option value="">Select Hospital</option>
                                        <?php foreach ($hospitals as $hospital): ?>
                                            <option value="<?php echo $hospital['id']; ?>" 
                                                <?php echo (isset($_POST['hospital_id']) && $_POST['hospital_id'] == $hospital['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hospital['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Register <?php echo getUserTypeDisplay($userType); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-generate user ID from name
        document.getElementById('first_name').addEventListener('input', generateUserId);
        document.getElementById('last_name').addEventListener('input', generateUserId);
        
        function generateUserId() {
            const firstName = document.getElementById('first_name').value.toLowerCase();
            const lastName = document.getElementById('last_name').value.toLowerCase();
            const userIdField = document.getElementById('user_id');
            
            if (firstName && lastName && !userIdField.value) {
                const userId = firstName.charAt(0) + lastName + Math.floor(Math.random() * 100);
                userIdField.value = userId;
            }
        }
        
        // Form submission loading state
        document.querySelector('.registration-form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>