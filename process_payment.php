<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];
    
    // Verify appointment belongs to current patient
    $sql = "SELECT a.* FROM appointments a 
            WHERE a.id = ? AND a.patient_id = ? AND a.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appointment_id, $_SESSION['patient_id']]);
    $appointment = $stmt->fetch();
    
    if ($appointment) {
        // Simulate payment processing (in real world, integrate with payment gateway)
        $sql = "UPDATE payments SET status = 'completed' WHERE appointment_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$appointment_id])) {
            $_SESSION['success'] = "Payment processed successfully";
        } else {
            $_SESSION['error'] = "Payment processing failed";
        }
    } else {
        $_SESSION['error'] = "Invalid appointment";
    }
}

header('Location: dashboard.php');