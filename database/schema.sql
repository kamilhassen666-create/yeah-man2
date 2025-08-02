-- Goba Hospital Patient Record Management System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS goba_hospital;
USE goba_hospital;

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hospital information table
CREATE TABLE hospitals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    national_id VARCHAR(20),
    passport_number VARCHAR(20),
    birth_certificate VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact VARCHAR(20),
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    allergies TEXT,
    medical_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patient login table
CREATE TABLE patient_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Doctors table
CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    license_number VARCHAR(50),
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id)
);

-- Doctor login table
CREATE TABLE doctor_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Medical staff table
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id)
);

-- Staff login table
CREATE TABLE staff_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- External health office table
CREATE TABLE external_health_office (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- External login table
CREATE TABLE external_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    external_id INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (external_id) REFERENCES external_health_office(id) ON DELETE CASCADE
);

-- Consultations table
CREATE TABLE consultations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id INT,
    doctor_id INT,
    consultation_date DATETIME,
    symptoms TEXT,
    diagnosis TEXT,
    prescription TEXT,
    notes TEXT,
    audio_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Surgeries table
CREATE TABLE surgeries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id INT,
    doctor_id INT,
    surgery_date DATETIME,
    surgery_type VARCHAR(200),
    description TEXT,
    pre_conditions TEXT,
    post_conditions TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Diagnoses table
CREATE TABLE diagnoses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id VARCHAR(50) UNIQUE NOT NULL,
    patient_id INT,
    doctor_id INT,
    diagnosis_date DATETIME,
    condition_name VARCHAR(200),
    description TEXT,
    test_results TEXT,
    treatment_plan TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Medication dosages table
CREATE TABLE medication_dosages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    staff_id INT,
    medication_name VARCHAR(200),
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    start_date DATE,
    end_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- Patient referrals table
CREATE TABLE patient_referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    from_hospital_id INT,
    to_hospital_id INT,
    doctor_id INT,
    referral_date DATETIME,
    reason TEXT,
    status ENUM('Pending', 'Accepted', 'Rejected', 'Completed'),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (from_hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (to_hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT,
    amount DECIMAL(10,2),
    payment_method ENUM('Commercial Bank', 'Awash Bank', 'Abyssinia Bank', 'Telebirr'),
    payment_date DATETIME,
    reference_number VARCHAR(100),
    description TEXT,
    status ENUM('Pending', 'Completed', 'Failed'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- External patient information table
CREATE TABLE external_patient_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    external_id INT,
    patient_name VARCHAR(200),
    patient_id VARCHAR(50),
    medical_info TEXT,
    file_path VARCHAR(255),
    uploaded_date DATETIME,
    status ENUM('Pending', 'Processed', 'Rejected'),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (external_id) REFERENCES external_health_office(id) ON DELETE CASCADE
);

-- Insert default admin account
INSERT INTO admin (username, password, email) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gobahospital.com');

-- Insert sample hospital
INSERT INTO hospitals (name, address, phone, email) VALUES ('Goba Hospital', 'Goba, Ethiopia', '+251-123-456-789', 'info@gobahospital.com');

-- Insert sample data for testing
INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, national_id, phone, email, address, blood_type) VALUES
('P001', 'Abebe', 'Kebede', '1990-05-15', 'Male', 'ET123456789', '+251-911-123-456', 'abebe@email.com', 'Addis Ababa, Ethiopia', 'O+'),
('P002', 'Fatima', 'Ahmed', '1985-08-22', 'Female', 'ET987654321', '+251-922-234-567', 'fatima@email.com', 'Dire Dawa, Ethiopia', 'A+');

INSERT INTO patient_login (patient_id, username, password) VALUES
(1, 'abebe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'fatima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO doctors (doctor_id, first_name, last_name, specialization, phone, email, hospital_id) VALUES
('D001', 'Dr. Yohannes', 'Tesfaye', 'Cardiology', '+251-933-345-678', 'yohannes@email.com', 1),
('D002', 'Dr. Aisha', 'Mohammed', 'Pediatrics', '+251-944-456-789', 'aisha@email.com', 1);

INSERT INTO doctor_login (doctor_id, username, password) VALUES
(1, 'dr.yohannes', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'dr.aisha', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO staff (staff_id, first_name, last_name, position, phone, email, hospital_id) VALUES
('S001', 'Nurse', 'Bethel', 'Senior Nurse', '+251-955-567-890', 'bethel@email.com', 1),
('S002', 'Pharmacy', 'Tekle', 'Pharmacist', '+251-966-678-901', 'tekle@email.com', 1);

INSERT INTO staff_login (staff_id, username, password) VALUES
(1, 'nurse.bethel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'pharmacy.tekle', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');