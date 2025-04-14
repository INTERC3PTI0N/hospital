<?php
require_once 'config.php';

// Add new doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $pdo->beginTransaction();

        // Create user account
        $username = strtolower(str_replace(' ', '', $name));
        $password = password_hash($username . '123', PASSWORD_DEFAULT); // Default password: username123

        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, 'doctor']);
        $user_id = $pdo->lastInsertId();

        // Add doctor record
        $sql = "INSERT INTO doctors (user_id, name, specialization, email, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $name, $specialization, $email, $phone]);

        $pdo->commit();
        header('Location: doctors.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
}

// Delete doctor
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM doctors WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    header('Location: doctors.php');
    exit();
}

// Fetch all doctors
$stmt = $pdo->query("SELECT * FROM doctors ORDER BY name");
$doctors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Hospital Management System</title>
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
                        <a class="nav-link active" href="doctors.php">Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">Patients</a>
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
        <h2>Manage Doctors</h2>
        
        <!-- Add Doctor Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Doctor</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                </form>
            </div>
        </div>

        <!-- Doctors List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Doctors List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Specialization</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $doctor['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>
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