<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = "Username already exists";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create user account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'patient')"; 
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hashedPassword]);
            $userId = $pdo->lastInsertId();
            
            // Create patient profile
            $sql = "INSERT INTO patients (name, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?)"; 
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $address, $userId]);
            
            $pdo->commit();
            $success = "Registration successful! Please login.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(to right,rgb(239, 79, 254),rgb(30, 168, 175));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            backdrop-filter: blur(20px);
            background-color: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            color: white;
        }

        .card-header {
            border-bottom: none;
        }

        .form-control {
            border-radius: 0.75rem;
            border: none;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 242, 254, 0.25);
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        a {
            color: #f0f8ff;
            text-decoration: underline;
        }

        .alert {
            border-radius: 0.75rem;
            font-size: 0.95rem;
        }

        ::placeholder {
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card p-4">
                    <div class="card-header text-center">
                        <h2 class="fw-bold">Patient Registration</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Enter your full name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="example@domain.com">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required placeholder="10-digit number">
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Residential address"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Choose a username">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Create a password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p>Already have an account? <a href="index.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
