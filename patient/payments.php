<?php
require_once '../includes/functions.php';
require_login(['patient']);

$patient_info = get_user_info($_SESSION['user_id'], 'patient');

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['make_payment'])) {
    $payment_type = sanitize_input($_POST['payment_type']);
    $amount = floatval($_POST['amount']);
    $bank_name = sanitize_input($_POST['bank_name']);
    $payment_method = sanitize_input($_POST['payment_method']);
    
    if ($amount > 0 && !empty($payment_type) && !empty($bank_name) && !empty($payment_method)) {
        $payment_result = process_payment($_SESSION['user_id'], $payment_type, $amount, $bank_name, $payment_method);
        
        if ($payment_result['success']) {
            redirect_with_message('payments.php', 'Payment initiated successfully! Transaction ID: ' . $payment_result['transaction_id'], 'success');
        } else {
            $error_message = $payment_result['message'];
        }
    } else {
        $error_message = 'Please fill in all required fields with valid values.';
    }
}

// Get payment history
$payment_query = "SELECT * FROM payment WHERE patient_ssn = ? ORDER BY payment_date DESC";
$payment_history = getRows($payment_query, [$_SESSION['user_id']]) ?: [];

// Calculate statistics
$total_paid = 0;
$pending_payments = 0;
$completed_payments = 0;

foreach ($payment_history as $payment) {
    if ($payment['payment_status'] === 'Completed') {
        $total_paid += $payment['amount'];
        $completed_payments++;
    } elseif ($payment['payment_status'] === 'Pending') {
        $pending_payments++;
    }
}

$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Patient Portal</title>
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
                <li>
                    <a href="search.php">
                        <i class="fas fa-search"></i>
                        <span>Search Records</span>
                    </a>
                </li>
                <li class="active">
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

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1><i class="fas fa-credit-card"></i> Payments & Billing</h1>
                <p>Manage your medical payments and view billing history</p>
            </div>

            <!-- Payment Statistics -->
            <div class="payment-stats">
                <div class="stat-card total-paid">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_paid, 2); ?> ETB</h3>
                        <p>Total Paid</p>
                    </div>
                </div>

                <div class="stat-card completed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_payments; ?></h3>
                        <p>Completed Payments</p>
                    </div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_payments; ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>

                <div class="stat-card total-transactions">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($payment_history); ?></h3>
                        <p>Total Transactions</p>
                    </div>
                </div>
            </div>

            <div class="payment-container">
                <!-- Make Payment Form -->
                <div class="payment-form-section">
                    <h3><i class="fas fa-plus-circle"></i> Make a Payment</h3>
                    
                    <form method="POST" action="" class="payment-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_type">Payment Type <span class="required">*</span></label>
                                <select id="payment_type" name="payment_type" class="form-control" required>
                                    <option value="">Select payment type</option>
                                    <option value="Consultation">Consultation Fee</option>
                                    <option value="Surgery">Surgery Fee</option>
                                    <option value="Medication">Medication Cost</option>
                                    <option value="Other">Other Medical Services</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="amount">Amount (ETB) <span class="required">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" 
                                       min="1" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="bank_name">Bank <span class="required">*</span></label>
                                <select id="bank_name" name="bank_name" class="form-control" required>
                                    <option value="">Select your bank</option>
                                    <option value="Commercial Bank">Commercial Bank of Ethiopia</option>
                                    <option value="Awash Bank">Awash Bank</option>
                                    <option value="Abyssinia Bank">Abyssinia Bank</option>
                                    <option value="Telebirr">Telebirr (Mobile Payment)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="payment_method">Payment Method <span class="required">*</span></label>
                                <select id="payment_method" name="payment_method" class="form-control" required>
                                    <option value="">Select method</option>
                                    <option value="Card">Credit/Debit Card</option>
                                    <option value="Mobile">Mobile Payment</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cash">Cash Payment</option>
                                </select>
                            </div>
                        </div>

                        <div class="payment-info">
                            <div class="info-card">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <h4>Payment Information</h4>
                                    <ul>
                                        <li>All payments are processed securely through Ethiopian banking partners</li>
                                        <li>Transaction fees may apply depending on your bank</li>
                                        <li>Payment confirmation will be sent to your registered email</li>
                                        <li>For cash payments, please visit the hospital billing office</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="make_payment" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Process Payment
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Banking Partners -->
                <div class="banking-partners">
                    <h3><i class="fas fa-university"></i> Supported Banks</h3>
                    <div class="banks-grid">
                        <div class="bank-card">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4>Commercial Bank of Ethiopia</h4>
                            <p>Cards, Mobile Banking, Branch Transfer</p>
                        </div>
                        
                        <div class="bank-card">
                            <div class="bank-logo">
                                <i class="fas fa-building"></i>
                            </div>
                            <h4>Awash Bank</h4>
                            <p>Cards, Online Banking, Mobile</p>
                        </div>
                        
                        <div class="bank-card">
                            <div class="bank-logo">
                                <i class="fas fa-landmark"></i>
                            </div>
                            <h4>Abyssinia Bank</h4>
                            <p>Cards, Internet Banking, ATM</p>
                        </div>
                        
                        <div class="bank-card">
                            <div class="bank-logo">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4>Telebirr</h4>
                            <p>Mobile Payment, QR Code, USSD</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="payment-history">
                <h3><i class="fas fa-history"></i> Payment History</h3>
                
                <?php if (!empty($payment_history)): ?>
                    <div class="table-container">
                        <table class="table sortable-table">
                            <thead>
                                <tr>
                                    <th data-sort="date">Date</th>
                                    <th data-sort="type">Type</th>
                                    <th data-sort="amount">Amount</th>
                                    <th data-sort="bank">Bank</th>
                                    <th data-sort="method">Method</th>
                                    <th data-sort="status">Status</th>
                                    <th data-sort="reference">Reference</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_history as $payment): ?>
                                    <tr>
                                        <td><?php echo format_datetime($payment['payment_date']); ?></td>
                                        <td>
                                            <span class="payment-type">
                                                <i class="fas fa-<?php 
                                                    echo match($payment['payment_type']) {
                                                        'Consultation' => 'stethoscope',
                                                        'Surgery' => 'procedures',
                                                        'Medication' => 'pills',
                                                        default => 'medical-file'
                                                    };
                                                ?>"></i>
                                                <?php echo htmlspecialchars($payment['payment_type']); ?>
                                            </span>
                                        </td>
                                        <td class="amount"><?php echo number_format($payment['amount'], 2); ?> <?php echo $payment['currency']; ?></td>
                                        <td><?php echo htmlspecialchars($payment['bank_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($payment['payment_status']); ?>">
                                                <?php echo $payment['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td class="reference"><?php echo htmlspecialchars($payment['reference_number']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewPayment('<?php echo $payment['id']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($payment['payment_status'] === 'Completed'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="downloadReceipt('<?php echo $payment['id']; ?>')">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-payments">
                        <i class="fas fa-receipt"></i>
                        <h4>No Payment History</h4>
                        <p>You haven't made any payments yet. Use the form above to make your first payment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Payment Details Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Payment Details</h3>
                <button class="modal-close" onclick="closeModal('paymentModal')">&times;</button>
            </div>
            <div class="modal-body" id="paymentDetails">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>

    <style>
        .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .total-paid .stat-icon { background: #10b981; }
        .completed .stat-icon { background: #3b82f6; }
        .pending .stat-icon { background: #f59e0b; }
        .total-transactions .stat-icon { background: #8b5cf6; }

        .stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #64748b;
            margin: 0;
        }

        .payment-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .payment-form-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-form-section h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .payment-info {
            margin: 1.5rem 0;
        }

        .info-card {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            gap: 1rem;
        }

        .info-card i {
            color: #2563eb;
            font-size: 1.25rem;
            margin-top: 0.25rem;
        }

        .info-card h4 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .info-card ul {
            margin: 0;
            padding-left: 1rem;
            color: #374151;
        }

        .info-card li {
            margin-bottom: 0.25rem;
        }

        .banking-partners {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .banking-partners h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .banks-grid {
            display: grid;
            gap: 1rem;
        }

        .bank-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .bank-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .bank-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            color: white;
            font-size: 1.25rem;
        }

        .bank-card h4 {
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .bank-card p {
            color: #6b7280;
            font-size: 0.8rem;
            margin: 0;
        }

        .payment-history {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-history h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .amount {
            font-weight: 600;
            color: #1e293b;
        }

        .reference {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.completed { background: #dcfce7; color: #166534; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.failed { background: #fef2f2; color: #dc2626; }
        .status.refunded { background: #f3f4f6; color: #374151; }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .no-payments {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .no-payments i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-payments h4 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-close:hover {
            color: #374151;
        }

        .modal-body {
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .payment-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-stats {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>

    <script>
        // Payment form handling
        document.getElementById('bank_name').addEventListener('change', function() {
            const paymentMethodSelect = document.getElementById('payment_method');
            const bankName = this.value;
            
            // Reset payment method options
            paymentMethodSelect.innerHTML = '<option value="">Select method</option>';
            
            if (bankName === 'Telebirr') {
                paymentMethodSelect.innerHTML += '<option value="Mobile">Mobile Payment</option>';
            } else if (bankName) {
                paymentMethodSelect.innerHTML += `
                    <option value="Card">Credit/Debit Card</option>
                    <option value="Mobile">Mobile Payment</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash Payment</option>
                `;
            }
        });

        // View payment details
        function viewPayment(paymentId) {
            // In a real application, this would fetch payment details via AJAX
            const modalBody = document.getElementById('paymentDetails');
            modalBody.innerHTML = `
                <div class="payment-detail-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading payment details...
                </div>
            `;
            
            openModal('paymentModal');
            
            // Simulate loading payment details
            setTimeout(() => {
                modalBody.innerHTML = `
                    <div class="payment-details">
                        <div class="detail-row">
                            <label>Transaction ID:</label>
                            <span>TXN202401${paymentId.padStart(6, '0')}</span>
                        </div>
                        <div class="detail-row">
                            <label>Payment Date:</label>
                            <span>${new Date().toLocaleDateString()}</span>
                        </div>
                        <div class="detail-row">
                            <label>Amount:</label>
                            <span>500.00 ETB</span>
                        </div>
                        <div class="detail-row">
                            <label>Status:</label>
                            <span class="status completed">Completed</span>
                        </div>
                        <div class="detail-row">
                            <label>Bank:</label>
                            <span>Commercial Bank</span>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        // Download receipt
        function downloadReceipt(paymentId) {
            // In a real application, this would generate and download a receipt
            alert('Receipt download functionality would be implemented here.');
        }

        // Preset amounts for common services
        const presetAmounts = {
            'Consultation': 500,
            'Surgery': 5000,
            'Medication': 200,
            'Other': 0
        };

        document.getElementById('payment_type').addEventListener('change', function() {
            const amountField = document.getElementById('amount');
            const selectedType = this.value;
            
            if (presetAmounts[selectedType] !== undefined) {
                amountField.value = presetAmounts[selectedType];
            }
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>