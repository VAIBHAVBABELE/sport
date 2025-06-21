<?php
session_start();
include 'includes/db_admin.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: login_admin.php");
        exit();
    }

    // Fetch admin from the database
    $stmt = $conn_admin->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($admin && password_verify($password, $admin['password'])) {
        // Login successful
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['success'] = "Login successful!";

        // Debugging: Check if session variables are set
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";

        // Redirect to admin dashboard
        header('Location: pages/admin/dashboard.php');
        exit();
    } else {
        // Login failed
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login_admin.php");
        exit();
    }
} else {
    // Redirect if the form is not submitted
    header("Location: login_admin.php");
    exit();
}
?>