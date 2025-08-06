<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Patient authentication
    public function loginPatient($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT pl.*, p.first_name, p.last_name, p.email 
                FROM patient_login pl 
                JOIN patient p ON pl.patient_ssn = p.ssn 
                WHERE pl.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastLogin('patient_login', $user['id']);
                $this->setSession('patient', $user);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Patient login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Doctor authentication
    public function loginDoctor($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT dl.*, d.first_name, d.last_name, d.email, d.specialization 
                FROM doctor_login dl 
                JOIN doctor d ON dl.doctor_ssn = d.ssn 
                WHERE dl.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastLogin('doctor_login', $user['id']);
                $this->setSession('doctor', $user);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Doctor login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Staff authentication
    public function loginStaff($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT sl.*, s.first_name, s.last_name, s.email, s.position 
                FROM staff_login sl 
                JOIN medical_staff s ON sl.staff_ssn = s.ssn 
                WHERE sl.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastLogin('staff_login', $user['id']);
                $this->setSession('staff', $user);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Staff login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Admin authentication
    public function loginAdmin($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admin_login WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastLogin('admin_login', $user['id']);
                $this->setSession('admin', $user);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            return false;
        }
    }
    
    // External Health Office authentication
    public function loginExternal($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM external_health_office WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->setSession('external', $user);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("External office login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Set session data
    private function setSession($userType, $userData) {
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_data'] = $userData;
        $_SESSION['login_time'] = time();
    }
    
    // Update last login time
    private function updateLastLogin($table, $userId) {
        try {
            $stmt = $this->db->prepare("UPDATE {$table} SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_type']) && isset($_SESSION['user_id']);
    }
    
    // Get current user type
    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    // Get current user data
    public function getUserData() {
        return $_SESSION['user_data'] ?? null;
    }
    
    // Check user type
    public function isPatient() {
        return $this->getUserType() === 'patient';
    }
    
    public function isDoctor() {
        return $this->getUserType() === 'doctor';
    }
    
    public function isStaff() {
        return $this->getUserType() === 'staff';
    }
    
    public function isAdmin() {
        return $this->getUserType() === 'admin';
    }
    
    public function isExternal() {
        return $this->getUserType() === 'external';
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        session_start();
    }
    
    // Require login for pages
    public function requireLogin($userTypes = []) {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit();
        }
        
        if (!empty($userTypes) && !in_array($this->getUserType(), $userTypes)) {
            header('Location: /unauthorized.php');
            exit();
        }
    }
    
    // Password hashing
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Generate secure random password
    public function generatePassword($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    // Register new patient
    public function registerPatient($patientData, $username, $password) {
        try {
            $this->db->beginTransaction();
            
            // Insert patient
            $stmt = $this->db->prepare("
                INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, email, phone, address, emergency_contact, emergency_phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $patientData['ssn'],
                $patientData['first_name'],
                $patientData['last_name'],
                $patientData['date_of_birth'],
                $patientData['gender'],
                $patientData['email'],
                $patientData['phone'],
                $patientData['address'],
                $patientData['emergency_contact'],
                $patientData['emergency_phone']
            ]);
            
            // Insert login credentials
            $stmt = $this->db->prepare("
                INSERT INTO patient_login (patient_ssn, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $patientData['ssn'],
                $username,
                $this->hashPassword($password)
            ]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Patient registration error: " . $e->getMessage());
            return false;
        }
    }
    
    // Register new doctor
    public function registerDoctor($doctorData, $username, $password) {
        try {
            $this->db->beginTransaction();
            
            // Insert doctor
            $stmt = $this->db->prepare("
                INSERT INTO doctor (ssn, first_name, last_name, specialization, email, phone, address, license_number, hospital_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $doctorData['ssn'],
                $doctorData['first_name'],
                $doctorData['last_name'],
                $doctorData['specialization'],
                $doctorData['email'],
                $doctorData['phone'],
                $doctorData['address'],
                $doctorData['license_number'],
                $doctorData['hospital_id']
            ]);
            
            // Insert login credentials
            $stmt = $this->db->prepare("
                INSERT INTO doctor_login (doctor_ssn, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $doctorData['ssn'],
                $username,
                $this->hashPassword($password)
            ]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Doctor registration error: " . $e->getMessage());
            return false;
        }
    }
    
    // Register new staff
    public function registerStaff($staffData, $username, $password) {
        try {
            $this->db->beginTransaction();
            
            // Insert staff
            $stmt = $this->db->prepare("
                INSERT INTO medical_staff (ssn, first_name, last_name, position, email, phone, address, hospital_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $staffData['ssn'],
                $staffData['first_name'],
                $staffData['last_name'],
                $staffData['position'],
                $staffData['email'],
                $staffData['phone'],
                $staffData['address'],
                $staffData['hospital_id']
            ]);
            
            // Insert login credentials
            $stmt = $this->db->prepare("
                INSERT INTO staff_login (staff_ssn, username, password) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $staffData['ssn'],
                $username,
                $this->hashPassword($password)
            ]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Staff registration error: " . $e->getMessage());
            return false;
        }
    }
}
?>