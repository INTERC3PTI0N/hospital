<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    
    try {
        $pdo->beginTransaction();
        
        // Generate username from name (firstname.lastname)
        $nameParts = explode(' ', strtolower($name));
        $username = $nameParts[0];
        if (isset($nameParts[1])) {
            $username .= '.' . $nameParts[1];
        }
        
        // Check if username exists and append number if needed
        $baseUsername = $username;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) break;
            $username = $baseUsername . $counter++;
        }
        
        // Generate temporary password
        $tempPassword = substr(md5(uniqid()), 0, 8);
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Create user account
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'doctor')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $hashedPassword]);
        $userId = $pdo->lastInsertId();
        
        // Create doctor profile
        $sql = "INSERT INTO doctors (name, email, phone, specialization, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $specialization, $userId]);
        
        $pdo->commit();
        $success = "Doctor added successfully! Login credentials:<br>";
        $success .= "Username: " . $username . "<br>";
        $success .= "Temporary Password: " . $tempPassword;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to add doctor: " . $e->getMessage();
    }
}

// Fetch all doctors
$doctors = $pdo->query("SELECT d.*, u.username FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY d.name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Doctors</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Doctors</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Phone:</label>
                <input type="tel" name="phone" required>
            </div>
            
            <div class="form-group">
                <label>Specialization:</label>
                <input type="text" name="specialization" required>
            </div>
            
            <button type="submit">Add Doctor</button>
        </form>
        
        <h3>Existing Doctors</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>