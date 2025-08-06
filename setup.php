<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Goba Hospital - Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; }
        .success { color: #059669; background: #ecfdf5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2563eb; background: #eff6ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        code { background: #f3f4f6; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🏥 Goba Hospital - Database Setup</h1>";

try {
    // Create database and tables
    $sql_file = 'config/setup_database.sql';
    $sql = file_get_contents($sql_file);
    
    echo "<div class='info'>Setting up database schema...</div>";
    
    // Execute SQL commands
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec($sql);
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<div class='success'>✅ Database schema created successfully!</div>";
    
    // Insert sample data
    echo "<div class='info'>Inserting sample data...</div>";
    
    // Sample Patients
    $patients = [
        ['ETH001234567', 'Abebe', 'Kebede', '1985-03-15', 'Male', 'abebe@email.com', '+251911234567', 'Addis Ababa, Ethiopia', 'Almaz Kebede', '+251911234568', 'O+', 'None'],
        ['ETH001234568', 'Hanan', 'Mohammed', '1990-07-22', 'Female', 'hanan@email.com', '+251911234569', 'Dire Dawa, Ethiopia', 'Ahmed Mohammed', '+251911234570', 'A+', 'Penicillin'],
        ['ETH001234569', 'Dawit', 'Tesfaye', '1978-11-08', 'Male', 'dawit@email.com', '+251911234571', 'Bahir Dar, Ethiopia', 'Meron Tesfaye', '+251911234572', 'B+', 'None'],
        ['ETH001234570', 'Rahel', 'Hailu', '1995-05-30', 'Female', 'rahel@email.com', '+251911234573', 'Mekelle, Ethiopia', 'Hailu Gebremedhin', '+251911234574', 'AB+', 'Aspirin'],
        ['ETH001234571', 'Samuel', 'Girma', '1982-12-12', 'Male', 'samuel@email.com', '+251911234575', 'Hawassa, Ethiopia', 'Tigist Girma', '+251911234576', 'O-', 'None']
    ];
    
    foreach ($patients as $patient) {
        executeQuery("INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, email, phone, address, emergency_contact, emergency_phone, blood_type, allergies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $patient);
    }
    
    // Sample Doctors
    $doctors = [
        ['DOC001234567', 'Dr. Getachew', 'Haile', 'getachew@gobahospital.et', '+251911234577', 'Goba, Ethiopia', 'Cardiology', 'LIC001', 1, '2015-01-15'],
        ['DOC001234568', 'Dr. Selamawit', 'Bekele', 'selamawit@gobahospital.et', '+251911234578', 'Goba, Ethiopia', 'Pediatrics', 'LIC002', 1, '2017-03-20'],
        ['DOC001234569', 'Dr. Henok', 'Tadesse', 'henok@gobahospital.et', '+251911234579', 'Goba, Ethiopia', 'Surgery', 'LIC003', 1, '2012-06-10'],
        ['DOC001234570', 'Dr. Mahlet', 'Asefa', 'mahlet@gobahospital.et', '+251911234580', 'Goba, Ethiopia', 'Internal Medicine', 'LIC004', 1, '2018-09-05'],
        ['DOC001234571', 'Dr. Berhanu', 'Worku', 'berhanu@gobahospital.et', '+251911234581', 'Goba, Ethiopia', 'Neurology', 'LIC005', 1, '2016-11-12']
    ];
    
    foreach ($doctors as $doctor) {
        executeQuery("INSERT INTO doctor (ssn, first_name, last_name, email, phone, address, specialization, license_number, hospital_id, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $doctor);
    }
    
    // Sample Medical Staff
    $staff = [
        ['STAFF001234567', 'Almaz', 'Demisse', 'almaz@gobahospital.et', '+251911234582', 'Goba, Ethiopia', 'Head Nurse', 'Emergency', 1, '2014-05-20'],
        ['STAFF001234568', 'Tsegaye', 'Mulugeta', 'tsegaye@gobahospital.et', '+251911234583', 'Goba, Ethiopia', 'Pharmacy Technician', 'Pharmacy', 1, '2016-08-15'],
        ['STAFF001234569', 'Birtukan', 'Alemayehu', 'birtukan@gobahospital.et', '+251911234584', 'Goba, Ethiopia', 'Lab Technician', 'Laboratory', 1, '2018-02-10'],
        ['STAFF001234570', 'Mulugeta', 'Abera', 'mulugeta@gobahospital.et', '+251911234585', 'Goba, Ethiopia', 'Nurse', 'Pediatrics', 1, '2019-04-25'],
        ['STAFF001234571', 'Yodit', 'Getnet', 'yodit@gobahospital.et', '+251911234586', 'Goba, Ethiopia', 'Radiologic Technologist', 'Radiology', 1, '2017-07-18']
    ];
    
    foreach ($staff as $member) {
        executeQuery("INSERT INTO medical_staff (ssn, first_name, last_name, email, phone, address, position, department, hospital_id, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $member);
    }
    
    // Create login credentials (password: password123 for all)
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    
    // Patient logins
    $patient_logins = [
        ['ETH001234567', 'abebe_kebede', $password_hash],
        ['ETH001234568', 'hanan_mohammed', $password_hash],
        ['ETH001234569', 'dawit_tesfaye', $password_hash],
        ['ETH001234570', 'rahel_hailu', $password_hash],
        ['ETH001234571', 'samuel_girma', $password_hash]
    ];
    
    foreach ($patient_logins as $login) {
        executeQuery("INSERT INTO patient_login (patient_ssn, username, password) VALUES (?, ?, ?)", $login);
    }
    
    // Doctor logins
    $doctor_logins = [
        ['DOC001234567', 'dr_getachew', $password_hash],
        ['DOC001234568', 'dr_selamawit', $password_hash],
        ['DOC001234569', 'dr_henok', $password_hash],
        ['DOC001234570', 'dr_mahlet', $password_hash],
        ['DOC001234571', 'dr_berhanu', $password_hash]
    ];
    
    foreach ($doctor_logins as $login) {
        executeQuery("INSERT INTO doctor_login (doctor_ssn, username, password) VALUES (?, ?, ?)", $login);
    }
    
    // Staff logins
    $staff_logins = [
        ['STAFF001234567', 'almaz_demisse', $password_hash],
        ['STAFF001234568', 'tsegaye_mulugeta', $password_hash],
        ['STAFF001234569', 'birtukan_alemayehu', $password_hash],
        ['STAFF001234570', 'mulugeta_abera', $password_hash],
        ['STAFF001234571', 'yodit_getnet', $password_hash]
    ];
    
    foreach ($staff_logins as $login) {
        executeQuery("INSERT INTO staff_login (staff_ssn, username, password) VALUES (?, ?, ?)", $login);
    }
    
    // Sample Consultations
    $consultations = [
        ['DOC001234567', 'ETH001234567', '2024-01-15 10:00:00', 'Chest pain, shortness of breath', 'Hypertension', 'Prescribed ACE inhibitors, lifestyle changes', 'Patient advised to reduce salt intake', null, 'CONS20240115001', 'Completed'],
        ['DOC001234568', 'ETH001234568', '2024-01-16 14:30:00', 'Fever, cough', 'Upper respiratory infection', 'Antibiotics prescribed', 'Follow-up in 1 week', null, 'CONS20240116001', 'Completed'],
        ['DOC001234570', 'ETH001234569', '2024-01-17 09:15:00', 'Abdominal pain', 'Gastritis', 'Proton pump inhibitors', 'Dietary modifications recommended', null, 'CONS20240117001', 'Completed'],
        ['DOC001234568', 'ETH001234570', '2024-01-18 11:45:00', 'Routine checkup', 'Normal development', 'No treatment needed', 'Continue regular checkups', null, 'CONS20240118001', 'Completed'],
        ['DOC001234571', 'ETH001234571', '2024-01-19 16:20:00', 'Headaches, dizziness', 'Migraine', 'Pain management medication', 'Stress reduction advised', null, 'CONS20240119001', 'Completed']
    ];
    
    foreach ($consultations as $consultation) {
        executeQuery("INSERT INTO consultation (doctor_ssn, patient_ssn, consultation_date, symptoms, diagnosis, treatment, notes, audio_file, reference_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $consultation);
    }
    
    // Sample Diagnoses
    $diagnoses = [
        ['DOC001234567', 'ETH001234567', '2024-01-15 10:30:00', 'Essential Hypertension', 'Patient has consistently elevated blood pressure readings', 'Moderate', 'I10', 'Monitor blood pressure regularly', 'DIAG20240115001'],
        ['DOC001234568', 'ETH001234568', '2024-01-16 14:45:00', 'Acute Upper Respiratory Infection', 'Viral infection of upper respiratory tract', 'Mild', 'J06.9', 'Rest and hydration recommended', 'DIAG20240116001'],
        ['DOC001234570', 'ETH001234569', '2024-01-17 09:30:00', 'Chronic Gastritis', 'Inflammation of stomach lining', 'Moderate', 'K29.5', 'Avoid spicy foods', 'DIAG20240117001']
    ];
    
    foreach ($diagnoses as $diagnosis) {
        executeQuery("INSERT INTO diagnosis (doctor_ssn, patient_ssn, diagnosis_date, diagnosis_name, description, severity, icd_code, notes, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $diagnosis);
    }
    
    // Sample Medical Administration
    $medications = [
        ['STAFF001234568', 'ETH001234567', 'DOC001234567', 'Lisinopril', '10mg', 'Once daily', '30 days', '2024-01-15 11:00:00', 'Monitor blood pressure weekly', true, 'MED20240115001'],
        ['STAFF001234568', 'ETH001234568', 'DOC001234568', 'Amoxicillin', '500mg', 'Three times daily', '7 days', '2024-01-16 15:00:00', 'Take with food', true, 'MED20240116001'],
        ['STAFF001234568', 'ETH001234569', 'DOC001234570', 'Omeprazole', '20mg', 'Once daily before breakfast', '14 days', '2024-01-17 10:00:00', 'Take on empty stomach', true, 'MED20240117001']
    ];
    
    foreach ($medications as $medication) {
        executeQuery("INSERT INTO medical_administration (staff_ssn, patient_ssn, doctor_ssn, medication_name, dosage, frequency, duration, administration_date, notes, allergies_checked, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $medication);
    }
    
    // Sample Payments
    $payments = [
        ['ETH001234567', 'Consultation', 500.00, 'ETB', 'Commercial Bank', 'TXN20240115001', 'Card', 'Completed', '2024-01-15 11:30:00', 'PAY20240115001', 'Consultation fee'],
        ['ETH001234568', 'Consultation', 500.00, 'ETB', 'Awash Bank', 'TXN20240116001', 'Mobile', 'Completed', '2024-01-16 15:30:00', 'PAY20240116001', 'Consultation and medication'],
        ['ETH001234569', 'Consultation', 500.00, 'ETB', 'Telebirr', 'TXN20240117001', 'Mobile', 'Completed', '2024-01-17 10:30:00', 'PAY20240117001', 'Consultation fee']
    ];
    
    foreach ($payments as $payment) {
        executeQuery("INSERT INTO payment (patient_ssn, payment_type, amount, currency, bank_name, transaction_id, payment_method, payment_status, payment_date, reference_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $payment);
    }
    
    echo "<div class='success'>✅ Sample data inserted successfully!</div>";
    echo "<div class='success'>✅ Database setup completed!</div>";
    
    echo "<h2>🔑 Demo Login Credentials</h2>";
    echo "<div class='info'>
    <h3>Admin Portal:</h3>
    Username: <code>admin</code><br>
    Password: <code>admin123</code>
    
    <h3>Patient Portal:</h3>
    Username: <code>abebe_kebede</code><br>
    Password: <code>password123</code>
    
    <h3>Doctor Portal:</h3>
    Username: <code>dr_getachew</code><br>
    Password: <code>password123</code>
    
    <h3>Staff Portal:</h3>
    Username: <code>almaz_demisse</code><br>
    Password: <code>password123</code>
    </div>";
    
    echo "<h2>📊 Database Statistics</h2>";
    echo "<div class='info'>";
    echo "Patients: " . getRowCount("SELECT COUNT(*) FROM patient") . "<br>";
    echo "Doctors: " . getRowCount("SELECT COUNT(*) FROM doctor") . "<br>";
    echo "Medical Staff: " . getRowCount("SELECT COUNT(*) FROM medical_staff") . "<br>";
    echo "Consultations: " . getRowCount("SELECT COUNT(*) FROM consultation") . "<br>";
    echo "Diagnoses: " . getRowCount("SELECT COUNT(*) FROM diagnosis") . "<br>";
    echo "Medications: " . getRowCount("SELECT COUNT(*) FROM medical_administration") . "<br>";
    echo "Payments: " . getRowCount("SELECT COUNT(*) FROM payment") . "<br>";
    echo "</div>";
    
    echo "<h2>🚀 Next Steps</h2>";
    echo "<div class='info'>
    1. Access the main homepage: <a href='index.html'>index.html</a><br>
    2. Try logging into different portals with the credentials above<br>
    3. Explore the patient, doctor, staff, and admin functionalities<br>
    4. The system is now ready for use and testing!
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>