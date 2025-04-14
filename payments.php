<?php
require_once 'config.php';

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['amount'];

    // Simulate payment processing
    $payment_status = rand(0, 10) > 2 ? 'completed' : 'failed'; // 80% success rate

    $sql = "INSERT INTO payments (appointment_id, amount, status) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appointment_id, $amount, $payment_status]);

    header('Location: payments.php');
    exit();
}

// Fetch unpaid appointments
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, 
               d.name as doctor_name, d.specialization,
               p.name as patient_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN patients p ON a.patient_id = p.id
        LEFT JOIN payments py ON a.id = py.appointment_id
        WHERE (py.id IS NULL OR py.status = 'pending')
        AND a.status = 'confirmed'
        ORDER BY a.appointment_date";
$unpaid_appointments = $pdo->query($sql)->fetchAll();

// Fetch payment history
$sql = "SELECT py.*, a.appointment_date, d.name as doctor_name, p.name as patient_name
        FROM payments py
        JOIN appointments a ON py.appointment_id = a.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN patients p ON a.patient_id = p.id
        ORDER BY py.payment_date DESC";
$payments = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Hospital Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="payments.php">Payments</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Payments</h2>
        
        <!-- Process Payment Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Process Payment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appointment_id" class="form-label">Select Appointment</label>
                            <select class="form-select" id="appointment_id" name="appointment_id" required>
                                <option value="">Select Appointment</option>
                                <?php foreach ($unpaid_appointments as $appointment): ?>
                                    <option value="<?php echo $appointment['id']; ?>">
                                        <?php echo htmlspecialchars($appointment['patient_name']) . 
                                                 ' - Dr. ' . htmlspecialchars($appointment['doctor_name']) . 
                                                 ' (' . htmlspecialchars($appointment['appointment_date']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount (Rs)</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                    </div>
                    <button type="submit" name="process_payment" class="btn btn-primary">Process Payment</button>
                </form>
            </div>
        </div>

        <!-- Payment History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($payment['payment_date']))); ?></td>
                                <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['doctor_name']); ?></td>
                                <td>Rs <?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>