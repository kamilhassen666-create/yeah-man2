-- Goba Hospital Patient Record Management System Database Schema
-- Created for comprehensive patient, doctor, staff, and admin management

-- Create database
CREATE DATABASE IF NOT EXISTS goba_hospital;
USE goba_hospital;

-- Hospital table
CREATE TABLE hospital (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patient table
CREATE TABLE patient (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    blood_type VARCHAR(5),
    allergies TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor table
CREATE TABLE doctor (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    specialization VARCHAR(255),
    license_number VARCHAR(100) UNIQUE NOT NULL,
    years_experience INT,
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE SET NULL
);

-- Medical staff table
CREATE TABLE medical_staff (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    position VARCHAR(100),
    department VARCHAR(100),
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE SET NULL
);

-- Consultation table with audio recording capability
CREATE TABLE consultation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    consultation_date DATETIME NOT NULL,
    complaint TEXT,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    follow_up_date DATE,
    audio_recording_path VARCHAR(500),
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    INDEX idx_consultation_date (consultation_date),
    INDEX idx_reference_number (reference_number)
);

-- Operation/Surgery table
CREATE TABLE operation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    operation_date DATETIME NOT NULL,
    operation_type VARCHAR(255) NOT NULL,
    description TEXT,
    complications TEXT,
    allergies_noted TEXT,
    duration_minutes INT,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    INDEX idx_operation_date (operation_date),
    INDEX idx_reference_number (reference_number)
);

-- Diagnosis table
CREATE TABLE diagnosis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    diagnosis_date DATETIME NOT NULL,
    diagnosis_name VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('Mild', 'Moderate', 'Severe', 'Critical'),
    icd_code VARCHAR(20),
    notes TEXT,
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    INDEX idx_diagnosis_date (diagnosis_date),
    INDEX idx_reference_number (reference_number)
);

-- Medical administration (medicine dosage by staff)
CREATE TABLE medical_administration (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    doctor_ssn VARCHAR(50) NOT NULL,
    administration_date DATETIME NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    route VARCHAR(50),
    allergies_checked TEXT,
    notes TEXT,
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    INDEX idx_administration_date (administration_date),
    INDEX idx_reference_number (reference_number)
);

-- Login tables for authentication
CREATE TABLE patient_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    patient_ssn VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

CREATE TABLE doctor_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    doctor_ssn VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE
);

CREATE TABLE staff_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    staff_ssn VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn) ON DELETE CASCADE
);

CREATE TABLE admin_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('Super Admin', 'Admin', 'Manager') DEFAULT 'Admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- External health office table for inter-hospital communication
CREATE TABLE external_health_office (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_name VARCHAR(255) NOT NULL,
    hospital_id INT NOT NULL,
    contact_person VARCHAR(200),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE CASCADE
);

CREATE TABLE external_office_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    office_id INT NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES external_health_office(id) ON DELETE CASCADE
);

-- Patient transfer/referral table
CREATE TABLE patient_referral (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    referring_doctor_ssn VARCHAR(50) NOT NULL,
    from_hospital_id INT NOT NULL,
    to_hospital_id INT NOT NULL,
    referral_date DATETIME NOT NULL,
    reason TEXT NOT NULL,
    medical_summary TEXT,
    urgency ENUM('Low', 'Medium', 'High', 'Emergency') DEFAULT 'Medium',
    status ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending',
    document_path VARCHAR(500),
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    FOREIGN KEY (referring_doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (from_hospital_id) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (to_hospital_id) REFERENCES hospital(id) ON DELETE CASCADE,
    INDEX idx_referral_date (referral_date),
    INDEX idx_reference_number (reference_number)
);

-- Payment table for multiple banks
CREATE TABLE payment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    payment_type ENUM('Consultation', 'Surgery', 'Medicine', 'Other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    bank_name ENUM('Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_date DATETIME NOT NULL,
    reference_id VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    INDEX idx_payment_date (payment_date),
    INDEX idx_transaction_id (transaction_id)
);

-- Document uploads table
CREATE TABLE document_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    doctor_ssn VARCHAR(50),
    document_type ENUM('Medical Report', 'Lab Result', 'X-Ray', 'Prescription', 'Other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    upload_date DATETIME NOT NULL,
    uploaded_by ENUM('Doctor', 'Staff', 'External Office') NOT NULL,
    reference_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE SET NULL,
    INDEX idx_upload_date (upload_date),
    INDEX idx_reference_number (reference_number)
);

-- Insert sample hospital data
INSERT INTO hospital (name, email, phone, address, city, country) VALUES
('Goba Hospital', 'info@gobahospital.et', '+251-11-1234567', 'Medical District, Goba', 'Goba', 'Ethiopia'),
('Addis Ababa Medical Center', 'contact@aamc.et', '+251-11-7654321', 'Bole Area, Addis Ababa', 'Addis Ababa', 'Ethiopia');

-- Insert sample admin user
INSERT INTO admin_login (user_id, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gobahospital.et', 'Super Admin');
-- Password is 'password' hashed with bcrypt