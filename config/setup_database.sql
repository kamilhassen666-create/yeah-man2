-- Create database
CREATE DATABASE IF NOT EXISTS goba_hospital;
USE goba_hospital;

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
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(20),
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
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    specialization VARCHAR(255),
    license_number VARCHAR(100) UNIQUE,
    hospital_id INT,
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id)
);

-- Medical staff table
CREATE TABLE medical_staff (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    position VARCHAR(100),
    department VARCHAR(100),
    hospital_id INT,
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id)
);

-- Consultation table
CREATE TABLE consultation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    consultation_date DATETIME NOT NULL,
    symptoms TEXT,
    diagnosis TEXT,
    treatment TEXT,
    notes TEXT,
    audio_file VARCHAR(255),
    reference_number VARCHAR(100) UNIQUE,
    status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    INDEX idx_reference_number (reference_number),
    INDEX idx_consultation_date (consultation_date)
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
    allergies TEXT,
    pre_conditions TEXT,
    post_conditions TEXT,
    reference_number VARCHAR(100) UNIQUE,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    INDEX idx_reference_number (reference_number),
    INDEX idx_operation_date (operation_date)
);

-- Diagnosis table
CREATE TABLE diagnosis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    diagnosis_date DATETIME NOT NULL,
    diagnosis_name VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('Mild', 'Moderate', 'Severe', 'Critical') DEFAULT 'Mild',
    icd_code VARCHAR(20),
    notes TEXT,
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    INDEX idx_reference_number (reference_number),
    INDEX idx_diagnosis_date (diagnosis_date)
);

-- Medical administration table (for medicine dosage by staff)
CREATE TABLE medical_administration (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_ssn VARCHAR(50) NOT NULL,
    patient_ssn VARCHAR(50) NOT NULL,
    doctor_ssn VARCHAR(50),
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100),
    duration VARCHAR(100),
    administration_date DATETIME NOT NULL,
    notes TEXT,
    allergies_checked BOOLEAN DEFAULT FALSE,
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    INDEX idx_reference_number (reference_number),
    INDEX idx_administration_date (administration_date)
);

-- Login tables for authentication
CREATE TABLE patient_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

CREATE TABLE doctor_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_ssn VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn)
);

CREATE TABLE staff_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_ssn VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn)
);

CREATE TABLE admin_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role ENUM('Super Admin', 'Admin', 'Manager') DEFAULT 'Admin',
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- External health office table
CREATE TABLE external_health_office (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_name VARCHAR(255) NOT NULL,
    doctor_ssn VARCHAR(50) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn)
);

-- Patient transfers/referrals table
CREATE TABLE patient_transfer (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    from_hospital_id INT,
    to_hospital_name VARCHAR(255) NOT NULL,
    to_hospital_contact TEXT,
    transfer_date DATETIME NOT NULL,
    reason TEXT,
    medical_summary TEXT,
    documents JSON,
    status ENUM('Pending', 'Approved', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_by VARCHAR(50),
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    FOREIGN KEY (from_hospital_id) REFERENCES hospital(id),
    INDEX idx_reference_number (reference_number)
);

-- Payment table for banking integration
CREATE TABLE payment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    payment_type ENUM('Consultation', 'Surgery', 'Medication', 'Other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(5) DEFAULT 'ETB',
    bank_name ENUM('Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr') NOT NULL,
    transaction_id VARCHAR(255) UNIQUE,
    payment_method ENUM('Card', 'Mobile', 'Bank Transfer', 'Cash') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_date DATETIME NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_payment_date (payment_date)
);

-- File uploads table
CREATE TABLE file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_ssn VARCHAR(50) NOT NULL,
    uploaded_by VARCHAR(50) NOT NULL,
    uploader_type ENUM('Doctor', 'Staff', 'External') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    file_size INT NOT NULL,
    description TEXT,
    category ENUM('Medical Report', 'X-Ray', 'Lab Result', 'Prescription', 'Other') DEFAULT 'Other',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    INDEX idx_patient_ssn (patient_ssn),
    INDEX idx_upload_date (upload_date)
);

-- Audit log table for tracking changes
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    user_type ENUM('Patient', 'Doctor', 'Staff', 'Admin', 'External') NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id VARCHAR(100),
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
);

-- Insert default hospital
INSERT INTO hospital (name, email, address, phone) VALUES 
('Goba Hospital', 'info@gobahospital.et', 'Goba, Bale Zone, Oromia Region, Ethiopia', '+251-22-XXX-XXXX');

-- Insert default admin user (password: admin123)
INSERT INTO admin_login (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gobahospital.et', 'Super Admin');

-- Create indexes for better performance
CREATE INDEX idx_patient_name ON patient(first_name, last_name);
CREATE INDEX idx_doctor_name ON doctor(first_name, last_name);
CREATE INDEX idx_staff_name ON medical_staff(first_name, last_name);
CREATE INDEX idx_consultation_patient ON consultation(patient_ssn);
CREATE INDEX idx_operation_patient ON operation(patient_ssn);
CREATE INDEX idx_diagnosis_patient ON diagnosis(patient_ssn);

-- Create views for commonly used queries
CREATE VIEW patient_full_info AS
SELECT 
    p.*,
    pl.username,
    pl.last_login,
    pl.is_active as login_active
FROM patient p
LEFT JOIN patient_login pl ON p.ssn = pl.patient_ssn;

CREATE VIEW doctor_full_info AS
SELECT 
    d.*,
    h.name as hospital_name,
    dl.username,
    dl.last_login,
    dl.is_active as login_active
FROM doctor d
LEFT JOIN hospital h ON d.hospital_id = h.id
LEFT JOIN doctor_login dl ON d.ssn = dl.doctor_ssn;

CREATE VIEW staff_full_info AS
SELECT 
    s.*,
    h.name as hospital_name,
    sl.username,
    sl.last_login,
    sl.is_active as login_active
FROM medical_staff s
LEFT JOIN hospital h ON s.hospital_id = h.id
LEFT JOIN staff_login sl ON s.ssn = sl.staff_ssn;