-- Goba Hospital Patient Record Management System Database Schema

CREATE DATABASE IF NOT EXISTS goba_hospital;
USE goba_hospital;

-- Hospital table
CREATE TABLE hospital (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
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
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctor table
CREATE TABLE doctor (
    ssn VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    license_number VARCHAR(100) UNIQUE,
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id)
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
    FOREIGN KEY (hospital_id) REFERENCES hospital(id)
);

-- Consultation table with audio support
CREATE TABLE consultation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    consultation_date DATETIME NOT NULL,
    complaints TEXT,
    symptoms TEXT,
    diagnosis_summary TEXT,
    treatment_plan TEXT,
    prescription TEXT,
    follow_up_date DATE,
    audio_file_path VARCHAR(500),
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Operation/Surgery table
CREATE TABLE operation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    operation_date DATETIME NOT NULL,
    operation_type VARCHAR(255) NOT NULL,
    description TEXT,
    complications TEXT,
    allergies TEXT,
    anesthesia_type VARCHAR(100),
    duration_hours DECIMAL(4,2),
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Diagnosis table
CREATE TABLE diagnosis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    diagnosis_date DATETIME NOT NULL,
    diagnosis_name VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('Mild', 'Moderate', 'Severe', 'Critical'),
    treatment_status ENUM('Active', 'Resolved', 'Chronic', 'Under Treatment'),
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Medical Administration table (for staff medicine dosage records)
CREATE TABLE medical_administration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_ssn VARCHAR(50),
    patient_ssn VARCHAR(50),
    administration_date DATETIME NOT NULL,
    medicines TEXT,
    dosage TEXT,
    allergies TEXT,
    notes TEXT,
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn),
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Patient Login table
CREATE TABLE patient_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_ssn VARCHAR(50) UNIQUE,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Doctor Login table
CREATE TABLE doctor_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_ssn VARCHAR(50) UNIQUE,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_ssn) REFERENCES doctor(ssn)
);

-- Staff Login table
CREATE TABLE staff_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_ssn VARCHAR(50) UNIQUE,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_ssn) REFERENCES medical_staff(ssn)
);

-- Admin Login table
CREATE TABLE admin_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    email VARCHAR(255),
    role ENUM('Super Admin', 'Admin') DEFAULT 'Admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- External Health Office table
CREATE TABLE external_health_office (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patient Transfer table (for external health office)
CREATE TABLE patient_transfer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_ssn VARCHAR(50),
    from_office_id INT,
    to_hospital_id INT,
    transfer_date DATETIME NOT NULL,
    reason TEXT,
    medical_documents TEXT,
    file_attachments JSON,
    status ENUM('Pending', 'Sent', 'Received', 'Rejected') DEFAULT 'Pending',
    reference_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn),
    FOREIGN KEY (from_office_id) REFERENCES external_health_office(id),
    FOREIGN KEY (to_hospital_id) REFERENCES hospital(id)
);

-- Payment table for bank integration
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_ssn VARCHAR(50),
    service_type ENUM('Consultation', 'Operation', 'Diagnosis', 'Other'),
    service_id INT,
    amount DECIMAL(10,2) NOT NULL,
    bank_name ENUM('Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr'),
    transaction_id VARCHAR(255),
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_ssn) REFERENCES patient(ssn)
);

-- Insert default hospital
INSERT INTO hospital (name, email, phone, address) VALUES 
('Goba Hospital', 'info@gobahospital.com', '+251-22-000-0000', 'Goba, Bale Zone, Oromia Region, Ethiopia');

-- Insert default admin
INSERT INTO admin_login (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gobahospital.com', 'Super Admin');
-- Password is 'password' (hashed)