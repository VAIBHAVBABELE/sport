<?php
session_start();
include 'includes/db_admin.php';
include 'includes/functions.php';

// Only allow access if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Insert admin into the database
    $sql = "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn_admin->prepare($sql);
    $stmt->execute([$name, $email, $password]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Admin registration successful!";
        header('Location: pages/admin/dashboard.php');
    } else {
        $_SESSION['error'] = "Admin registration failed. Please try again.";
        header('Location: register_admin.php');
    }
    exit();
} else {
    // Redirect if the form is not submitted
    header('Location: register_admin.php');
    exit();
}
?>