<?php
require_once '../includes/config.php';
requireUserType(['doctor']);

$pdo = getDBConnection();

// Get doctor information
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all patients for dropdown
$stmt = $pdo->prepare("SELECT id, patient_id, first_name, last_name FROM patients ORDER BY first_name, last_name");
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = sanitizeInput($_POST['patient_id']);
    $consultation_date = sanitizeInput($_POST['consultation_date']);
    $symptoms = sanitizeInput($_POST['symptoms']);
    $diagnosis = sanitizeInput($_POST['diagnosis']);
    $prescription = sanitizeInput($_POST['prescription']);
    $notes = sanitizeInput($_POST['notes']);
    
    if (empty($patient_id) || empty($consultation_date) || empty($symptoms) || empty($diagnosis)) {
        setFlashMessage('danger', 'Please fill in all required fields.');
    } else {
        try {
            // Handle audio file upload
            $audio_file = '';
            if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
                $allowed_types = ['audio/wav', 'audio/mp3', 'audio/mpeg'];
                $file_type = $_FILES['audio_file']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = '../uploads/audio/';
                    $file_extension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
                    $audio_file = 'consultation_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $audio_file;
                    
                    if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_path)) {
                        $audio_file = 'uploads/audio/' . $audio_file;
                    } else {
                        setFlashMessage('warning', 'Audio file upload failed, but consultation will be saved.');
                        $audio_file = '';
                    }
                } else {
                    setFlashMessage('warning', 'Invalid audio file type. Only WAV, MP3 files are allowed.');
                    $audio_file = '';
                }
            }
            
            $reference_id = generateReferenceId('CONS');
            $stmt = $pdo->prepare("INSERT INTO consultations (reference_id, patient_id, doctor_id, consultation_date, symptoms, diagnosis, prescription, notes, audio_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$reference_id, $patient_id, $_SESSION['user_id'], $consultation_date, $symptoms, $diagnosis, $prescription, $notes, $audio_file]);
            
            setFlashMessage('success', 'Consultation recorded successfully. Reference ID: ' . $reference_id);
            header('Location: consultations.php');
            exit();
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error recording consultation: ' . $e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-3">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <img src="../assets/images/doctor-avatar.png" alt="Doctor" class="profile-avatar mb-3">
                <h5 class="text-white">Dr. <?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50"><?php echo $doctor['specialization']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users"></i>Patients
                </a>
                <a class="nav-link" href="consultations.php">
                    <i class="fas fa-stethoscope"></i>Consultations
                </a>
                <a class="nav-link" href="surgeries.php">
                    <i class="fas fa-procedures"></i>Surgeries
                </a>
                <a class="nav-link" href="diagnoses.php">
                    <i class="fas fa-notes-medical"></i>Diagnoses
                </a>
                <a class="nav-link" href="referrals.php">
                    <i class="fas fa-exchange-alt"></i>Referrals
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i>Profile
                </a>
            </nav>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="main-content">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2><i class="fas fa-stethoscope me-2"></i>Record Consultation</h2>
                    <p class="text-muted">Record a new patient consultation with comprehensive medical information</p>
                </div>
            </div>
            
            <!-- Consultation Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-2"></i>New Consultation
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" data-validate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patient_id" class="form-label">Patient *</label>
                                            <select class="form-select" id="patient_id" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php foreach ($patients as $patient): ?>
                                                    <option value="<?php echo $patient['id']; ?>">
                                                        <?php echo $patient['patient_id'] . ' - ' . $patient['first_name'] . ' ' . $patient['last_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="consultation_date" class="form-label">Consultation Date & Time *</label>
                                            <input type="datetime-local" class="form-control" id="consultation_date" name="consultation_date" data-datetime required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="symptoms" class="form-label">Symptoms *</label>
                                    <textarea class="form-control" id="symptoms" name="symptoms" rows="4" placeholder="Describe patient symptoms in detail..." required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="diagnosis" class="form-label">Diagnosis *</label>
                                    <textarea class="form-control" id="diagnosis" name="diagnosis" rows="4" placeholder="Enter medical diagnosis..." required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prescription" class="form-label">Prescription</label>
                                    <textarea class="form-control" id="prescription" name="prescription" rows="4" placeholder="Enter prescribed medications and dosage..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes or recommendations..."></textarea>
                                </div>
                                
                                <!-- Audio Recording Section -->
                                <div class="mb-4">
                                    <label class="form-label">Audio Recording</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-primary" id="recordButton">
                                                        <i class="fas fa-microphone me-2"></i>Start Recording
                                                    </button>
                                                    <small class="text-muted d-block mt-2">
                                                        Record consultation audio for better documentation
                                                    </small>
                                                </div>
                                                <div class="col-md-6">
                                                    <audio id="audioPlayer" controls style="display: none; width: 100%;"></audio>
                                                    <div class="mt-2">
                                                        <label for="audio_file" class="form-label">Or Upload Audio File</label>
                                                        <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/*">
                                                        <small class="text-muted">Supported formats: WAV, MP3</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Consultation
                                    </button>
                                    <a href="consultations.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Patient Search -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>Quick Patient Search
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="search_patient_id" class="form-label">Patient ID</label>
                                        <input type="text" class="form-control" id="search_patient_id" placeholder="Enter Patient ID">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="search_patient_name" class="form-label">Patient Name</label>
                                        <input type="text" class="form-control" id="search_patient_name" placeholder="Enter patient name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-outline-primary w-100" onclick="searchPatient()">
                                            <i class="fas fa-search me-2"></i>Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="searchResults"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function searchPatient() {
    const patientId = document.getElementById('search_patient_id').value;
    const patientName = document.getElementById('search_patient_name').value;
    
    if (!patientId && !patientName) {
        alert('Please enter either Patient ID or Patient Name');
        return;
    }
    
    // Simulate search functionality
    const resultsDiv = document.getElementById('searchResults');
    resultsDiv.innerHTML = '<div class="alert alert-info">Search functionality will be implemented with AJAX.</div>';
}

// Audio recording functionality is handled in assets/js/script.js
</script>

<?php include '../includes/footer.php'; ?>