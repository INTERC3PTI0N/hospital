<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Verify appointment exists and has completed payment
    $sql = "SELECT a.*, p.status as payment_status 
            FROM appointments a 
            JOIN payments p ON a.id = p.appointment_id 
            WHERE a.id = ? AND a.status = 'pending' AND p.status = 'completed'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();
    
    // If appointment is approved, create a pending payment entry
    if ($action === 'approve') {
        $sql = "INSERT INTO payments (appointment_id, status, amount) VALUES (?, 'pending', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$appointment_id]);
    }
    
    if ($appointment) {
        $pdo->beginTransaction();
        
        try {
            if ($action === 'approve') {
                // Update appointment status to confirmed
                $sql = "UPDATE appointments SET status = 'confirmed' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$appointment_id]);
                
                $_SESSION['success'] = "Appointment approved successfully";
                
            } elseif ($action === 'reject') {
                // Update appointment status to cancelled
                $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$appointment_id]);
                
                // Simulate refund process
                // In real-world scenario, integrate with payment gateway for actual refund
                $sql = "UPDATE payments SET status = 'refunded' WHERE appointment_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$appointment_id]);
                
                $_SESSION['success'] = "Appointment rejected and payment refunded";
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to update appointment status";
        }
    } else {
        $_SESSION['error'] = "Invalid appointment or payment not completed";
    }
}

header('Location: dashboard.php');