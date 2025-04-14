<?php
require_once 'config.php';

// Add new appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time]);
    header('Location: appointments.php');
    exit();
}

// Update appointment status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $sql = "UPDATE appointments SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $id]);
    header('Location: appointments.php');
    exit();
}

// Fetch all doctors for dropdown
$doctors = $pdo->query("SELECT id, name, specialization FROM doctors ORDER BY name")->fetchAll();

// Fetch all patients for dropdown
$patients = $pdo->query("SELECT id, name FROM patients ORDER BY name")->fetchAll();

// Fetch all appointments with related information
$sql = "SELECT a.*, d.name as doctor_name, p.name as patient_name 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        JOIN patients p ON a.patient_id = p.id 
        ORDER BY a.appointment_date, a.appointment_time";
$appointments = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Hospital Management System</title>
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
                        <a class="nav-link active" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">Payments</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Appointments</h2>
        
        <!-- Add Appointment Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Schedule New Appointment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['id']; ?>">
                                        <?php echo htmlspecialchars($patient['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doctor_id" class="form-label">Doctor</label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo htmlspecialchars($doctor['name']) . ' (' . htmlspecialchars($doctor['specialization']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="appointment_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="appointment_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>
                    </div>
                    <button type="submit" name="add_appointment" class="btn btn-primary">Schedule Appointment</button>
                </form>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Appointments List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $appointment['status'] === 'confirmed' ? 'success' : 
                                            ($appointment['status'] === 'cancelled' ? 'danger' : 'warning');
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'pending'): ?>
                                        <a href="?id=<?php echo $appointment['id']; ?>&status=confirmed" class="btn btn-success btn-sm">Confirm</a>
                                        <a href="?id=<?php echo $appointment['id']; ?>&status=cancelled" class="btn btn-danger btn-sm">Cancel</a>
                                    <?php endif; ?>
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