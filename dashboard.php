<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch role-specific data
switch ($role) {
    case 'doctor':
        $doctor_id = $_SESSION['doctor_id'];
        $sql = "SELECT a.*, p.name as patient_name 
               FROM appointments a 
               JOIN patients p ON a.patient_id = p.id 
               WHERE a.doctor_id = ? AND a.status != 'cancelled' 
               ORDER BY a.appointment_date, a.appointment_time";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$doctor_id]);
        $appointments = $stmt->fetchAll();
        break;
        
    case 'patient':
        $patient_id = $_SESSION['patient_id'];
        // Fetch patient's appointments and payments
        $sql = "SELECT a.*, d.name as doctor_name, d.specialization, 
               COALESCE(p.status, 'unpaid') as payment_status 
               FROM appointments a 
               JOIN doctors d ON a.doctor_id = d.id 
               LEFT JOIN payments p ON a.id = p.appointment_id 
               WHERE a.patient_id = ? 
               ORDER BY a.appointment_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$patient_id]);
        $appointments = $stmt->fetchAll();
        
        // Fetch available doctors for new appointments
        $doctors = $pdo->query("SELECT id, name, specialization FROM doctors ORDER BY name")->fetchAll();
        break;
        
    case 'admin':
        // Fetch pending appointments with payment status
        $sql = "SELECT a.*, d.name as doctor_name, p.name as patient_name, 
               pay.status as payment_status, pay.amount 
               FROM appointments a 
               JOIN doctors d ON a.doctor_id = d.id 
               JOIN patients p ON a.patient_id = p.id 
               LEFT JOIN payments pay ON a.id = pay.appointment_id 
               WHERE a.status = 'pending' 
               ORDER BY a.appointment_date";
        $appointments = $pdo->query($sql)->fetchAll();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Hospital Management System</a>
            <div class="navbar-nav ms-auto">
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Welcome, <?php echo ucfirst($role); ?></h2>
        
        <?php if ($role === 'doctor'): ?>
            <h3 class="mt-4">Your Schedule</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo $appointment['appointment_date']; ?></td>
                                <td><?php echo $appointment['appointment_time']; ?></td>
                                <td><?php echo $appointment['patient_name']; ?></td>
                                <td><?php echo ucfirst($appointment['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php elseif ($role === 'patient'): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h3>Request Appointment</h3>
                    <form action="process_appointment.php" method="POST">
                        <div class="mb-3">
                            <label for="doctor" class="form-label">Select Doctor</label>
                            <select class="form-select" name="doctor_id" required>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo $doctor['name']; ?> (<?php echo $doctor['specialization']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" name="appointment_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" name="appointment_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Request Appointment</button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h3>Your Appointments</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['doctor_name']; ?></td>
                                        <td><?php echo ucfirst($appointment['status']); ?></td>
                                        <td><?php echo ucfirst($appointment['payment_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif ($role === 'admin'): ?>
            <h3 class="mt-4">Pending Appointments</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                            <th>Patient</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo $appointment['appointment_date']; ?></td>
                                <td><?php echo $appointment['appointment_time']; ?></td>
                                <td><?php echo $appointment['doctor_name']; ?></td>
                                <td><?php echo $appointment['patient_name']; ?></td>
                                <td><?php echo ucfirst($appointment['payment_status']); ?></td>
                                <td>
                                    <?php if ($appointment['payment_status'] === 'completed'): ?>
                                        <a href="update_appointment.php?id=<?php echo $appointment['id']; ?>&action=approve" 
                                           class="btn btn-sm btn-success">Approve</a>
                                        <a href="update_appointment.php?id=<?php echo $appointment['id']; ?>&action=reject" 
                                           class="btn btn-sm btn-danger">Reject</a>
                                    <?php else: ?>
                                        Waiting for payment
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>