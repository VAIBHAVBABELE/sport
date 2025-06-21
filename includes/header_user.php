<?php
$current_file = basename($_SERVER['PHP_SELF']);

// Custom titles for specific pages
$custom_titles = [
    'fixtures.php' => 'Game Fixtures and Score',
    'gallery.php' => 'Gallery',
    'game_registration.php' => 'Game Registration Form',
    'leaderboard.php' => 'Leaderboard',
    'team_registration.php' => 'Team Registration Form',
    'updates.php' => 'Updates',
    'upload_content.php' => 'Upload Content',
    'profile.php' => 'Profile',
];

// Set the title based on the file name
$page_name = $custom_titles[$current_file] ?? ucfirst(str_replace('.php', '', $current_file));
$title = $page_name . " | Nitra Sport Fest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title;?></title>
    <link rel="icon" type="image/png" href="https://img.icons8.com/color/48/000000/trophy.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Custom Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        /* Navbar Styling - Desktop */
        .navbar {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 0.5rem 0; /* Reduced padding */
        }

        .navbar-brand {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
            margin-left: 15px;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffdd57 !important;
            transform: translateY(-2px);
        }

        /* Mobile Sidebar Styles */
        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1050;
            top: 0;
            left: 0;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar.open {
            width: 250px;
        }

        .sidebar .close-btn {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 1.5rem;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
        }

        .sidebar-nav {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav .nav-item {
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-nav .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar-nav .nav-link:hover {
            color: #ffdd57;
            padding-left: 10px;
        }

        .sidebar-nav .nav-link i {
            margin-right: 10px;
            width: 24px;
            text-align: center;
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            margin-left: 15px;
        }

        /* Mini Navigation Icons */
        .mini-nav {
            display: none;
            position: fixed;
            left: 0;
            top: 55%;
            transform: translateY(-50%);
            z-index: 1000;
            background: rgba(106, 17, 203, 0.8);
            border-radius: 0 10px 10px 0;
            padding: 10px 5px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }

        .mini-nav .nav-link {
            color: white;
            font-size: 1.2rem;
            padding: 10px;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }

        .mini-nav .nav-link:hover {
            color: #ffdd57;
            transform: scale(1.1);
        }

        .mini-nav .nav-link i {
            display: block;
            margin-bottom: 5px;
        }

        .mini-nav .nav-link span {
            display: none;
            font-size: 0.8rem;
        }

        /* Back to Top Button */
        #back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            background: #6a11cb;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }

        #back-to-top:hover {
            background: #2575fc;
            transform: translateY(-5px);
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                display: none !important;
            }

            .mobile-menu-btn {
                display: block;
            }

            .mini-nav {
                display: block;
            }

            .navbar-nav .nav-link {
                margin: 0;
                padding: 10px 15px;
            }
        }

        @media (min-width: 992px) {
            .sidebar, .mini-nav {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="home.php">
                Nitra Mitra Fest
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="game_registration.php">Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leaderboard.php">LeaderBoard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fixtures.php">Fixture</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="team_registration.php">Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="updates.php">Updates</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload_content.php">Uploads</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mini Navigation Icons -->
    <div class="mini-nav" id="miniNav">
        <a class="nav-link" href="home.php" title="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a class="nav-link" href="game_registration.php" title="Games">
            <i class="fas fa-gamepad"></i>
            <span>Games</span>
        </a>
        <a class="nav-link" href="leaderboard.php" title="LeaderBoard">
            <i class="fas fa-trophy"></i>
            <span>LeaderBoard</span>
        </a>
        <a class="nav-link" href="fixtures.php" title="Fixture">
            <i class="fas fa-calendar-alt"></i>
            <span>Fixture</span>
        </a>
        <a class="nav-link" href="team_registration.php" title="Teams">
            <i class="fas fa-users"></i>
            <span>Teams</span>
        </a>
        <a class="nav-link" href="gallery.php" title="Gallery">
            <i class="fas fa-images"></i>
            <span>Gallery</span>
        </a>
        <a class="nav-link" href="updates.php" title="Updates">
            <i class="fas fa-bell"></i>
            <span>Updates</span>
        </a>
        <a class="nav-link" href="upload_content.php" title="Uploads">
            <i class="fas fa-upload"></i>
            <span>Uploads</span>
        </a>
        <a class="nav-link" href="profile.php" title="Profile">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        <a class="nav-link" href="../../logout.php" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <!-- Mobile Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="close-btn" id="closeSidebar">
            <i class="fas fa-times"></i>
        </button>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link" href="home.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="game_registration.php">
                    <i class="fas fa-gamepad"></i> Games
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="leaderboard.php">
                    <i class="fas fa-trophy"></i> LeaderBoard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="fixtures.php">
                    <i class="fas fa-calendar-alt"></i> Fixture
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="team_registration.php">
                    <i class="fas fa-users"></i> Teams
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="gallery.php">
                    <i class="fas fa-images"></i> Gallery
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="updates.php">
                    <i class="fas fa-bell"></i> Updates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="upload_content.php">
                    <i class="fas fa-upload"></i> Uploads
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Sidebar functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const miniNav = document.getElementById('miniNav');

        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            miniNav.style.display = 'none';
        });

        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('open');
            miniNav.style.display = 'block';
        });

        // Close sidebar when clicking on a link
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('open');
                miniNav.style.display = 'block';
            });
        });

        // Back to Top Button
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>