<?php
session_start();
require_once '../config/database.php';

// Authentication functions
function login_user($username, $password, $user_type) {
    switch($user_type) {
        case 'patient':
            return login_patient($username, $password);
        case 'doctor':
            return login_doctor($username, $password);
        case 'staff':
            return login_staff($username, $password);
        case 'admin':
            return login_admin($username, $password);
        case 'external':
            return login_external($username, $password);
        default:
            return false;
    }
}

function login_patient($username, $password) {
    $query = "SELECT pl.*, p.first_name, p.last_name, p.email 
              FROM patient_login pl 
              JOIN patient p ON pl.patient_ssn = p.ssn 
              WHERE pl.username = ? AND pl.is_active = 1";
    $user = getRow($query, [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['patient_ssn'];
        $_SESSION['user_type'] = 'patient';
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        // Update last login
        executeQuery("UPDATE patient_login SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Log the action
        log_audit($user['patient_ssn'], 'Patient', 'Login', 'patient_login', $user['id']);
        
        return true;
    }
    return false;
}

function login_doctor($username, $password) {
    $query = "SELECT dl.*, d.first_name, d.last_name, d.email, d.specialization 
              FROM doctor_login dl 
              JOIN doctor d ON dl.doctor_ssn = d.ssn 
              WHERE dl.username = ? AND dl.is_active = 1";
    $user = getRow($query, [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['doctor_ssn'];
        $_SESSION['user_type'] = 'doctor';
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['specialization'] = $user['specialization'];
        
        // Update last login
        executeQuery("UPDATE doctor_login SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Log the action
        log_audit($user['doctor_ssn'], 'Doctor', 'Login', 'doctor_login', $user['id']);
        
        return true;
    }
    return false;
}

function login_staff($username, $password) {
    $query = "SELECT sl.*, s.first_name, s.last_name, s.email, s.position 
              FROM staff_login sl 
              JOIN medical_staff s ON sl.staff_ssn = s.ssn 
              WHERE sl.username = ? AND sl.is_active = 1";
    $user = getRow($query, [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['staff_ssn'];
        $_SESSION['user_type'] = 'staff';
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['position'] = $user['position'];
        
        // Update last login
        executeQuery("UPDATE staff_login SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Log the action
        log_audit($user['staff_ssn'], 'Staff', 'Login', 'staff_login', $user['id']);
        
        return true;
    }
    return false;
}

function login_admin($username, $password) {
    $query = "SELECT * FROM admin_login WHERE username = ? AND is_active = 1";
    $user = getRow($query, [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['username'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        executeQuery("UPDATE admin_login SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        // Log the action
        log_audit($user['username'], 'Admin', 'Login', 'admin_login', $user['id']);
        
        return true;
    }
    return false;
}

function login_external($username, $password) {
    $query = "SELECT e.*, d.first_name, d.last_name 
              FROM external_health_office e 
              JOIN doctor d ON e.doctor_ssn = d.ssn 
              WHERE e.username = ? AND e.is_active = 1";
    $user = getRow($query, [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['doctor_ssn'];
        $_SESSION['user_type'] = 'external';
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['office_name'] = $user['office_name'];
        
        // Log the action
        log_audit($user['doctor_ssn'], 'External', 'Login', 'external_health_office', $user['id']);
        
        return true;
    }
    return false;
}

function logout_user() {
    if (isset($_SESSION['user_id'])) {
        log_audit($_SESSION['user_id'], $_SESSION['user_type'], 'Logout', null, null);
    }
    session_destroy();
    header('Location: ../index.html');
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function require_login($allowed_types = []) {
    if (!is_logged_in()) {
        header('Location: ../index.html');
        exit();
    }
    
    if (!empty($allowed_types) && !in_array($_SESSION['user_type'], $allowed_types)) {
        header('Location: ../index.html');
        exit();
    }
}

// Validation functions
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone);
}

function validate_ssn($ssn) {
    return !empty($ssn) && strlen($ssn) >= 5;
}

function validate_required_fields($fields) {
    foreach ($fields as $field => $value) {
        if (empty(trim($value))) {
            return "Field '$field' is required.";
        }
    }
    return true;
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Utility functions
function generate_reference_number($prefix = 'REF') {
    return $prefix . date('Ymd') . sprintf('%04d', rand(1000, 9999));
}

function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

function calculate_age($birth_date) {
    $birth = new DateTime($birth_date);
    $today = new DateTime();
    return $birth->diff($today)->y;
}

function get_user_info($user_id, $user_type) {
    switch($user_type) {
        case 'patient':
            return getRow("SELECT * FROM patient WHERE ssn = ?", [$user_id]);
        case 'doctor':
            return getRow("SELECT d.*, h.name as hospital_name FROM doctor d LEFT JOIN hospital h ON d.hospital_id = h.id WHERE d.ssn = ?", [$user_id]);
        case 'staff':
            return getRow("SELECT s.*, h.name as hospital_name FROM medical_staff s LEFT JOIN hospital h ON s.hospital_id = h.id WHERE s.ssn = ?", [$user_id]);
        case 'admin':
            return getRow("SELECT * FROM admin_login WHERE username = ?", [$user_id]);
        default:
            return false;
    }
}

// Search functions
function search_patients($search_term, $search_type = 'name') {
    switch($search_type) {
        case 'ssn':
            $query = "SELECT * FROM patient WHERE ssn LIKE ?";
            $params = ["%$search_term%"];
            break;
        case 'name':
            $query = "SELECT * FROM patient WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
            $params = ["%$search_term%"];
            break;
        case 'phone':
            $query = "SELECT * FROM patient WHERE phone LIKE ?";
            $params = ["%$search_term%"];
            break;
        default:
            $query = "SELECT * FROM patient WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR ssn LIKE ? OR phone LIKE ?";
            $params = ["%$search_term%", "%$search_term%", "%$search_term%"];
    }
    
    return getRows($query, $params);
}

function search_records($patient_ssn, $record_type, $date_from = null, $date_to = null) {
    $conditions = ["patient_ssn = ?"];
    $params = [$patient_ssn];
    
    if ($date_from) {
        $conditions[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = implode(' AND ', $conditions);
    
    switch($record_type) {
        case 'consultation':
            $query = "SELECT c.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name 
                      FROM consultation c 
                      JOIN doctor d ON c.doctor_ssn = d.ssn 
                      WHERE $where_clause 
                      ORDER BY c.consultation_date DESC";
            break;
        case 'operation':
            $query = "SELECT o.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name 
                      FROM operation o 
                      JOIN doctor d ON o.doctor_ssn = d.ssn 
                      WHERE $where_clause 
                      ORDER BY o.operation_date DESC";
            break;
        case 'diagnosis':
            $query = "SELECT dia.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name 
                      FROM diagnosis dia 
                      JOIN doctor d ON dia.doctor_ssn = d.ssn 
                      WHERE $where_clause 
                      ORDER BY dia.diagnosis_date DESC";
            break;
        case 'medication':
            $query = "SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name 
                      FROM medical_administration m 
                      JOIN medical_staff s ON m.staff_ssn = s.ssn 
                      WHERE $where_clause 
                      ORDER BY m.administration_date DESC";
            break;
        default:
            return false;
    }
    
    return getRows($query, $params);
}

// File upload functions
function upload_file($file, $patient_ssn, $uploader_id, $uploader_type, $category = 'Other') {
    $upload_dir = '../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types)];
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        return ['success' => false, 'message' => 'File size too large. Maximum size is 10MB.'];
    }
    
    $file_name = uniqid() . '_' . $file['name'];
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $query = "INSERT INTO file_uploads (patient_ssn, uploaded_by, uploader_type, file_name, file_path, file_type, file_size, category) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = executeQuery($query, [
            $patient_ssn, $uploader_id, $uploader_type, $file['name'], 
            $file_path, $file_extension, $file['size'], $category
        ]);
        
        if ($result) {
            log_audit($uploader_id, $uploader_type, 'File Upload', 'file_uploads', getLastInsertId());
            return ['success' => true, 'message' => 'File uploaded successfully', 'file_id' => getLastInsertId()];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

// Audit logging
function log_audit($user_id, $user_type, $action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $query = "INSERT INTO audit_log (user_id, user_type, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($query, [
        $user_id, $user_type, $action, $table_name, $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $ip_address, $user_agent
    ]);
}

// Payment functions
function process_payment($patient_ssn, $payment_type, $amount, $bank_name, $payment_method) {
    $transaction_id = 'TXN' . date('YmdHis') . rand(1000, 9999);
    $reference_number = generate_reference_number('PAY');
    
    $query = "INSERT INTO payment (patient_ssn, payment_type, amount, bank_name, transaction_id, payment_method, payment_date, reference_number) 
              VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
    
    $result = executeQuery($query, [
        $patient_ssn, $payment_type, $amount, $bank_name, $transaction_id, $payment_method, $reference_number
    ]);
    
    if ($result) {
        log_audit($patient_ssn, 'Patient', 'Payment Initiated', 'payment', getLastInsertId());
        return ['success' => true, 'transaction_id' => $transaction_id, 'reference_number' => $reference_number];
    }
    
    return ['success' => false, 'message' => 'Payment processing failed'];
}

// Response functions
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Statistics functions
function get_patient_statistics($patient_ssn) {
    $stats = [];
    
    $stats['consultations'] = getRowCount("SELECT COUNT(*) FROM consultation WHERE patient_ssn = ?", [$patient_ssn]);
    $stats['operations'] = getRowCount("SELECT COUNT(*) FROM operation WHERE patient_ssn = ?", [$patient_ssn]);
    $stats['diagnoses'] = getRowCount("SELECT COUNT(*) FROM diagnosis WHERE patient_ssn = ?", [$patient_ssn]);
    $stats['medications'] = getRowCount("SELECT COUNT(*) FROM medical_administration WHERE patient_ssn = ?", [$patient_ssn]);
    
    return $stats;
}

function get_doctor_statistics($doctor_ssn) {
    $stats = [];
    
    $stats['consultations'] = getRowCount("SELECT COUNT(*) FROM consultation WHERE doctor_ssn = ?", [$doctor_ssn]);
    $stats['operations'] = getRowCount("SELECT COUNT(*) FROM operation WHERE doctor_ssn = ?", [$doctor_ssn]);
    $stats['diagnoses'] = getRowCount("SELECT COUNT(*) FROM diagnosis WHERE doctor_ssn = ?", [$doctor_ssn]);
    $stats['patients'] = getRowCount("SELECT COUNT(DISTINCT patient_ssn) FROM consultation WHERE doctor_ssn = ?", [$doctor_ssn]);
    
    return $stats;
}
?>