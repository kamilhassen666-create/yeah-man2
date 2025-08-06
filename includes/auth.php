<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Login function for different user types
    public function login($userId, $password, $userType) {
        try {
            $table = $this->getLoginTable($userType);
            $stmt = $this->db->prepare("SELECT * FROM $table WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['account_locked']) {
                    return ['success' => false, 'message' => 'Account is locked. Contact administrator.'];
                }
                
                // Reset login attempts on successful login
                $this->resetLoginAttempts($userId, $userType);
                
                // Update last login
                $this->updateLastLogin($userId, $userType);
                
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_type'] = $userType;
                $_SESSION['logged_in'] = true;
                
                // Get additional user info based on type
                $userInfo = $this->getUserInfo($user, $userType);
                $_SESSION['user_info'] = $userInfo;
                
                return ['success' => true, 'message' => 'Login successful', 'redirect' => $this->getRedirectUrl($userType)];
            } else {
                // Increment login attempts
                $this->incrementLoginAttempts($userId, $userType);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    // Logout function
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Check user type
    public function getUserType() {
        return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
    }
    
    // Require login for protected pages
    public function requireLogin($allowedTypes = []) {
        if (!$this->isLoggedIn()) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (!empty($allowedTypes) && !in_array($this->getUserType(), $allowedTypes)) {
            header('Location: ../auth/unauthorized.php');
            exit();
        }
    }
    
    // Get login table based on user type
    private function getLoginTable($userType) {
        switch ($userType) {
            case 'patient': return 'patient_login';
            case 'doctor': return 'doctor_login';
            case 'staff': return 'staff_login';
            case 'admin': return 'admin_login';
            case 'external_office': return 'external_office_login';
            default: throw new Exception('Invalid user type');
        }
    }
    
    // Get redirect URL based on user type
    public function getRedirectUrl($userType) {
        switch ($userType) {
            case 'patient': return '../patient/dashboard.php';
            case 'doctor': return '../doctor/dashboard.php';
            case 'staff': return '../staff/dashboard.php';
            case 'admin': return '../admin/dashboard.php';
            case 'external_office': return '../external/dashboard.php';
            default: return '../index.php';
        }
    }
    
    // Get user information based on type
    private function getUserInfo($loginData, $userType) {
        try {
            switch ($userType) {
                case 'patient':
                    $stmt = $this->db->prepare("SELECT * FROM patient WHERE ssn = ?");
                    $stmt->execute([$loginData['patient_ssn']]);
                    break;
                case 'doctor':
                    $stmt = $this->db->prepare("SELECT * FROM doctor WHERE ssn = ?");
                    $stmt->execute([$loginData['doctor_ssn']]);
                    break;
                case 'staff':
                    $stmt = $this->db->prepare("SELECT * FROM medical_staff WHERE ssn = ?");
                    $stmt->execute([$loginData['staff_ssn']]);
                    break;
                case 'admin':
                    return ['name' => $loginData['admin_name'], 'role' => $loginData['role']];
                case 'external_office':
                    $stmt = $this->db->prepare("SELECT eho.*, d.first_name, d.last_name FROM external_health_office eho LEFT JOIN doctor d ON eol.doctor_ssn = d.ssn WHERE eho.id = ?");
                    $stmt->execute([$loginData['office_id']]);
                    break;
                default:
                    return [];
            }
            
            if (isset($stmt)) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Increment login attempts
    private function incrementLoginAttempts($userId, $userType) {
        try {
            $table = $this->getLoginTable($userType);
            $stmt = $this->db->prepare("UPDATE $table SET login_attempts = login_attempts + 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Lock account after 5 failed attempts
            $stmt = $this->db->prepare("SELECT login_attempts FROM $table WHERE user_id = ?");
            $stmt->execute([$userId]);
            $attempts = $stmt->fetchColumn();
            
            if ($attempts >= 5) {
                $stmt = $this->db->prepare("UPDATE $table SET account_locked = 1 WHERE user_id = ?");
                $stmt->execute([$userId]);
            }
        } catch (Exception $e) {
            // Log error
        }
    }
    
    // Reset login attempts
    private function resetLoginAttempts($userId, $userType) {
        try {
            $table = $this->getLoginTable($userType);
            $stmt = $this->db->prepare("UPDATE $table SET login_attempts = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Log error
        }
    }
    
    // Update last login
    private function updateLastLogin($userId, $userType) {
        try {
            $table = $this->getLoginTable($userType);
            $stmt = $this->db->prepare("UPDATE $table SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Log error
        }
    }
    
    // Register new user (for admin use)
    public function registerUser($userData, $userType) {
        try {
            $this->db->beginTransaction();
            
            // Insert into respective user table first
            $userInserted = $this->insertUserData($userData, $userType);
            
            if ($userInserted) {
                // Insert into login table
                $loginInserted = $this->insertLoginData($userData, $userType);
                
                if ($loginInserted) {
                    $this->db->commit();
                    return ['success' => true, 'message' => 'User registered successfully'];
                }
            }
            
            $this->db->rollback();
            return ['success' => false, 'message' => 'Registration failed'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    // Insert user data into respective table
    private function insertUserData($userData, $userType) {
        switch ($userType) {
            case 'patient':
                $stmt = $this->db->prepare("INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, email, phone, address, emergency_contact, emergency_phone, blood_type, allergies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $userData['ssn'], $userData['first_name'], $userData['last_name'],
                    $userData['date_of_birth'], $userData['gender'], $userData['email'],
                    $userData['phone'], $userData['address'], $userData['emergency_contact'],
                    $userData['emergency_phone'], $userData['blood_type'], $userData['allergies']
                ]);
                
            case 'doctor':
                $stmt = $this->db->prepare("INSERT INTO doctor (ssn, first_name, last_name, specialization, email, phone, address, license_number, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $userData['ssn'], $userData['first_name'], $userData['last_name'],
                    $userData['specialization'], $userData['email'], $userData['phone'],
                    $userData['address'], $userData['license_number'], $userData['hospital_id']
                ]);
                
            case 'staff':
                $stmt = $this->db->prepare("INSERT INTO medical_staff (ssn, first_name, last_name, position, email, phone, address, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $userData['ssn'], $userData['first_name'], $userData['last_name'],
                    $userData['position'], $userData['email'], $userData['phone'],
                    $userData['address'], $userData['hospital_id']
                ]);
                
            default:
                return false;
        }
    }
    
    // Insert login data
    private function insertLoginData($userData, $userType) {
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        switch ($userType) {
            case 'patient':
                $stmt = $this->db->prepare("INSERT INTO patient_login (user_id, password_hash, patient_ssn) VALUES (?, ?, ?)");
                return $stmt->execute([$userData['user_id'], $passwordHash, $userData['ssn']]);
                
            case 'doctor':
                $stmt = $this->db->prepare("INSERT INTO doctor_login (user_id, password_hash, doctor_ssn) VALUES (?, ?, ?)");
                return $stmt->execute([$userData['user_id'], $passwordHash, $userData['ssn']]);
                
            case 'staff':
                $stmt = $this->db->prepare("INSERT INTO staff_login (user_id, password_hash, staff_ssn) VALUES (?, ?, ?)");
                return $stmt->execute([$userData['user_id'], $passwordHash, $userData['ssn']]);
                
            default:
                return false;
        }
    }
}
?>