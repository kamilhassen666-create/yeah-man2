-- Goba Hospital Patient Record Management System Database Schema
-- Drop database if exists and create new one
DROP DATABASE IF EXISTS goba_hospital_db;
CREATE DATABASE goba_hospital_db;
USE goba_hospital_db;

-- Hospital table
CREATE TABLE hospital (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patient table
CREATE TABLE patient (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    blood_type VARCHAR(5),
    allergies TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctor table
CREATE TABLE doctor (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    license_number VARCHAR(50) UNIQUE,
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE SET NULL
);

-- Medical Staff table
CREATE TABLE medical_staff (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE SET NULL
);

-- Consultation table (with audio recording support)
CREATE TABLE consultation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    date_time DATETIME NOT NULL,
    complaints TEXT,
    examination_findings TEXT,
    treatment_plan TEXT,
    prescription TEXT,
    follow_up_date DATE,
    audio_recording VARCHAR(255),
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Operation/Surgery table
CREATE TABLE operation (
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    date_time DATETIME,
    operation_type VARCHAR(100),
    description TEXT,
    complications TEXT,
    pre_operative_diagnosis TEXT,
    post_operative_diagnosis TEXT,
    surgeon_notes TEXT,
    anesthesia_type VARCHAR(100),
    duration_minutes INT,
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (doctor_ssn, patient_ssn, date_time),
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Diagnosis table
CREATE TABLE diagnosis (
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    date_time DATETIME,
    diagnosis_name VARCHAR(255),
    description TEXT,
    severity ENUM('Mild', 'Moderate', 'Severe', 'Critical'),
    icd_code VARCHAR(20),
    treatment_recommendations TEXT,
    lab_results TEXT,
    imaging_results TEXT,
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (doctor_ssn, patient_ssn, date_time),
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Medical Administration (for medicine dosage by staff)
CREATE TABLE medical_administration (
    staff_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    date_time DATETIME,
    medicine_name VARCHAR(255),
    dosage VARCHAR(100),
    route VARCHAR(50),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    allergies_checked BOOLEAN DEFAULT FALSE,
    notes TEXT,
    reference_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (staff_ssn, patient_ssn, date_time),
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn) ON DELETE CASCADE,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Patient Login table
CREATE TABLE patient_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    patient_ssn VARCHAR(50) UNIQUE,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    account_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Doctor Login table
CREATE TABLE doctor_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    doctor_ssn VARCHAR(50) UNIQUE,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    account_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE CASCADE
);

-- Staff Login table
CREATE TABLE staff_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    staff_ssn VARCHAR(50) UNIQUE,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    account_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn) ON DELETE CASCADE
);

-- Admin Login table
CREATE TABLE admin_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    admin_name VARCHAR(100),
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    account_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- External Health Office table
CREATE TABLE external_health_office (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- External Health Office Login table
CREATE TABLE external_office_login (
    user_id VARCHAR(50) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    office_id INT,
    doctor_ssn VARCHAR(50),
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    account_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES external_health_office(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn) ON DELETE SET NULL
);

-- Patient File Uploads table (for external health office)
CREATE TABLE patient_file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50),
    uploaded_by_office INT,
    file_name VARCHAR(255),
    file_type VARCHAR(50),
    file_path VARCHAR(500),
    file_size INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_office) REFERENCES external_health_office(id) ON DELETE CASCADE
);

-- Hospital Transfer Requests table
CREATE TABLE hospital_transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50),
    from_hospital_id INT,
    to_hospital_name VARCHAR(255),
    to_hospital_contact VARCHAR(255),
    transfer_reason TEXT,
    medical_summary TEXT,
    requesting_doctor_ssn VARCHAR(50),
    transfer_date DATE,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE,
    FOREIGN KEY (from_hospital_id) REFERENCES hospital(id) ON DELETE SET NULL,
    FOREIGN KEY (requesting_doctor_ssn) REFERENCES doctor(ssn) ON DELETE SET NULL
);

-- Payment table for Ethiopian banks
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50),
    service_type ENUM('consultation', 'operation', 'diagnosis', 'medication', 'other'),
    service_reference VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'ETB',
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking'),
    bank_name ENUM('Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr') NULL,
    transaction_id VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn) ON DELETE CASCADE
);

-- Insert sample hospital data
INSERT INTO hospital (name, email, address, phone) VALUES 
('Goba Hospital', 'info@gobahospital.et', 'Goba, Bale Zone, Oromia Region, Ethiopia', '+251-22-661-0001');

-- Insert sample admin user
INSERT INTO admin_login (user_id, password_hash, admin_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Create indexes for better performance
CREATE INDEX idx_consultation_patient ON consultation(patient_ssn);
CREATE INDEX idx_consultation_doctor ON consultation(doctor_ssn);
CREATE INDEX idx_consultation_date ON consultation(date_time);
CREATE INDEX idx_operation_patient ON operation(patient_ssn);
CREATE INDEX idx_operation_doctor ON operation(doctor_ssn);
CREATE INDEX idx_diagnosis_patient ON diagnosis(patient_ssn);
CREATE INDEX idx_diagnosis_doctor ON diagnosis(doctor_ssn);
CREATE INDEX idx_payment_patient ON payments(patient_ssn);
CREATE INDEX idx_transfers_patient ON hospital_transfers(patient_ssn);