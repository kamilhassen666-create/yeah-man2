<?php
require_once '../includes/config.php';
requireUserType(['patient']);

$pdo = getDBConnection();

// Get patient information
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Get patient's payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE patient_id = ? ORDER BY payment_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = sanitizeInput($_POST['amount']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $description = sanitizeInput($_POST['description']);
    
    if (empty($amount) || empty($payment_method)) {
        setFlashMessage('danger', 'Please fill in all required fields.');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (patient_id, amount, payment_method, payment_date, description, status, reference_number) VALUES (?, ?, ?, NOW(), ?, 'Pending', ?)");
            $reference_number = 'PAY_' . date('Ymd') . '_' . uniqid();
            $stmt->execute([$_SESSION['user_id'], $amount, $payment_method, $description, $reference_number]);
            
            setFlashMessage('success', 'Payment submitted successfully. Reference: ' . $reference_number);
            header('Location: payments.php');
            exit();
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error submitting payment: ' . $e->getMessage());
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
                <img src="../assets/images/patient-avatar.png" alt="Patient" class="profile-avatar mb-3">
                <h5 class="text-white"><?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-white-50">Patient ID: <?php echo $patient['patient_id']; ?></p>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="medical_records.php">
                    <i class="fas fa-file-medical"></i>Medical Records
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
                <a class="nav-link active" href="payments.php">
                    <i class="fas fa-credit-card"></i>Payments
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
                    <h2><i class="fas fa-credit-card me-2"></i>Payments</h2>
                    <p class="text-muted">Manage your payments and view payment history</p>
                </div>
            </div>
            
            <!-- Make Payment Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-2"></i>Make New Payment
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" data-validate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount (ETB)</label>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">Payment Method</label>
                                            <select class="form-select" id="payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="Commercial Bank">Commercial Bank</option>
                                                <option value="Awash Bank">Awash Bank</option>
                                                <option value="Abyssinia Bank">Abyssinia Bank</option>
                                                <option value="Telebirr">Telebirr</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter payment description (e.g., consultation fee, medication, etc.)"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Payment Methods</label>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <div class="payment-method" data-method="Commercial Bank">
                                                <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                                <h6>Commercial Bank</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="payment-method" data-method="Awash Bank">
                                                <i class="fas fa-university fa-2x text-success mb-2"></i>
                                                <h6>Awash Bank</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="payment-method" data-method="Abyssinia Bank">
                                                <i class="fas fa-university fa-2x text-warning mb-2"></i>
                                                <h6>Abyssinia Bank</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="payment-method" data-method="Telebirr">
                                                <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                                                <h6>Telebirr</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>Submit Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment History -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Payment History
                            </h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm print-btn">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                                <button class="btn btn-outline-success btn-sm export-btn">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                                <p class="text-muted text-center">No payment history found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo formatDateTime($payment['payment_date']); ?></td>
                                                    <td><span class="badge bg-secondary"><?php echo $payment['reference_number']; ?></span></td>
                                                    <td><strong>ETB <?php echo number_format($payment['amount'], 2); ?></strong></td>
                                                    <td><?php echo $payment['payment_method']; ?></td>
                                                    <td><?php echo $payment['description'] ?: 'N/A'; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $payment['status'] == 'Completed' ? 'success' : ($payment['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                            <?php echo $payment['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_payment.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($payment['status'] == 'Pending'): ?>
                                                            <button class="btn btn-sm btn-outline-warning" onclick="cancelPayment(<?php echo $payment['id']; ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Payment Summary -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Payments</h6>
                                                <h4>ETB <?php echo number_format(array_sum(array_column($payments, 'amount')), 2); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6>Completed</h6>
                                                <h4><?php echo count(array_filter($payments, function($p) { return $p['status'] == 'Completed'; })); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6>Pending</h6>
                                                <h4><?php echo count(array_filter($payments, function($p) { return $p['status'] == 'Pending'; })); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelPayment(paymentId) {
    if (confirm('Are you sure you want to cancel this payment?')) {
        // Add AJAX call to cancel payment
        alert('Payment cancellation feature will be implemented.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>