<?php
ob_start(); // Start output buffering
session_start(); // Call session_start() first
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';


if (!isLoggedIn()) {
    echo "Debug: User not logged in, redirecting to login_user.php<br>"; // Debugging
    header("Location: ../../login_user.php");
    exit();
}





displayAlert();

// Fetch the logged-in user's roll number
$user_id = $_SESSION['user_id'];
$stmt = $conn_user->prepare("SELECT roll_no FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_roll_no = $user['roll_no'];

// Fetch all games
$games = $conn_user->query("SELECT * FROM games")->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is allowed to register a team in any game
$allowed_games = [];

foreach ($games as $game) {
    $allowed_users = explode(',', $game['allowed_users']);
    if (in_array($user_roll_no, $allowed_users)) {
        // Check if the user has already registered a team for this game
        $stmt = $conn_user->prepare("SELECT * FROM teams WHERE created_by = ? AND game_id = ?");
        $stmt->execute([$_SESSION['user_id'], $game['id']]);
        $existing_team = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing_team) {
            // User is allowed to register for this game and hasn't registered yet
            $allowed_games[] = $game;
        }
    }
}

// If the user is allowed to register for at least one game, set $is_allowed to true
$is_allowed = !empty($allowed_games);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_allowed) {
    $game_id = $_POST['game_id'];
    $team_name = $_POST['team_name'];
    $sport = $_POST['sport'];
    $team_captain = $_POST['team_captain'];
    $vice_captain = $_POST['vice_captain'];
    $players = $_POST['players'];
    $num_players = $_POST['num_players'];
    $team_logo = $_FILES['team_logo'];

    // Validate input fields
    if (empty($team_name) || empty($sport) || empty($team_captain) || empty($vice_captain) || empty($players) || empty($num_players)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: team_registration.php");
        exit();
    }

    // Fetch the selected game's details
    $stmt = $conn_user->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $selected_game = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validate number of players
    if ($num_players > $selected_game['max_players']) {
        $_SESSION['error'] = "Number of players cannot exceed " . $selected_game['max_players'] . ".";
        header("Location: team_registration.php");
        exit();
    }

    // Handle file upload
    $team_logo_path = '';
    if ($team_logo['error'] === UPLOAD_ERR_OK) {
        $team_logo_path = 'assets/images/teams/' . basename($team_logo['name']);
        move_uploaded_file($team_logo['tmp_name'], '../../' . $team_logo_path);
    }

    // Save the team registration to the database
    try {
        $stmt = $conn_user->prepare("INSERT INTO teams (team_name, sport, team_captain, vice_captain, players, num_players, team_logo, created_by, game_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$team_name, $sport, $team_captain, $vice_captain, $players, $num_players, $team_logo_path, $_SESSION['user_id'], $game_id]);
        $_SESSION['success'] = "Team registered successfully!";
        header("Location: team_registration.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: team_registration.php");
        exit();
    }
}

// Fetch all team registrations with filters
$team_filter = $_GET['team_filter'] ?? '';
$team_sort = $_GET['team_sort'] ?? 'id';
$query = "SELECT teams.*, users.name AS created_by_name FROM teams JOIN users ON teams.created_by = users.id";
if ($team_filter) {
    $query .= " WHERE teams.team_name LIKE :team_filter OR teams.sport LIKE :team_filter";
}
$query .= " ORDER BY $team_sort";
$stmt = $conn_user->prepare($query);
if ($team_filter) {
    $stmt->bindValue(':team_filter', "%$team_filter%");
}
$stmt->execute();
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush();

?>

<!-- Custom CSS for Enhanced Design -->
<style>

        /* Description Section Styling */
        .description-section {
            background: linear-gradient(45deg, #ff7675, #d63031);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .description-section h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .description-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 0;
        }
    /* Background Gradient for Team Registration Page */
    body {
        background: linear-gradient(120deg, #edfbf9, #eecc92);
        min-height: 100vh;
        font-family: 'Arial', sans-serif;
    }

    .team-registration-section {
        background-color: rgba(255, 255, 255, 0.7);
        padding: 3rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 2rem auto;
        max-width: 1000px;
    }

    .team-registration-section h2 {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #6a11cb;
        text-align: center;
        margin-bottom: 2rem;
    }

    .team-registration-section .form-control {
        border-radius: 25px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .team-registration-section .form-control:focus {
        border-color: #6a11cb;
        box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
    }

    .team-registration-section .btn-primary {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .team-registration-section .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .team-registration-section .alert {
        border-radius: 15px;
    }

    .team-registration-section .table-responsive {
        overflow-x: auto;
    }

    .team-registration-section .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .team-registration-section .table th {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        color: white;
    }

    .team-registration-section .table td {
        vertical-align: middle;
    }

    .team-registration-section img {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

    <section class="description-section">
        <h3>About Team Registration </h3>
        <p>
            Gather your team and register for upcoming games and competitions! Provide your team name, captain, 
            vice-captain, and player details to complete the registration process. Ensure all information is 
            accurate and complete before submitting. Once registered, you’ll receive updates about match schedules,
             results, and more. Whether you're a seasoned team or just starting out, this is your chance to 
             showcase your skills and teamwork. Don’t miss this opportunity to compete, connect, and create 
             unforgettable memories. Let’s get your team ready for the challenge!
        </p>
    </section>

<section class="team-registration-section py-5">
    <div class="container">
        <h2>Team Registration</h2>

        <!-- Display Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Display Form for Allowed Users -->
        <?php if ($is_allowed): ?>
            <form action="team_registration.php" method="POST" enctype="multipart/form-data">
                <!-- Game Name (Auto-filled based on game) -->
                <div class="mb-3">
                    <label for="game_id" class="form-label">Game</label>
                    <input type="text" class="form-control" id="game_name" name="sport" value="<?= htmlspecialchars($allowed_games[0]['game_name']) ?>" readonly>
                    <input type="hidden" id="game_id" name="game_id" value="<?= $allowed_games[0]['id'] ?>">
                    <small class="form-text">Allowed to you by administrator.</small>
                </div>

                <!-- Team Name -->
                <div class="mb-3">
                    <label for="team_name" class="form-label">Team Name *</label>
                    <input type="text" class="form-control" id="team_name" name="team_name" required>
                    <small class="form-text">Team Name (which used during whole event)</small>
                </div>

                <!-- Team Captain -->
                <div class="mb-3">
                    <label for="team_captain" class="form-label">Team Captain *</label>
                    <input type="text" class="form-control" id="team_captain" name="team_captain" required>
                    <small class="form-text">Team Captain Name </small>
                </div>

                <!-- Vice Captain -->
                <div class="mb-3">
                    <label for="vice_captain" class="form-label">Vice Captain *</label>
                    <input type="text" class="form-control" id="vice_captain" name="vice_captain" required>
                    <small class="form-text">Team Vice Captain Name </small>
                </div>

                <!-- Players -->
                <div class="mb-3">
                    <label for="players" class="form-label">Players (comma-separated) *</label>
                    <textarea class="form-control" id="players" name="players" rows="5" required></textarea>
                    <small class="form-text text-muted">Maximum <?= $allowed_games[0]['max_players'] ?> players allowed.</small>
                </div>

                <!-- Number of Players -->
                <div class="mb-3">
                    <label for="num_players" class="form-label">Number of Players *</label>
                    <input type="number" class="form-control" id="num_players" name="num_players" max="<?= $allowed_games[0]['max_players'] ?>" required>
                    <small class="form-text text-muted">Maximum <?= $allowed_games[0]['max_players'] ?> players allowed.</small>
                </div>

                <!-- Team Logo -->
                <div class="mb-3">
                    <label for="team_logo" class="form-label">Team Logo</label>
                    <input type="file" class="form-control" id="team_logo" name="team_logo">
                    <small class="form-text text-muted">Upload Your Team Logo (Display in website)</small>
                </div>

                <!-- Submit Button -->
                <small class="form-text text-muted">Check details carefully , you do not edit after submission.</small>
                <button type="submit" class="btn btn-primary w-100">Register Team</button>
            </form>
        <?php elseif ($existing_team): ?>
            <div class="alert alert-info">You have already registered a team for this game.</div>
        <?php else: ?>
            <div class="alert alert-warning">You are not allowed to register a team.</div>
        <?php endif; ?>

        <!-- Display List of Teams with Filters -->
        <h3 class="mt-5 py-5">Registered Teams</h3>
        <form action="team_registration.php" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="team_filter" placeholder="Filter by team name, sport, or game" value="<?= htmlspecialchars($team_filter) ?>">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Game Name</th>
                        <th>Captain</th>
                        <th>Vice Captain</th>
                        <th>Players</th>
                        <th>Team Logo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><?= htmlspecialchars($team['team_name']) ?></td>
                        <td><?= htmlspecialchars($team['sport']) ?></td>
                        <td><?= htmlspecialchars($team['team_captain']) ?></td>
                        <td><?= htmlspecialchars($team['vice_captain']) ?></td>
                        <td><?= htmlspecialchars($team['players']) ?></td>
                        <td>
                            <?php if (!empty($team['team_logo'])): ?>
                                <img src="../../<?= htmlspecialchars($team['team_logo']) ?>" class="img-fluid" style="max-width: 100px;">
                            <?php else: ?>
                                No Logo
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>