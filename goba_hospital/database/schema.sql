-- Goba Hospital Patient Record Management System Database Schema
-- Created: 2024

CREATE DATABASE IF NOT EXISTS goba_hospital;
USE goba_hospital;

-- Hospital Information Table
CREATE TABLE hospital_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL DEFAULT 'Goba Hospital',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(100),
    established_date DATE,
    license_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin Login Table
CREATE TABLE admin_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor Information Table
CREATE TABLE doctor_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    specialization VARCHAR(100),
    qualification VARCHAR(255),
    license_number VARCHAR(50) UNIQUE,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    national_id VARCHAR(50),
    passport_number VARCHAR(50),
    hire_date DATE,
    department VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor Login Table
CREATE TABLE doctor_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctor_info(doctor_id) ON DELETE CASCADE
);

-- Patient Information Table
CREATE TABLE patient_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    national_id VARCHAR(50),
    passport_number VARCHAR(50),
    birth_certificate VARCHAR(50),
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
    occupation VARCHAR(100),
    insurance_number VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patient Login Table
CREATE TABLE patient_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id) ON DELETE CASCADE
);

-- Staff Information Table
CREATE TABLE staff_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    national_id VARCHAR(50),
    hire_date DATE,
    salary DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Staff Login Table
CREATE TABLE staff_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff_info(staff_id) ON DELETE CASCADE
);

-- External Health Office Table
CREATE TABLE external_office (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_id VARCHAR(20) UNIQUE NOT NULL,
    office_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- External Login Table
CREATE TABLE external_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES external_office(office_id) ON DELETE CASCADE
);

-- Consultation Records Table
CREATE TABLE consultation_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    consultation_date DATETIME NOT NULL,
    chief_complaint TEXT,
    history_present_illness TEXT,
    physical_examination TEXT,
    vital_signs JSON,
    diagnosis TEXT,
    treatment_plan TEXT,
    prescription TEXT,
    follow_up_date DATE,
    notes TEXT,
    audio_recording VARCHAR(255),
    status ENUM('Completed', 'In Progress', 'Cancelled') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor_info(doctor_id)
);

-- Surgery Records Table
CREATE TABLE surgery_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    surgery_date DATETIME NOT NULL,
    surgery_type VARCHAR(255) NOT NULL,
    procedure_name VARCHAR(255),
    pre_operative_diagnosis TEXT,
    post_operative_diagnosis TEXT,
    surgery_notes TEXT,
    complications TEXT,
    anesthesia_type VARCHAR(100),
    duration_minutes INT,
    blood_loss_ml INT,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor_info(doctor_id)
);

-- Diagnosis Records Table
CREATE TABLE diagnosis_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    diagnosis_date DATETIME NOT NULL,
    primary_diagnosis VARCHAR(255) NOT NULL,
    secondary_diagnosis TEXT,
    icd_code VARCHAR(20),
    symptoms TEXT,
    test_results TEXT,
    lab_results TEXT,
    imaging_results TEXT,
    treatment_recommendation TEXT,
    prognosis TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor_info(doctor_id)
);

-- Medication Dosage Table
CREATE TABLE medication_dosage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    staff_id VARCHAR(20) NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    route VARCHAR(50),
    instructions TEXT,
    start_date DATE,
    end_date DATE,
    administered_by VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id),
    FOREIGN KEY (staff_id) REFERENCES staff_info(staff_id)
);

-- Patient Referrals Table
CREATE TABLE patient_referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    referring_doctor_id VARCHAR(20) NOT NULL,
    referred_to_hospital VARCHAR(255),
    referred_to_doctor VARCHAR(255),
    referral_date DATE NOT NULL,
    reason_for_referral TEXT NOT NULL,
    urgency_level ENUM('Low', 'Medium', 'High', 'Emergency') DEFAULT 'Medium',
    medical_summary TEXT,
    current_medications TEXT,
    attachments VARCHAR(255),
    status ENUM('Pending', 'Accepted', 'Completed', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id),
    FOREIGN KEY (referring_doctor_id) REFERENCES doctor_info(doctor_id)
);

-- Payment Information Table
CREATE TABLE payment_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('Consultation', 'Surgery', 'Laboratory', 'Pharmacy', 'Other') NOT NULL,
    payment_method ENUM('Cash', 'Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr', 'Insurance') NOT NULL,
    transaction_id VARCHAR(100),
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_info(patient_id)
);

-- External Patient Information Table
CREATE TABLE external_patient_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    external_reference_id VARCHAR(50) UNIQUE NOT NULL,
    sending_office_id VARCHAR(20) NOT NULL,
    patient_name VARCHAR(255) NOT NULL,
    patient_national_id VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    medical_summary TEXT,
    reason_for_transfer TEXT,
    urgency_level ENUM('Low', 'Medium', 'High', 'Emergency') DEFAULT 'Medium',
    attachments VARCHAR(255),
    transfer_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Received', 'Processed') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sending_office_id) REFERENCES external_office(office_id)
);

-- File Uploads Table
CREATE TABLE file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_id VARCHAR(50) UNIQUE NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by VARCHAR(20),
    uploaded_by_type ENUM('doctor', 'staff', 'admin', 'external'),
    related_record_type ENUM('consultation', 'surgery', 'diagnosis', 'referral', 'external_patient'),
    related_record_id VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System Logs Table
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(20),
    user_type ENUM('admin', 'doctor', 'patient', 'staff', 'external'),
    action VARCHAR(255) NOT NULL,
    table_affected VARCHAR(100),
    record_id VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO admin_login (username, password, full_name, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gobahospital.com', 'super_admin');

-- Insert default hospital information
INSERT INTO hospital_info (name, address, phone, email, website, established_date, license_number)
VALUES ('Goba Hospital', 'Goba, Bale Zone, Oromia Region, Ethiopia', '+251-22-000-0000', 'info@gobahospital.com', 'www.gobahospital.com', '1990-01-01', 'GH-001-1990');

-- Create indexes for better performance
CREATE INDEX idx_patient_id ON patient_info(patient_id);
CREATE INDEX idx_doctor_id ON doctor_info(doctor_id);
CREATE INDEX idx_staff_id ON staff_info(staff_id);
CREATE INDEX idx_consultation_patient ON consultation_records(patient_id);
CREATE INDEX idx_consultation_doctor ON consultation_records(doctor_id);
CREATE INDEX idx_consultation_date ON consultation_records(consultation_date);
CREATE INDEX idx_surgery_patient ON surgery_records(patient_id);
CREATE INDEX idx_surgery_doctor ON surgery_records(doctor_id);
CREATE INDEX idx_diagnosis_patient ON diagnosis_records(patient_id);
CREATE INDEX idx_payment_patient ON payment_info(patient_id);
CREATE INDEX idx_referral_patient ON patient_referrals(patient_id);

-- Create views for easy data retrieval
CREATE VIEW patient_summary AS
SELECT 
    p.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS full_name,
    p.date_of_birth,
    p.gender,
    p.phone,
    p.email,
    p.blood_group,
    COUNT(DISTINCT c.id) AS total_consultations,
    COUNT(DISTINCT s.id) AS total_surgeries,
    COUNT(DISTINCT d.id) AS total_diagnoses,
    MAX(c.consultation_date) AS last_visit
FROM patient_info p
LEFT JOIN consultation_records c ON p.patient_id = c.patient_id
LEFT JOIN surgery_records s ON p.patient_id = s.patient_id
LEFT JOIN diagnosis_records d ON p.patient_id = d.patient_id
WHERE p.is_active = TRUE
GROUP BY p.patient_id;

CREATE VIEW doctor_summary AS
SELECT 
    d.doctor_id,
    CONCAT(d.first_name, ' ', d.last_name) AS full_name,
    d.specialization,
    d.department,
    d.phone,
    d.email,
    COUNT(DISTINCT c.id) AS total_consultations,
    COUNT(DISTINCT s.id) AS total_surgeries,
    COUNT(DISTINCT diag.id) AS total_diagnoses
FROM doctor_info d
LEFT JOIN consultation_records c ON d.doctor_id = c.doctor_id
LEFT JOIN surgery_records s ON d.doctor_id = s.doctor_id
LEFT JOIN diagnosis_records diag ON d.doctor_id = diag.doctor_id
WHERE d.is_active = TRUE
GROUP BY d.doctor_id;