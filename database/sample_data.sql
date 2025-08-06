-- Sample data for Goba Hospital Management System
USE goba_hospital;

-- Insert sample patients
INSERT INTO patient (ssn, first_name, last_name, date_of_birth, gender, email, phone, address, emergency_contact, emergency_phone) VALUES
('P001234567', 'John', 'Doe', '1985-06-15', 'Male', 'john.doe@email.com', '+251-911-123456', 'Goba, Bale Zone', 'Jane Doe', '+251-911-654321'),
('P002345678', 'Mary', 'Smith', '1990-03-22', 'Female', 'mary.smith@email.com', '+251-912-234567', 'Robe, Bale Zone', 'Robert Smith', '+251-912-765432'),
('P003456789', 'Ahmed', 'Hassan', '1978-11-08', 'Male', 'ahmed.hassan@email.com', '+251-913-345678', 'Goba, Bale Zone', 'Fatima Hassan', '+251-913-876543'),
('P004567890', 'Sarah', 'Johnson', '1995-07-30', 'Female', 'sarah.johnson@email.com', '+251-914-456789', 'Delo Mena, Bale Zone', 'Michael Johnson', '+251-914-987654'),
('P005678901', 'Mohamed', 'Ali', '1982-12-12', 'Male', 'mohamed.ali@email.com', '+251-915-567890', 'Goba, Bale Zone', 'Aisha Ali', '+251-915-098765');

-- Insert sample doctors
INSERT INTO doctor (ssn, first_name, last_name, specialization, email, phone, address, license_number, hospital_id) VALUES
('D001234567', 'Dr. James', 'Wilson', 'Internal Medicine', 'james.wilson@gobahospital.com', '+251-922-111111', 'Goba, Bale Zone', 'MD-001234', 1),
('D002345678', 'Dr. Lisa', 'Brown', 'Pediatrics', 'lisa.brown@gobahospital.com', '+251-922-222222', 'Goba, Bale Zone', 'MD-002345', 1),
('D003456789', 'Dr. Robert', 'Davis', 'Surgery', 'robert.davis@gobahospital.com', '+251-922-333333', 'Goba, Bale Zone', 'MD-003456', 1),
('D004567890', 'Dr. Emily', 'Miller', 'Cardiology', 'emily.miller@gobahospital.com', '+251-922-444444', 'Goba, Bale Zone', 'MD-004567', 1),
('D005678901', 'Dr. Michael', 'Garcia', 'Orthopedics', 'michael.garcia@gobahospital.com', '+251-922-555555', 'Goba, Bale Zone', 'MD-005678', 1);

-- Insert sample medical staff
INSERT INTO medical_staff (ssn, first_name, last_name, position, email, phone, address, hospital_id) VALUES
('S001234567', 'Alice', 'Cooper', 'Head Nurse', 'alice.cooper@gobahospital.com', '+251-933-111111', 'Goba, Bale Zone', 1),
('S002345678', 'David', 'Lee', 'Pharmacist', 'david.lee@gobahospital.com', '+251-933-222222', 'Goba, Bale Zone', 1),
('S003456789', 'Maria', 'Rodriguez', 'Nurse', 'maria.rodriguez@gobahospital.com', '+251-933-333333', 'Goba, Bale Zone', 1),
('S004567890', 'Thomas', 'Anderson', 'Lab Technician', 'thomas.anderson@gobahospital.com', '+251-933-444444', 'Goba, Bale Zone', 1),
('S005678901', 'Jennifer', 'Taylor', 'Radiologist Technician', 'jennifer.taylor@gobahospital.com', '+251-933-555555', 'Goba, Bale Zone', 1);

-- Insert login credentials for sample users
INSERT INTO patient_login (patient_ssn, username, password) VALUES
('P001234567', 'patient_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password123
('P002345678', 'mary_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('P003456789', 'ahmed_hassan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('P004567890', 'sarah_johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('P005678901', 'mohamed_ali', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO doctor_login (doctor_ssn, username, password) VALUES
('D001234567', 'doctor_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password123
('D002345678', 'lisa_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('D003456789', 'robert_davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('D004567890', 'emily_miller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('D005678901', 'michael_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO staff_login (staff_ssn, username, password) VALUES
('S001234567', 'staff_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password123
('S002345678', 'david_lee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('S003456789', 'maria_rodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('S004567890', 'thomas_anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('S005678901', 'jennifer_taylor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample consultations
INSERT INTO consultation (doctor_ssn, patient_ssn, consultation_date, complaints, symptoms, diagnosis_summary, treatment_plan, prescription, follow_up_date, reference_number) VALUES
('D001234567', 'P001234567', '2024-01-15 10:00:00', 'Chest pain and shortness of breath', 'Chest tightness, difficulty breathing during exercise', 'Possible angina, requires further cardiac evaluation', 'Rest, medication, cardiac stress test', 'Aspirin 81mg daily, Metoprolol 25mg twice daily', '2024-01-22', 'CON-1705312800-1001'),
('D002345678', 'P002345678', '2024-01-16 14:30:00', 'Fever and cough in child', 'High fever (39°C), persistent cough, fatigue', 'Upper respiratory tract infection', 'Rest, fluids, monitoring', 'Paracetamol for fever, cough syrup', '2024-01-23', 'CON-1705404600-1002'),
('D001234567', 'P003456789', '2024-01-17 09:15:00', 'Diabetes follow-up', 'Increased thirst, frequent urination', 'Type 2 Diabetes Mellitus - poor control', 'Medication adjustment, dietary counseling', 'Metformin 500mg twice daily, Insulin as needed', '2024-02-17', 'CON-1705489300-1003'),
('D004567890', 'P004567890', '2024-01-18 11:45:00', 'Heart palpitations', 'Racing heart, dizziness, chest flutter', 'Paroxysmal atrial fibrillation', 'Beta-blocker therapy, anticoagulation', 'Metoprolol 50mg twice daily, Warfarin 5mg daily', '2024-01-25', 'CON-1705582500-1004'),
('D001234567', 'P005678901', '2024-01-19 16:20:00', 'Hypertension check', 'Headaches, blurred vision', 'Hypertensive crisis', 'Immediate BP control, lifestyle modifications', 'Amlodipine 10mg daily, Lisinopril 20mg daily', '2024-01-26', 'CON-1705665600-1005');

-- Insert sample operations
INSERT INTO operation (doctor_ssn, patient_ssn, operation_date, operation_type, description, complications, allergies, anesthesia_type, duration_hours, status, reference_number) VALUES
('D003456789', 'P001234567', '2024-01-25 08:00:00', 'Coronary Angioplasty', 'Percutaneous coronary intervention for blocked LAD artery', 'None', 'NKDA', 'Local anesthesia with sedation', 2.5, 'Completed', 'OP-1706166000-2001'),
('D005678901', 'P003456789', '2024-01-30 10:30:00', 'Knee Arthroscopy', 'Diagnostic arthroscopy for chronic knee pain', 'Minimal bleeding', 'Penicillin allergy', 'General anesthesia', 1.5, 'Completed', 'OP-1706598600-2002'),
('D003456789', 'P004567890', '2024-02-05 07:45:00', 'Appendectomy', 'Laparoscopic appendectomy for acute appendicitis', 'None', 'NKDA', 'General anesthesia', 1.0, 'Scheduled', 'OP-1707118500-2003');

-- Insert sample diagnoses
INSERT INTO diagnosis (doctor_ssn, patient_ssn, diagnosis_date, diagnosis_name, description, severity, treatment_status, reference_number) VALUES
('D001234567', 'P001234567', '2024-01-15 10:30:00', 'Coronary Artery Disease', 'Significant stenosis in left anterior descending artery', 'Moderate', 'Under Treatment', 'DX-1705314600-3001'),
('D002345678', 'P002345678', '2024-01-16 15:00:00', 'Viral Upper Respiratory Infection', 'Common cold with secondary bacterial infection', 'Mild', 'Resolved', 'DX-1705406400-3002'),
('D001234567', 'P003456789', '2024-01-17 09:45:00', 'Type 2 Diabetes Mellitus', 'Poorly controlled diabetes with HbA1c of 9.2%', 'Moderate', 'Under Treatment', 'DX-1705491100-3003'),
('D004567890', 'P004567890', '2024-01-18 12:15:00', 'Atrial Fibrillation', 'Paroxysmal atrial fibrillation with rapid ventricular response', 'Moderate', 'Under Treatment', 'DX-1705584300-3004'),
('D001234567', 'P005678901', '2024-01-19 16:50:00', 'Hypertensive Crisis', 'Severe hypertension with target organ damage', 'Severe', 'Under Treatment', 'DX-1705667400-3005');

-- Insert sample medical administration records
INSERT INTO medical_administration (staff_ssn, patient_ssn, administration_date, medicines, dosage, allergies, notes, reference_number) VALUES
('S001234567', 'P001234567', '2024-01-15 18:00:00', 'Aspirin, Metoprolol', '81mg, 25mg', 'NKDA', 'Patient educated on medication compliance', 'MA-1705334400-4001'),
('S003456789', 'P002345678', '2024-01-16 20:30:00', 'Paracetamol, Cough syrup', '500mg, 5ml', 'NKDA', 'Mother instructed on dosing schedule', 'MA-1705434600-4002'),
('S002345678', 'P003456789', '2024-01-17 14:15:00', 'Metformin, Insulin', '500mg, 10 units', 'NKDA', 'Blood glucose monitoring instructions given', 'MA-1705507700-4003'),
('S001234567', 'P004567890', '2024-01-18 19:45:00', 'Metoprolol, Warfarin', '50mg, 5mg', 'NKDA', 'INR monitoring scheduled', 'MA-1705601100-4004'),
('S003456789', 'P005678901', '2024-01-19 21:20:00', 'Amlodipine, Lisinopril', '10mg, 20mg', 'NKDA', 'Blood pressure monitoring protocol initiated', 'MA-1705696800-4005');

-- Insert sample payment records
INSERT INTO payments (patient_ssn, service_type, service_id, amount, bank_name, transaction_id, payment_status) VALUES
('P001234567', 'Consultation', 1, 150.00, 'Commercial Bank', 'TXN-CB-20240115-001', 'Completed'),
('P001234567', 'Operation', 1, 5000.00, 'Commercial Bank', 'TXN-CB-20240125-002', 'Completed'),
('P002345678', 'Consultation', 2, 100.00, 'Awash Bank', 'TXN-AW-20240116-001', 'Completed'),
('P003456789', 'Consultation', 3, 120.00, 'Telebirr', 'TXN-TB-20240117-001', 'Completed'),
('P004567890', 'Consultation', 4, 200.00, 'Abyssinia Bank', 'TXN-AB-20240118-001', 'Completed'),
('P005678901', 'Consultation', 5, 180.00, 'Commercial Bank', 'TXN-CB-20240119-001', 'Completed'),
('P003456789', 'Operation', 2, 3500.00, 'Awash Bank', 'TXN-AW-20240130-002', 'Pending');

-- Insert sample external health office
INSERT INTO external_health_office (office_name, location, contact_person, email, phone, username, password) VALUES
('Bale Zone Health Office', 'Robe, Bale Zone', 'Dr. Bekele Mamo', 'health@balezone.gov.et', '+251-922-999999', 'external_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample patient transfer
INSERT INTO patient_transfer (patient_ssn, from_office_id, to_hospital_id, transfer_date, reason, medical_documents, status, reference_number) VALUES
('P004567890', 1, 1, '2024-02-01 14:30:00', 'Requires specialized cardiac surgery not available at local facility', 'ECG reports, Echocardiogram, Blood work results', 'Received', 'TR-1706796600-5001');