<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';

// Redirect if not logged in or if admin
if (!isLoggedIn()) {
    header("Location: ../../login_user.php");
    exit();
}

if (isAdmin()) {
    header("Location: ../../pages/admin/dashboard.php");
    exit();
}

// Fetch logged-in user's name
$user_id = $_SESSION['user_id'];
$stmt = $conn_user->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['name'];

// Fetch real data for counts
$stmt = $conn_user->prepare("SELECT COUNT(*) AS total_users FROM users");
$stmt->execute();
$registered_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$stmt = $conn_user->prepare("SELECT COUNT(*) AS total_games FROM game_registrations");
$stmt->execute();
$game_registrations = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];

$stmt = $conn_user->prepare("SELECT COUNT(*) AS total_teams FROM teams");
$stmt->execute();
$teams_registered = $stmt->fetch(PDO::FETCH_ASSOC)['total_teams'];

// Fetch upcoming events from the database
$stmt = $conn_user->prepare("SELECT * FROM events WHERE event_date > NOW() ORDER BY event_date ASC");
$stmt->execute();
$upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch announcements from the database
$stmt = $conn_user->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

displayAlert();
ob_end_flush(); // End output buffering and send output to the browser
?>

<!-- Custom CSS for Enhanced Design -->
<style>
       

    .user-home-section {
        background: linear-gradient(120deg, #edfbf9, #eecc92);
        padding: 2rem 0;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .count-card {
        background: linear-gradient(45deg, #6c5ce7, #a29bfe);
        color: white;
    }
    .event-card {
        color: white;
    }
    .announcement-card {
        background: linear-gradient(45deg, #0984e3, #74b9ff);
        color: white;
    }
    .quick-links .btn {
        width: 200px;
        margin: 10px;
        border-radius: 25px;
        font-weight: bold;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .quick-links .btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .welcome-message {
        background: linear-gradient(45deg, #ff7675, #d63031);
        padding: 2rem;
        border-radius: 15px;
        color: white;
        margin-bottom: 2rem;
    }
    .welcome-message h2 {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .welcome-message p {
        font-size: 1.2rem;
    }
    .counts-section {
        margin-bottom: 2rem;
    }
    .counts-section .card {
        height: 100%;
    }
    .upcoming-events-section, .announcements-section {
        margin-bottom: 2rem;
    }
    .upcoming-events-section .card, .announcements-section .card {
        height: 100%;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .modal-content {
        border-radius: 15px;
        border: none;
    }
    .modal-header {
        background: linear-gradient(45deg, #00b894, #55efc4);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    @media (max-width: 768px) {
        .quick-links .btn {
            width: 100%;
            margin: 5px 0;
        }
        .welcome-message h2 {
            font-size: 2rem;
        }
        .welcome-message p {
            font-size: 1rem;
        }
    }
</style>

<section class="user-home-section">
    <div class="container">

        
        <!-- Welcome Message -->
        <div class="welcome-message text-center">
            <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p>This is your home page. Explore the website features and stay updated with the latest events.</p>
        </div>

        <!-- Counts Section -->
        <div class="row text-center counts-section">
            <div class="col-md-4 mb-4">
                <div class="card count-card">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $registered_users; ?></h3>
                        <p class="card-text">Registered Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card count-card">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $game_registrations; ?></h3>
                        <p class="card-text">Game Registrations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card count-card">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $teams_registered; ?></h3>
                        <p class="card-text">Teams Registered</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Events Section -->
        <div class="upcoming-events-section">
            <h3 class="text-center mb-4">Upcoming Events</h3>
            <div class="row">
                <?php
                $event_colors = ['#00b894', '#0984e3', '#6c5ce7', '#e84393', '#fdcb6e'];
                foreach ($upcoming_events as $index => $event):
                    $color = $event_colors[$index % count($event_colors)];
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card" style="background: linear-gradient(45deg, <?php echo $color; ?>, <?php echo adjustBrightness($color, -20); ?>);">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                <h6 class="card-subtitle mb-2"><?php echo htmlspecialchars($event['event_date']); ?></h6>
                                <p class="card-text"><?php echo htmlspecialchars(substr($event['event_description'], 0, 100) . '...'); ?></p>
                                <button type="button" class="btn btn-light" data-toggle="modal" data-target="#eventModal<?php echo $event['id']; ?>">
                                    Learn More
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Event Modal -->
                    <div class="modal fade" id="eventModal<?php echo $event['id']; ?>" tabindex="-1" aria-labelledby="eventModalLabel<?php echo $event['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="eventModalLabel<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($event['event_description']); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Links Section -->
        <div class="text-center mb-5">
            <h3 class="mb-4">Quick Links</h3>
            <div class="d-flex justify-content-center flex-wrap quick-links">
                <a href="game_registration.php" class="btn btn-primary">Game Registration</a>
                <a href="team_registration.php" class="btn btn-success">Team Registration</a>
                <a href="profile.php" class="btn btn-info">View Profile</a>
                <a href="leaderboard.php" class="btn btn-warning">Leaderboard</a>
                <a href="gallery.php" class="btn btn-danger">Gallery</a>
            </div>
        </div>

        <!-- Announcements Section -->
        <div class="announcements-section">
            <h3 class="text-center mb-4">Announcements</h3>
            <div class="card announcement-card">
                <div class="card-body">
                    <h5 class="card-title">Important Notices</h5>
                    <div class="list-group">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($announcement['notice']); ?></h6>
                                    <small><?php echo htmlspecialchars($announcement['created_at']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Function to adjust color brightness
function adjustBrightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
?>
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php include '../../includes/footer.php'; ?>