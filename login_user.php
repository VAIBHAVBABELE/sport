<?php
session_start();
include 'includes/header.php';
include 'includes/functions.php';

displayAlert();
?>

<!-- Custom CSS for Enhanced Design -->
<style>
    /* Background Gradient for Login Page */
    body {
        background: linear-gradient(120deg, #edfbf9, #eecc92);
        min-height: 100vh;
    }

    .login-section {
        background-color: rgba(255, 255, 255, 0.7);
        padding: 3rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 2rem auto;
        max-width: 600px;
    }

    .login-section h2 {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #6a11cb;
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-section .form-control {
        border-radius: 25px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .login-section .form-control:focus {
        border-color: #6a11cb;
        box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
    }

    .login-section .form-label {
        font-weight: 600;
        color: #333;
    }

    .login-section .btn-primary {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-section .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .login-section .alert {
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .login-section .text-center a {
        color: #6a11cb;
        text-decoration: none;
        font-weight: 500;
    }

    .login-section .text-center a:hover {
        text-decoration: underline;
    }
</style>

<!-- Login Form -->
<section class="login-section">
    <div class="container">
        <h2>User Login</h2>
        <form action="process_login_user.php" method="POST">
            <!-- Email Input -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <!-- Password Input -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <!-- Register Link -->
        <p class="text-center mt-3">
            Don't have an account? <a href="register_user.php">Register here</a>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>