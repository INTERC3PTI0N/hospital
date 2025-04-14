<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Insert new appointment with pending status
    $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time])) {
        $appointment_id = $pdo->lastInsertId();
        header('Location: dashboard.php');
    } else {
        $_SESSION['error'] = "Failed to create appointment";
        header('Location: dashboard.php');
    }
    exit();
}

header('Location: dashboard.php');