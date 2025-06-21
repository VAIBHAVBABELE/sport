<?php
session_start();
include 'includes/functions.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    redirectBasedOnRole();
} elseif (isset($_SESSION['admin_id'])) {
    // Redirect admin to dashboard
    header('Location: pages/admin/dashboard.php');
    exit();
}

displayAlert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Website</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Animate.css for Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        /* Background and General Styling */
        body {
            background: linear-gradient(45deg, #ff6f61, #ffcc00);
            font-family: 'Arial', sans-serif;
            color: #ffffff;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(45deg, #ff6f61, #ffcc00);
            color: white;
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease;
        }

        .hero-section .btn {
            margin: 0.5rem;
            font-size: 1.1rem;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hero-section .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* About Section */
        .about-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 4rem 0;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 800px;
        }

        .about-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 1.5rem;
            animation: fadeInDown 1s ease;
        }

        .about-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: center;
            color: black;
            animation: fadeInUp 1s ease;
        }

        /* Features Section */
        .features-section {
            background: linear-gradient(45deg, #333333, #1a1a1a);
            padding: 4rem 0;
        }

        .features-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #ffcc00;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .feature-card i {
            font-size: 3rem;
            color: #ffcc00;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #ffcc00;
            margin-bottom: 1rem;
        }

        .feature-card p {
            font-size: 1rem;
            line-height: 1.6;
            color: #ffffff;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown">Welcome to Nitra Sport Fest Official Website</h1>
            <p class="animate__animated animate__fadeInUp">This website for all individual,viewer,players,students of college.It is not the game registration .Register to access tournaments, leaderboards, and more.
                 Log in to track progress, connect with gamers, and dive into the action. Level up your gaming 
                 journey today! 🎮✨</p>
            <a href="register_user.php" class="btn btn-light btn-lg">User Registration</a>
            <a href="login_user.php" class="btn btn-outline-light btn-lg">User Login</a>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeInDown">About Us</h2>
            <p class="animate__animated animate__fadeInUp">
            At Nitra Technical Campus, Ghaziabad, we are thrilled to present our annual Sports Fest, a 
            celebration of talent, teamwork, and the spirit of competition. This event is designed to 
            bring together students from all disciplines to showcase their skills, engage in thrilling 
            gaming tournaments, and foster a sense of camaraderie. Whether you're a casual gamer or a 
            competitive athlete, our platform offers something for everyone. 
             traditional sports challenges, the fest is a perfect blend of technology, strategy,
             and physical prowess. Join us to experience unforgettable moments, connect with fellow enthusiasts,
              and be part of a vibrant community that celebrates sportsmanship and innovation. Let’s make this 
              year’s Sports Fest bigger, better, and more exciting than ever!
                
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeInDown">Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card animate__animated animate__fadeInUp">
                        <i class="fas fa-trophy"></i>
                        <h3>Tournaments</h3>
                        <p>Participate in exciting gaming tournaments and win amazing prizes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card animate__animated animate__fadeInUp">
                        <i class="fas fa-users"></i>
                        <h3>Community</h3>
                        <p>Connect with fellow gamers and join a vibrant gaming community.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card animate__animated animate__fadeInUp">
                        <i class="fas fa-newspaper"></i>
                        <h3>News & Updates</h3>
                        <p>Stay updated with the latest gaming news and updates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>