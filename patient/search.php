<?php
require_once '../includes/functions.php';
require_login(['patient']);

$search_query = $_GET['q'] ?? '';
$search_type = $_GET['type'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$search_results = [];
$total_results = 0;

if (!empty($search_query)) {
    // Search based on type
    switch ($search_type) {
        case 'consultation':
            $query = "SELECT c.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name, 'consultation' as record_type
                      FROM consultation c 
                      JOIN doctor d ON c.doctor_ssn = d.ssn 
                      WHERE c.patient_ssn = ? AND (
                          c.symptoms LIKE ? OR 
                          c.diagnosis LIKE ? OR 
                          c.treatment LIKE ? OR 
                          c.notes LIKE ? OR 
                          c.reference_number LIKE ?
                      )";
            $params = [$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"];
            
            if ($date_from) {
                $query .= " AND DATE(c.consultation_date) >= ?";
                $params[] = $date_from;
            }
            if ($date_to) {
                $query .= " AND DATE(c.consultation_date) <= ?";
                $params[] = $date_to;
            }
            
            $query .= " ORDER BY c.consultation_date DESC";
            $search_results = getRows($query, $params) ?: [];
            break;
            
        case 'diagnosis':
            $query = "SELECT d.*, CONCAT(doc.first_name, ' ', doc.last_name) as doctor_name, 'diagnosis' as record_type
                      FROM diagnosis d 
                      JOIN doctor doc ON d.doctor_ssn = doc.ssn 
                      WHERE d.patient_ssn = ? AND (
                          d.diagnosis_name LIKE ? OR 
                          d.description LIKE ? OR 
                          d.icd_code LIKE ? OR 
                          d.notes LIKE ? OR 
                          d.reference_number LIKE ?
                      )";
            $params = [$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"];
            
            if ($date_from) {
                $query .= " AND DATE(d.diagnosis_date) >= ?";
                $params[] = $date_from;
            }
            if ($date_to) {
                $query .= " AND DATE(d.diagnosis_date) <= ?";
                $params[] = $date_to;
            }
            
            $query .= " ORDER BY d.diagnosis_date DESC";
            $search_results = getRows($query, $params) ?: [];
            break;
            
        case 'medication':
            $query = "SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name, 'medication' as record_type
                      FROM medical_administration m 
                      JOIN medical_staff s ON m.staff_ssn = s.ssn 
                      WHERE m.patient_ssn = ? AND (
                          m.medication_name LIKE ? OR 
                          m.dosage LIKE ? OR 
                          m.frequency LIKE ? OR 
                          m.notes LIKE ? OR 
                          m.reference_number LIKE ?
                      )";
            $params = [$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"];
            
            if ($date_from) {
                $query .= " AND DATE(m.administration_date) >= ?";
                $params[] = $date_from;
            }
            if ($date_to) {
                $query .= " AND DATE(m.administration_date) <= ?";
                $params[] = $date_to;
            }
            
            $query .= " ORDER BY m.administration_date DESC";
            $search_results = getRows($query, $params) ?: [];
            break;
            
        default: // 'all'
            // Search across all record types
            $consultations = getRows("
                SELECT c.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name, 'consultation' as record_type,
                       c.consultation_date as record_date
                FROM consultation c 
                JOIN doctor d ON c.doctor_ssn = d.ssn 
                WHERE c.patient_ssn = ? AND (
                    c.symptoms LIKE ? OR c.diagnosis LIKE ? OR c.treatment LIKE ? OR c.notes LIKE ? OR c.reference_number LIKE ?
                )" . ($date_from ? " AND DATE(c.consultation_date) >= ?" : "") . ($date_to ? " AND DATE(c.consultation_date) <= ?" : "") . "
                ORDER BY c.consultation_date DESC",
                array_filter([$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", $date_from, $date_to])
            ) ?: [];
            
            $diagnoses = getRows("
                SELECT d.*, CONCAT(doc.first_name, ' ', doc.last_name) as doctor_name, 'diagnosis' as record_type,
                       d.diagnosis_date as record_date
                FROM diagnosis d 
                JOIN doctor doc ON d.doctor_ssn = doc.ssn 
                WHERE d.patient_ssn = ? AND (
                    d.diagnosis_name LIKE ? OR d.description LIKE ? OR d.icd_code LIKE ? OR d.notes LIKE ? OR d.reference_number LIKE ?
                )" . ($date_from ? " AND DATE(d.diagnosis_date) >= ?" : "") . ($date_to ? " AND DATE(d.diagnosis_date) <= ?" : "") . "
                ORDER BY d.diagnosis_date DESC",
                array_filter([$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", $date_from, $date_to])
            ) ?: [];
            
            $medications = getRows("
                SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name, 'medication' as record_type,
                       m.administration_date as record_date
                FROM medical_administration m 
                JOIN medical_staff s ON m.staff_ssn = s.ssn 
                WHERE m.patient_ssn = ? AND (
                    m.medication_name LIKE ? OR m.dosage LIKE ? OR m.frequency LIKE ? OR m.notes LIKE ? OR m.reference_number LIKE ?
                )" . ($date_from ? " AND DATE(m.administration_date) >= ?" : "") . ($date_to ? " AND DATE(m.administration_date) <= ?" : "") . "
                ORDER BY m.administration_date DESC",
                array_filter([$_SESSION['user_id'], "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%", $date_from, $date_to])
            ) ?: [];
            
            // Combine and sort by date
            $search_results = array_merge($consultations, $diagnoses, $medications);
            usort($search_results, function($a, $b) {
                return strtotime($b['record_date']) - strtotime($a['record_date']);
            });
            break;
    }
    
    $total_results = count($search_results);
}

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Records - Patient Portal</title>
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
                <li>
                    <a href="records.php">
                        <i class="fas fa-notes-medical"></i>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li class="active">
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
                <h1><i class="fas fa-search"></i> Search Medical Records</h1>
                <p>Find specific medical records using keywords, dates, or reference numbers</p>
            </div>

            <!-- Search Form -->
            <div class="search-container">
                <form method="GET" action="" class="search-form">
                    <div class="search-input-group">
                        <div class="search-main">
                            <input type="text" name="q" class="search-input" placeholder="Search symptoms, diagnoses, medications, notes, or reference numbers..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>" autofocus>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    
                    <div class="search-filters">
                        <div class="filter-group">
                            <label for="type">Search In:</label>
                            <select id="type" name="type" class="form-control">
                                <option value="all" <?php echo $search_type === 'all' ? 'selected' : ''; ?>>All Records</option>
                                <option value="consultation" <?php echo $search_type === 'consultation' ? 'selected' : ''; ?>>Consultations</option>
                                <option value="diagnosis" <?php echo $search_type === 'diagnosis' ? 'selected' : ''; ?>>Diagnoses</option>
                                <option value="medication" <?php echo $search_type === 'medication' ? 'selected' : ''; ?>>Medications</option>
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
                            <a href="search.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <?php if (!empty($search_query)): ?>
                <div class="search-results">
                    <div class="results-header">
                        <h3><i class="fas fa-list"></i> Search Results</h3>
                        <span class="results-count"><?php echo $total_results; ?> result(s) found for "<?php echo htmlspecialchars($search_query); ?>"</span>
                    </div>

                    <?php if ($total_results > 0): ?>
                        <div class="results-grid">
                            <?php foreach ($search_results as $result): ?>
                                <div class="result-card <?php echo $result['record_type']; ?>-result">
                                    <div class="result-header">
                                        <div class="result-type">
                                            <i class="fas fa-<?php 
                                                echo match($result['record_type']) {
                                                    'consultation' => 'stethoscope',
                                                    'diagnosis' => 'diagnoses',
                                                    'medication' => 'pills',
                                                    default => 'file-medical'
                                                };
                                            ?>"></i>
                                            <span><?php echo ucfirst($result['record_type']); ?></span>
                                        </div>
                                        <span class="result-date">
                                            <?php echo format_datetime($result['record_date']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="result-body">
                                        <?php if ($result['record_type'] === 'consultation'): ?>
                                            <h4>Dr. <?php echo htmlspecialchars($result['doctor_name']); ?></h4>
                                            <div class="result-content">
                                                <?php if (!empty($result['symptoms'])): ?>
                                                    <p><strong>Symptoms:</strong> <?php echo htmlspecialchars($result['symptoms']); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($result['diagnosis'])): ?>
                                                    <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($result['diagnosis']); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($result['treatment'])): ?>
                                                    <p><strong>Treatment:</strong> <?php echo htmlspecialchars($result['treatment']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($result['record_type'] === 'diagnosis'): ?>
                                            <h4><?php echo htmlspecialchars($result['diagnosis_name']); ?></h4>
                                            <div class="result-content">
                                                <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($result['doctor_name']); ?></p>
                                                <?php if (!empty($result['description'])): ?>
                                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($result['description']); ?></p>
                                                <?php endif; ?>
                                                <p><strong>Severity:</strong> 
                                                    <span class="severity <?php echo strtolower($result['severity']); ?>">
                                                        <?php echo $result['severity']; ?>
                                                    </span>
                                                </p>
                                            </div>
                                        <?php elseif ($result['record_type'] === 'medication'): ?>
                                            <h4><?php echo htmlspecialchars($result['medication_name']); ?></h4>
                                            <div class="result-content">
                                                <p><strong>Administered by:</strong> <?php echo htmlspecialchars($result['staff_name']); ?></p>
                                                <p><strong>Dosage:</strong> <?php echo htmlspecialchars($result['dosage']); ?></p>
                                                <?php if (!empty($result['frequency'])): ?>
                                                    <p><strong>Frequency:</strong> <?php echo htmlspecialchars($result['frequency']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="result-footer">
                                        <span class="reference-number">Ref: <?php echo htmlspecialchars($result['reference_number']); ?></span>
                                        <a href="records.php?type=<?php echo $result['record_type']; ?>" class="view-all-link">
                                            View All <?php echo ucfirst($result['record_type']); ?>s
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h4>No Results Found</h4>
                            <p>No records match your search criteria. Try:</p>
                            <ul>
                                <li>Using different keywords</li>
                                <li>Checking your spelling</li>
                                <li>Searching in all record types</li>
                                <li>Expanding your date range</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Search Tips -->
                <div class="search-tips">
                    <h3><i class="fas fa-lightbulb"></i> Search Tips</h3>
                    <div class="tips-grid">
                        <div class="tip-card">
                            <i class="fas fa-keyboard"></i>
                            <h4>Keywords</h4>
                            <p>Search for symptoms, diagnosis names, medication names, or any text in your medical records.</p>
                            <div class="example">
                                <strong>Example:</strong> "headache", "hypertension", "aspirin"
                            </div>
                        </div>
                        
                        <div class="tip-card">
                            <i class="fas fa-hashtag"></i>
                            <h4>Reference Numbers</h4>
                            <p>Use reference numbers to find specific consultations, diagnoses, or medication records.</p>
                            <div class="example">
                                <strong>Example:</strong> "CONS20240115001", "DIAG20240116001"
                            </div>
                        </div>
                        
                        <div class="tip-card">
                            <i class="fas fa-filter"></i>
                            <h4>Filters</h4>
                            <p>Narrow down your search by record type and date range for more precise results.</p>
                            <div class="example">
                                <strong>Tip:</strong> Use "All Records" to search across everything
                            </div>
                        </div>
                        
                        <div class="tip-card">
                            <i class="fas fa-calendar"></i>
                            <h4>Date Range</h4>
                            <p>Search within specific time periods to find records from particular visits or treatments.</p>
                            <div class="example">
                                <strong>Example:</strong> Last month's consultations
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        .search-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .search-input-group {
            margin-bottom: 1.5rem;
        }

        .search-main {
            display: flex;
            gap: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .search-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-btn:hover {
            background: #1d4ed8;
        }

        .search-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .search-results {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .results-header {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-header h3 {
            margin: 0;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .results-count {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .results-grid {
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .result-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .result-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .consultation-result { border-left: 4px solid #3b82f6; }
        .diagnosis-result { border-left: 4px solid #f59e0b; }
        .medication-result { border-left: 4px solid #10b981; }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .result-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        .result-date {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .result-body h4 {
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .result-content p {
            margin-bottom: 0.5rem;
            line-height: 1.5;
            color: #374151;
        }

        .result-content strong {
            color: #1e293b;
        }

        .result-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .reference-number {
            font-size: 0.875rem;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }

        .view-all-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .severity {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .severity.mild { background: #ecfdf5; color: #059669; }
        .severity.moderate { background: #fef3c7; color: #d97706; }
        .severity.severe { background: #fee2e2; color: #dc2626; }
        .severity.critical { background: #fef2f2; color: #991b1b; }

        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-results h4 {
            margin-bottom: 1rem;
            color: #374151;
        }

        .no-results ul {
            text-align: left;
            max-width: 300px;
            margin: 1rem auto 0;
        }

        .search-tips {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .search-tips h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .tip-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .tip-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .tip-card i {
            font-size: 2rem;
            color: #2563eb;
            margin-bottom: 1rem;
        }

        .tip-card h4 {
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .tip-card p {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .example {
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #374151;
        }

        @media (max-width: 768px) {
            .search-main {
                flex-direction: column;
            }

            .search-filters {
                grid-template-columns: 1fr;
            }

            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .result-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .tips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script src="../assets/js/script.js"></script>
</body>
</html>