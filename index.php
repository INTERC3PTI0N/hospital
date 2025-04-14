<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Get role-specific information
        if ($user['role'] === 'doctor') {
            $sql = "SELECT * FROM doctors WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['id']]);
            $doctor = $stmt->fetch();
            $_SESSION['doctor_id'] = $doctor['id'];
        } elseif ($user['role'] === 'patient') {
            $sql = "SELECT * FROM patients WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['id']]);
            $patient = $stmt->fetch();
            $_SESSION['patient_id'] = $patient['id'];
        }
        
        if ($user['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body {
            background: linear-gradient(to right,rgb(233, 100, 67) 0%,rgb(49, 6, 42) 100%);
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
            background-color: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .form-control::placeholder {
            color: #ddd;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(56, 249, 215, 0.3);
            color: #000;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-weight: 600;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            border-radius: 0.75rem;
            font-size: 0.95rem;
        }

        a {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card p-4">
                    <div class="card-header text-center">
                        <h2 class="fw-bold">Hospital Management Portal</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Enter your username">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
