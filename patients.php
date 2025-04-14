<?php
require_once 'config.php';

// Add new patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    try {
        $pdo->beginTransaction();

        // Create user account
        $username = strtolower(str_replace(' ', '', $name));
        $password = password_hash($username . '123', PASSWORD_DEFAULT); // Default password: username123

        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, 'patient']);
        $user_id = $pdo->lastInsertId();

        // Add patient record
        $sql = "INSERT INTO patients (user_id, name, email, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $name, $email, $phone, $address]);

        $pdo->commit();
        header('Location: patients.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
}

// Delete patient
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $sql = "DELETE FROM patients WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            header('Location: patients.php');
        } else {
            header('Location: patients.php?error=deletion_failed');
        }
    } catch (Exception $e) {
        header('Location: patients.php?error=deletion_failed');
    }
    exit();
}

// Fetch all patients
$stmt = $pdo->query("SELECT * FROM patients ORDER BY name");
$patients = $stmt->fetchAll();
?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'deletion_failed'): ?>
<script>
    alert('Patient cannot be deleted. There are appointments or payments associated with this patient.');
</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Hospital Management System</title>
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
                        <a class="nav-link active" href="patients.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">Payments</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Patients</h2>
        
        <!-- Add Patient Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Patient</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" required></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_patient" class="btn btn-primary">Add Patient</button>
                </form>
            </div>
        </div>

        <!-- Patients List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Patients List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                <td><?php echo htmlspecialchars($patient['address']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $patient['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient?')">Delete</a>
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