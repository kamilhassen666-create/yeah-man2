<?php
require_once '../config/database.php';

/**
 * Authentication class for Goba Hospital Management System
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Login user based on user type
     */
    public function login($userType, $userId, $password) {
        try {
            $user = $this->getUserByType($userType, $userId);
            
            if ($user && verifyPassword($password, $user['password'])) {
                $this->createSession($user, $userType);
                $this->updateLastLogin($userType, $userId);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by type and user ID
     */
    private function getUserByType($userType, $userId) {
        $tables = [
            'patient' => 'patient_login',
            'doctor' => 'doctor_login',
            'staff' => 'staff_login',
            'admin' => 'admin_login',
            'external' => 'external_office_login'
        ];
        
        if (!isset($tables[$userType])) {
            return false;
        }
        
        $table = $tables[$userType];
        $sql = "SELECT * FROM {$table} WHERE user_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Create user session
     */
    private function createSession($user, $userType) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $userType;
        $_SESSION['login_time'] = time();
        
        // Get additional user info based on type
        switch ($userType) {
            case 'patient':
                $patientInfo = $this->getPatientInfo($user['patient_ssn']);
                $_SESSION['ssn'] = $user['patient_ssn'];
                $_SESSION['full_name'] = $patientInfo['first_name'] . ' ' . $patientInfo['last_name'];
                break;
                
            case 'doctor':
                $doctorInfo = $this->getDoctorInfo($user['doctor_ssn']);
                $_SESSION['ssn'] = $user['doctor_ssn'];
                $_SESSION['full_name'] = $doctorInfo['first_name'] . ' ' . $doctorInfo['last_name'];
                $_SESSION['specialization'] = $doctorInfo['specialization'];
                break;
                
            case 'staff':
                $staffInfo = $this->getStaffInfo($user['staff_ssn']);
                $_SESSION['ssn'] = $user['staff_ssn'];
                $_SESSION['full_name'] = $staffInfo['first_name'] . ' ' . $staffInfo['last_name'];
                $_SESSION['position'] = $staffInfo['position'];
                break;
                
            case 'admin':
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                break;
                
            case 'external':
                $_SESSION['office_id'] = $user['office_id'];
                $_SESSION['full_name'] = $user['full_name'];
                break;
        }
    }
    
    /**
     * Get patient information
     */
    private function getPatientInfo($ssn) {
        $sql = "SELECT * FROM patient WHERE ssn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ssn]);
        return $stmt->fetch();
    }
    
    /**
     * Get doctor information
     */
    private function getDoctorInfo($ssn) {
        $sql = "SELECT * FROM doctor WHERE ssn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ssn]);
        return $stmt->fetch();
    }
    
    /**
     * Get staff information
     */
    private function getStaffInfo($ssn) {
        $sql = "SELECT * FROM medical_staff WHERE ssn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ssn]);
        return $stmt->fetch();
    }
    
    /**
     * Update last login time
     */
    private function updateLastLogin($userType, $userId) {
        $tables = [
            'patient' => 'patient_login',
            'doctor' => 'doctor_login',
            'staff' => 'staff_login',
            'admin' => 'admin_login',
            'external' => 'external_office_login'
        ];
        
        if (isset($tables[$userType])) {
            $table = $tables[$userType];
            $sql = "UPDATE {$table} SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        }
    }
    
    /**
     * Check if session is valid
     */
    public function isSessionValid() {
        if (!isLoggedIn()) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Destroy session
        session_destroy();
        session_start();
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        return true;
    }
    
    /**
     * Check user permission for specific action
     */
    public function hasPermission($requiredUserType) {
        if (!$this->isSessionValid()) {
            return false;
        }
        
        $currentUserType = $_SESSION['user_type'];
        
        // Admin can access everything
        if ($currentUserType === 'admin') {
            return true;
        }
        
        // Check if user type matches required type
        return $currentUserType === $requiredUserType;
    }
    
    /**
     * Require login for page access
     */
    public function requireLogin($userType = null) {
        if (!$this->isSessionValid()) {
            redirect('../index.php?error=login_required');
        }
        
        if ($userType && !$this->hasPermission($userType)) {
            redirect('../index.php?error=access_denied');
        }
    }
    
    /**
     * Register new user
     */
    public function register($userType, $userData) {
        try {
            $this->db->beginTransaction();
            
            // Hash password
            $userData['password'] = hashPassword($userData['password']);
            
            switch ($userType) {
                case 'patient':
                    $result = $this->registerPatient($userData);
                    break;
                case 'doctor':
                    $result = $this->registerDoctor($userData);
                    break;
                case 'staff':
                    $result = $this->registerStaff($userData);
                    break;
                case 'admin':
                    $result = $this->registerAdmin($userData);
                    break;
                case 'external':
                    $result = $this->registerExternal($userData);
                    break;
                default:
                    throw new Exception("Invalid user type");
            }
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register patient
     */
    private function registerPatient($data) {
        // Insert into patient table
        $sql = "INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, phone, email, address, city, country, emergency_contact_name, emergency_contact_phone, blood_type, allergies) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $patientResult = $stmt->execute([
            $data['ssn'], $data['first_name'], $data['last_name'], $data['date_of_birth'],
            $data['gender'], $data['phone'], $data['email'], $data['address'], $data['city'],
            $data['country'], $data['emergency_contact_name'], $data['emergency_contact_phone'],
            $data['blood_type'], $data['allergies']
        ]);
        
        // Insert into patient_login table
        $sql = "INSERT INTO patient_login (user_id, password, patient_ssn) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $loginResult = $stmt->execute([$data['user_id'], $data['password'], $data['ssn']]);
        
        return $patientResult && $loginResult;
    }
    
    /**
     * Register doctor
     */
    private function registerDoctor($data) {
        // Insert into doctor table
        $sql = "INSERT INTO doctor (ssn, first_name, last_name, date_of_birth, gender, phone, email, address, city, country, specialization, license_number, years_experience, hospital_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $doctorResult = $stmt->execute([
            $data['ssn'], $data['first_name'], $data['last_name'], $data['date_of_birth'],
            $data['gender'], $data['phone'], $data['email'], $data['address'], $data['city'],
            $data['country'], $data['specialization'], $data['license_number'], 
            $data['years_experience'], $data['hospital_id']
        ]);
        
        // Insert into doctor_login table
        $sql = "INSERT INTO doctor_login (user_id, password, doctor_ssn) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $loginResult = $stmt->execute([$data['user_id'], $data['password'], $data['ssn']]);
        
        return $doctorResult && $loginResult;
    }
    
    /**
     * Register staff
     */
    private function registerStaff($data) {
        // Insert into medical_staff table
        $sql = "INSERT INTO medical_staff (ssn, first_name, last_name, date_of_birth, gender, phone, email, address, city, country, position, department, hospital_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $staffResult = $stmt->execute([
            $data['ssn'], $data['first_name'], $data['last_name'], $data['date_of_birth'],
            $data['gender'], $data['phone'], $data['email'], $data['address'], $data['city'],
            $data['country'], $data['position'], $data['department'], $data['hospital_id']
        ]);
        
        // Insert into staff_login table
        $sql = "INSERT INTO staff_login (user_id, password, staff_ssn) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $loginResult = $stmt->execute([$data['user_id'], $data['password'], $data['ssn']]);
        
        return $staffResult && $loginResult;
    }
    
    /**
     * Register admin
     */
    private function registerAdmin($data) {
        $sql = "INSERT INTO admin_login (user_id, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['user_id'], $data['password'], $data['full_name'], 
            $data['email'], $data['role'] ?? 'Admin'
        ]);
    }
    
    /**
     * Register external office
     */
    private function registerExternal($data) {
        // Insert into external_health_office table first
        $sql = "INSERT INTO external_health_office (office_name, hospital_id, contact_person, email, phone, address) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $officeResult = $stmt->execute([
            $data['office_name'], $data['hospital_id'], $data['contact_person'],
            $data['email'], $data['phone'], $data['address']
        ]);
        
        if ($officeResult) {
            $officeId = $this->db->lastInsertId();
            
            // Insert into external_office_login table
            $sql = "INSERT INTO external_office_login (user_id, password, office_id, full_name) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $loginResult = $stmt->execute([
                $data['user_id'], $data['password'], $officeId, $data['full_name']
            ]);
            
            return $loginResult;
        }
        
        return false;
    }
}

// Create global auth instance
$auth = new Auth();
?>