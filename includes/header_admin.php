<?php
$current_file = basename($_SERVER['PHP_SELF']);

// Custom titles for specific pages
$custom_titles = [
    'dashboard.php' => 'Dashboard',
    'manage_fixtures.php' => 'Manage Fixtures',
    'manage_games.php' => 'Manage Games',
    'admin_panel.php' => 'Manage Events',
    'manage_leaderboard.php' => 'Manage Leaderboard',
    'upload_content.php' => 'Upload Content',
    'verify_content.php' => 'Verify Content',
    
];

// Set the title based on the file name
$page_name = $custom_titles[$current_file] ?? ucfirst(str_replace('.php', '', $current_file));
$title = $page_name . " | Nitra Sport Fest";
?>
<!-- includes/header_admin.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title;?></title>
    <link rel="icon" type="image/png" href="https://img.icons8.com/color/48/000000/trophy.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Nitra Mitra Fest</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_fixtures.php">fixture</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php">events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_games.php">games/teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_leaderboard.php">leaderboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload_content.php">upload content</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_game_registration.php">Game Registration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="verify_content.php">verify content</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>