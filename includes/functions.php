<?php
// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if the user is an admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to redirect users based on their role
function redirectBasedOnRole() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header("Location: pages/admin/dashboard.php");
        } else {
            header("Location: pages/user/home.php");
        }
        exit();
    }
}

// Function to display alerts
function displayAlert() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
}
?>