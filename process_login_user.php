<?php
session_start();
include 'includes/db_user.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: login_user.php");
        exit();
    }

    // Fetch user from the database
    $stmt = $conn_user->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['success'] = "Login successful!";
        header('Location: pages/user/home.php');
    } else {
        // Login failed
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login_user.php");
    }
    exit();
} else {
    // Redirect if the form is not submitted
    header("Location: login_user.php");
    exit();
}
?>