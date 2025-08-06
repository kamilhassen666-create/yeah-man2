<?php
require_once '../includes/functions.php';
require_login(['patient']);

$patient_info = get_user_info($_SESSION['user_id'], 'patient');
$record_type = $_GET['type'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Get records based on filters
$consultations = search_records($_SESSION['user_id'], 'consultation', $date_from, $date_to);
$operations = search_records($_SESSION['user_id'], 'operation', $date_from, $date_to);
$diagnoses = search_records($_SESSION['user_id'], 'diagnosis', $date_from, $date_to);
$medications = search_records($_SESSION['user_id'], 'medication', $date_from, $date_to);

// Get file uploads
$file_query = "SELECT * FROM file_uploads WHERE patient_ssn = ? ORDER BY upload_date DESC";
$files = getRows($file_query, [$_SESSION['user_id']]);

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Patient Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="nav-brand">
            <i class="fas fa-hospital"></i>
            <span>Goba Hospital</span>
        </div>
        <div class="nav-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="active">
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="search.php">
                        <i class="fas fa-search"></i>
                        <span>Search Records</span>
                    </a>
                </li>
                <li>
                    <a href="payments.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="appointments.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1><i class="fas fa-notes-medical"></i> Medical Records</h1>
                <p>View and manage your complete medical history</p>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <form method="GET" action="" class="filters-form">
                    <div class="filter-group">
                        <label for="type">Record Type:</label>
                        <select id="type" name="type" class="form-control">
                            <option value="all" <?php echo $record_type === 'all' ? 'selected' : ''; ?>>All Records</option>
                            <option value="consultation" <?php echo $record_type === 'consultation' ? 'selected' : ''; ?>>Consultations</option>
                            <option value="operation" <?php echo $record_type === 'operation' ? 'selected' : ''; ?>>Operations</option>
                            <option value="diagnosis" <?php echo $record_type === 'diagnosis' ? 'selected' : ''; ?>>Diagnoses</option>
                            <option value="medication" <?php echo $record_type === 'medication' ? 'selected' : ''; ?>>Medications</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="records.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Record Tabs -->
            <div class="record-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="consultations">
                        <i class="fas fa-stethoscope"></i> Consultations (<?php echo count($consultations ?: []); ?>)
                    </button>
                    <button class="tab-btn" data-tab="operations">
                        <i class="fas fa-procedures"></i> Operations (<?php echo count($operations ?: []); ?>)
                    </button>
                    <button class="tab-btn" data-tab="diagnoses">
                        <i class="fas fa-diagnoses"></i> Diagnoses (<?php echo count($diagnoses ?: []); ?>)
                    </button>
                    <button class="tab-btn" data-tab="medications">
                        <i class="fas fa-pills"></i> Medications (<?php echo count($medications ?: []); ?>)
                    </button>
                    <button class="tab-btn" data-tab="files">
                        <i class="fas fa-file-medical"></i> Files (<?php echo count($files ?: []); ?>)
                    </button>
                </div>

                <!-- Consultations Tab -->
                <div class="tab-content active" id="consultations">
                    <h3><i class="fas fa-stethoscope"></i> Consultations</h3>
                    <?php if (!empty($consultations)): ?>
                        <div class="records-grid">
                            <?php foreach ($consultations as $consultation): ?>
                                <div class="record-card consultation-card">
                                    <div class="record-header">
                                        <h4>Dr. <?php echo htmlspecialchars($consultation['doctor_name']); ?></h4>
                                        <span class="record-date"><?php echo format_datetime($consultation['consultation_date']); ?></span>
                                    </div>
                                    <div class="record-body">
                                        <div class="record-info">
                                            <label>Symptoms:</label>
                                            <p><?php echo htmlspecialchars($consultation['symptoms'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Diagnosis:</label>
                                            <p><?php echo htmlspecialchars($consultation['diagnosis'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Treatment:</label>
                                            <p><?php echo htmlspecialchars($consultation['treatment'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <?php if (!empty($consultation['notes'])): ?>
                                        <div class="record-info">
                                            <label>Notes:</label>
                                            <p><?php echo htmlspecialchars($consultation['notes']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-footer">
                                        <span class="reference-number">Ref: <?php echo htmlspecialchars($consultation['reference_number']); ?></span>
                                        <span class="status <?php echo strtolower($consultation['status']); ?>">
                                            <?php echo $consultation['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-stethoscope"></i>
                            <h4>No Consultations Found</h4>
                            <p>No consultation records match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Operations Tab -->
                <div class="tab-content" id="operations">
                    <h3><i class="fas fa-procedures"></i> Operations</h3>
                    <?php if (!empty($operations)): ?>
                        <div class="records-grid">
                            <?php foreach ($operations as $operation): ?>
                                <div class="record-card operation-card">
                                    <div class="record-header">
                                        <h4><?php echo htmlspecialchars($operation['operation_type']); ?></h4>
                                        <span class="record-date"><?php echo format_datetime($operation['operation_date']); ?></span>
                                    </div>
                                    <div class="record-body">
                                        <div class="record-info">
                                            <label>Surgeon:</label>
                                            <p>Dr. <?php echo htmlspecialchars($operation['doctor_name']); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Description:</label>
                                            <p><?php echo htmlspecialchars($operation['description'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <?php if (!empty($operation['complications'])): ?>
                                        <div class="record-info">
                                            <label>Complications:</label>
                                            <p><?php echo htmlspecialchars($operation['complications']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($operation['pre_conditions'])): ?>
                                        <div class="record-info">
                                            <label>Pre-conditions:</label>
                                            <p><?php echo htmlspecialchars($operation['pre_conditions']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-footer">
                                        <span class="reference-number">Ref: <?php echo htmlspecialchars($operation['reference_number']); ?></span>
                                        <span class="status <?php echo strtolower($operation['status']); ?>">
                                            <?php echo $operation['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-procedures"></i>
                            <h4>No Operations Found</h4>
                            <p>No surgical records match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Diagnoses Tab -->
                <div class="tab-content" id="diagnoses">
                    <h3><i class="fas fa-diagnoses"></i> Diagnoses</h3>
                    <?php if (!empty($diagnoses)): ?>
                        <div class="records-grid">
                            <?php foreach ($diagnoses as $diagnosis): ?>
                                <div class="record-card diagnosis-card">
                                    <div class="record-header">
                                        <h4><?php echo htmlspecialchars($diagnosis['diagnosis_name']); ?></h4>
                                        <span class="record-date"><?php echo format_datetime($diagnosis['diagnosis_date']); ?></span>
                                    </div>
                                    <div class="record-body">
                                        <div class="record-info">
                                            <label>Doctor:</label>
                                            <p>Dr. <?php echo htmlspecialchars($diagnosis['doctor_name']); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Description:</label>
                                            <p><?php echo htmlspecialchars($diagnosis['description'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Severity:</label>
                                            <span class="severity <?php echo strtolower($diagnosis['severity']); ?>">
                                                <?php echo $diagnosis['severity']; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($diagnosis['icd_code'])): ?>
                                        <div class="record-info">
                                            <label>ICD Code:</label>
                                            <p><?php echo htmlspecialchars($diagnosis['icd_code']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($diagnosis['notes'])): ?>
                                        <div class="record-info">
                                            <label>Notes:</label>
                                            <p><?php echo htmlspecialchars($diagnosis['notes']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-footer">
                                        <span class="reference-number">Ref: <?php echo htmlspecialchars($diagnosis['reference_number']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-diagnoses"></i>
                            <h4>No Diagnoses Found</h4>
                            <p>No diagnosis records match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Medications Tab -->
                <div class="tab-content" id="medications">
                    <h3><i class="fas fa-pills"></i> Medications</h3>
                    <?php if (!empty($medications)): ?>
                        <div class="records-grid">
                            <?php foreach ($medications as $medication): ?>
                                <div class="record-card medication-card">
                                    <div class="record-header">
                                        <h4><?php echo htmlspecialchars($medication['medication_name']); ?></h4>
                                        <span class="record-date"><?php echo format_datetime($medication['administration_date']); ?></span>
                                    </div>
                                    <div class="record-body">
                                        <div class="record-info">
                                            <label>Administered by:</label>
                                            <p><?php echo htmlspecialchars($medication['staff_name']); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Dosage:</label>
                                            <p><?php echo htmlspecialchars($medication['dosage']); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Frequency:</label>
                                            <p><?php echo htmlspecialchars($medication['frequency'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <div class="record-info">
                                            <label>Duration:</label>
                                            <p><?php echo htmlspecialchars($medication['duration'] ?: 'Not specified'); ?></p>
                                        </div>
                                        <?php if (!empty($medication['notes'])): ?>
                                        <div class="record-info">
                                            <label>Notes:</label>
                                            <p><?php echo htmlspecialchars($medication['notes']); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-footer">
                                        <span class="reference-number">Ref: <?php echo htmlspecialchars($medication['reference_number']); ?></span>
                                        <span class="allergies-checked <?php echo $medication['allergies_checked'] ? 'checked' : 'not-checked'; ?>">
                                            <i class="fas fa-<?php echo $medication['allergies_checked'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                            Allergies <?php echo $medication['allergies_checked'] ? 'Checked' : 'Not Checked'; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-pills"></i>
                            <h4>No Medications Found</h4>
                            <p>No medication records match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Files Tab -->
                <div class="tab-content" id="files">
                    <h3><i class="fas fa-file-medical"></i> Medical Files</h3>
                    <?php if (!empty($files)): ?>
                        <div class="files-grid">
                            <?php foreach ($files as $file): ?>
                                <div class="file-card">
                                    <div class="file-icon">
                                        <i class="fas fa-file-<?php 
                                            echo match($file['file_type']) {
                                                'pdf' => 'pdf',
                                                'doc', 'docx' => 'word',
                                                'jpg', 'jpeg', 'png' => 'image',
                                                default => 'alt'
                                            };
                                        ?>"></i>
                                    </div>
                                    <div class="file-info">
                                        <h4><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                        <p class="file-category"><?php echo htmlspecialchars($file['category']); ?></p>
                                        <p class="file-date">Uploaded: <?php echo format_datetime($file['upload_date']); ?></p>
                                        <p class="file-size"><?php echo number_format($file['file_size'] / 1024, 1); ?> KB</p>
                                        <?php if (!empty($file['description'])): ?>
                                            <p class="file-description"><?php echo htmlspecialchars($file['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="file-actions">
                                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" download class="btn btn-sm btn-secondary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-file-medical"></i>
                            <h4>No Files Found</h4>
                            <p>No medical files have been uploaded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <style>
        .filters-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }

        .record-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 1rem 1.5rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #6b7280;
        }

        .tab-btn:hover,
        .tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: #f8fafc;
        }

        .tab-content {
            display: none;
            padding: 2rem;
        }

        .tab-content.active {
            display: block;
        }

        .tab-content h3 {
            margin-bottom: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .records-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .record-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .record-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .consultation-card { border-left: 4px solid #3b82f6; }
        .operation-card { border-left: 4px solid #ef4444; }
        .diagnosis-card { border-left: 4px solid #f59e0b; }
        .medication-card { border-left: 4px solid #10b981; }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .record-header h4 {
            color: #1e293b;
            margin: 0;
        }

        .record-date {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .record-body {
            margin-bottom: 1rem;
        }

        .record-info {
            margin-bottom: 0.75rem;
        }

        .record-info label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .record-info p {
            color: #1e293b;
            margin: 0;
            line-height: 1.5;
        }

        .record-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .reference-number {
            font-size: 0.875rem;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }

        .status, .severity {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.completed { background: #dcfce7; color: #166534; }
        .status.scheduled { background: #dbeafe; color: #1d4ed8; }
        .status.cancelled { background: #fef2f2; color: #dc2626; }

        .severity.mild { background: #ecfdf5; color: #059669; }
        .severity.moderate { background: #fef3c7; color: #d97706; }
        .severity.severe { background: #fee2e2; color: #dc2626; }
        .severity.critical { background: #fef2f2; color: #991b1b; }

        .allergies-checked {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .allergies-checked.checked {
            color: #059669;
        }

        .allergies-checked.not-checked {
            color: #dc2626;
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .file-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .file-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .file-icon {
            font-size: 2.5rem;
            color: #6b7280;
        }

        .file-info {
            flex: 1;
        }

        .file-info h4 {
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }

        .file-category {
            color: #2563eb;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .file-date, .file-size {
            color: #6b7280;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .file-description {
            color: #374151;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .file-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .no-records {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .no-records i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-records h4 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        @media (max-width: 768px) {
            .filters-form {
                grid-template-columns: 1fr;
            }

            .tab-buttons {
                flex-direction: column;
            }

            .records-grid {
                grid-template-columns: 1fr;
            }

            .files-grid {
                grid-template-columns: 1fr;
            }

            .file-card {
                flex-direction: column;
                text-align: center;
            }

            .file-actions {
                flex-direction: row;
                width: 100%;
            }
        }
    </style>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                btn.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        // Check URL parameters to activate correct tab
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('type');
        if (activeTab && ['consultation', 'operation', 'diagnosis', 'medication'].includes(activeTab)) {
            const tabName = activeTab === 'consultation' ? 'consultations' : 
                           activeTab === 'operation' ? 'operations' : 
                           activeTab === 'diagnosis' ? 'diagnoses' : 'medications';
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>