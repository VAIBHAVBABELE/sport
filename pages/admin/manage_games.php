<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_admin.php'; // Use the admin header
include '../../includes/functions.php';
include '../../includes/db_admin.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login_admin.php");
    exit();
}

displayAlert();

// Fetch all users for the allowed users dropdown
$stmt = $conn_admin->prepare("SELECT id, name, roll_no FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for creating a new game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_game'])) {
    $game_name = $_POST['game_name'];
    $team_name = $_POST['team_name'];
    $type = $_POST['type'];
    $max_players = $_POST['max_players'];
    $allowed_users = $_POST['allowed_users'];

    try {
        $stmt = $conn_admin->prepare("INSERT INTO games (game_name, team_name, type, max_players, allowed_users) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$game_name, $team_name, $type, $max_players, $allowed_users]);
        $_SESSION['success'] = "Game created successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}

// Handle game update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_game'])) {
    $game_id = $_POST['game_id'];
    $game_name = $_POST['game_name'];
    $team_name = $_POST['team_name'];
    $type = $_POST['type'];
    $max_players = $_POST['max_players'];
    $allowed_users = implode(',', $_POST['allowed_users']);

    try {
        $stmt = $conn_admin->prepare("UPDATE games SET game_name = ?, team_name = ?, type = ?, max_players = ?, allowed_users = ? WHERE id = ?");
        $stmt->execute([$game_name, $team_name, $type, $max_players, $allowed_users, $game_id]);
        $_SESSION['success'] = "Game updated successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_team'])) {
    $team_name = $_POST['team_name'];
    $sport = $_POST['sport'];
    $team_captain = $_POST['team_captain'];
    $vice_captain = $_POST['vice_captain'];
    $players = $_POST['players'];
    $created_by = $_SESSION['admin_id'];
    $team_logo = $_FILES['team_logo'];

    // Handle file upload
    $team_logo_path = '';
    if ($team_logo['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/teams/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $team_logo_path = 'assets/images/teams/' . basename($team_logo['name']);
        move_uploaded_file($team_logo['tmp_name'], '../../' . $team_logo_path);
    }

    try {
        $stmt = $conn_admin->prepare("INSERT INTO teams (team_name, sport, team_captain, vice_captain, players, team_logo, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$team_name, $sport, $team_captain, $vice_captain, $players, $team_logo_path, $created_by]);
        $_SESSION['success'] = "Team registered successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_team'])) {
    $team_id = $_POST['team_id'];
    $team_name = $_POST['team_name'];
    $sport = $_POST['sport'];
    $team_captain = $_POST['team_captain'];
    $vice_captain = $_POST['vice_captain'];
    $players = $_POST['players'];
    $team_logo = $_FILES['team_logo'];

    // Handle file upload
    $team_logo_path = '';
    if ($team_logo['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/teams/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $team_logo_path = 'assets/images/teams/' . basename($team_logo['name']);
        move_uploaded_file($team_logo['tmp_name'], '../../' . $team_logo_path);
    } else {
        // If no new file is uploaded, retain the existing logo path
        $stmt = $conn_admin->prepare("SELECT team_logo FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $existing_team = $stmt->fetch(PDO::FETCH_ASSOC);
        $team_logo_path = $existing_team['team_logo'];
    }

    try {
        $stmt = $conn_admin->prepare("UPDATE teams SET team_name = ?, sport = ?, team_captain = ?, vice_captain = ?, players = ?, team_logo = ? WHERE id = ?");
        $stmt->execute([$team_name, $sport, $team_captain, $vice_captain, $players, $team_logo_path, $team_id]);
        $_SESSION['success'] = "Team updated successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}

// Handle game deletion
if (isset($_GET['delete_game'])) {
    $game_id = $_GET['delete_game'];
    try {
        $stmt = $conn_admin->prepare("DELETE FROM games WHERE id = ?");
        $stmt->execute([$game_id]);
        $_SESSION['success'] = "Game deleted successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}

// Handle team deletion
if (isset($_GET['delete_team'])) {
    $team_id = $_GET['delete_team'];
    try {
        $stmt = $conn_admin->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $_SESSION['success'] = "Team deleted successfully!";
        header("Location: manage_games.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_games.php");
        exit();
    }
}

// Fetch all games with filters
$game_filter = $_GET['game_filter'] ?? '';
$game_sort = $_GET['game_sort'] ?? 'id';
$query = "SELECT * FROM games";
if ($game_filter) {
    $query .= " WHERE game_name LIKE :game_filter";
}
$query .= " ORDER BY $game_sort";
$stmt = $conn_admin->prepare($query);
if ($game_filter) {
    $stmt->bindValue(':game_filter', "%$game_filter%");
}
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all team registrations with filters
$team_filter = $_GET['team_filter'] ?? '';
$team_sort = $_GET['team_sort'] ?? 'id';
$query = "SELECT teams.*, users.name AS created_by_name FROM teams JOIN users ON teams.created_by = users.id";
if ($team_filter) {
    $query .= " WHERE teams.team_name LIKE :team_filter OR teams.sport LIKE :team_filter";
}
$query .= " ORDER BY $team_sort";
$stmt = $conn_admin->prepare($query);
if ($team_filter) {
    $stmt->bindValue(':team_filter', "%$team_filter%");
}
$stmt->execute();
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch game details for editing
$edit_game = null;
if (isset($_GET['edit_game'])) {
    $game_id = $_GET['edit_game'];
    $stmt = $conn_admin->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $edit_game = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch team details for editing
$edit_team = null;
if (isset($_GET['edit_team'])) {
    $team_id = $_GET['edit_team'];
    $stmt = $conn_admin->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$team_id]);
    $edit_team = $stmt->fetch(PDO::FETCH_ASSOC);
}

//fetch team name from game
$stmt = $conn_admin->prepare("SELECT DISTINCT team_name FROM games");
$stmt->execute();
$team_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
ob_end_flush(); // End output buffering and send output to the browser
?>

<section class="manage-games-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Manage Games</h2>

        <!-- Game Creation/Edit Form -->
        <?php if (isset($_GET['create_game']) || isset($_GET['edit_game'])): ?>
        <form action="manage_games.php" method="POST" class="mb-4">
            <?php if (isset($edit_game)): ?>
                <input type="hidden" name="game_id" value="<?= $edit_game['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="game_name" class="form-label">Game Name</label>
                <select class="form-control" id="game_name" name="game_name" required>
                    <option value="Cricket" <?= (isset($edit_game) && $edit_game['game_name'] === 'Cricket') ? 'selected' : '' ?>>Cricket</option>
                    <option value="Volleyball" <?= (isset($edit_game) && $edit_game['game_name'] === 'Volleyball') ? 'selected' : '' ?>>Volleyball</option>
                    <option value="Khokho" <?= (isset($edit_game) && $edit_game['game_name'] === 'Khokho') ? 'selected' : '' ?>>Kho-Kho</option>
                    <option value="Chess" <?= (isset($edit_game) && $edit_game['game_name'] === 'Chess') ? 'selected' : '' ?>>Chess</option>
                    <option value="Tennis" <?= (isset($edit_game) && $edit_game['game_name'] === 'Table-Tennis') ? 'selected' : '' ?>>Table Tennis</option>
                    <option value="Badminton" <?= (isset($edit_game) && $edit_game['game_name'] === 'Badminton') ? 'selected' : '' ?>>Badminton</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="team_name" class="form-label">Team Name</label>
                <input type="text" class="form-control" id="team_name" name="team_name" value="<?= isset($edit_game) ? $edit_game['team_name'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <select class="form-control" id="type" name="type" required>
                    <option value="Indoor" <?= (isset($edit_game) && $edit_game['type'] === 'Indoor') ? 'selected' : '' ?>>Indoor</option>
                    <option value="Outdoor" <?= (isset($edit_game) && $edit_game['type'] === 'Outdoor') ? 'selected' : '' ?>>Outdoor</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="max_players" class="form-label">Max Players</label>
                <input type="number" class="form-control" id="max_players" name="max_players" value="<?= isset($edit_game) ? $edit_game['max_players'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="allowed_users" class="form-label">Allowed Users (Name and Roll No.)</label>
                <select class="form-control" id="allowed_users" name="allowed_users[]" multiple required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['roll_no'] ?>"><?= htmlspecialchars($user['name'] . ' - ' . $user['roll_no']) ?></option>
                <?php endforeach; ?>
    </select>
    <small class="text-muted">Hold Ctrl/Cmd to select multiple users.</small>
</div>
            <button type="submit" name="<?= isset($edit_game) ? 'update_game' : 'create_game' ?>" class="btn btn-primary"><?= isset($edit_game) ? 'Update Game' : 'Create Game' ?></button>
            <a href="manage_games.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php else: ?>
        <a href="manage_games.php?create_game=true" class="btn btn-primary mb-4">Create New Game</a>
        <?php endif; ?>

        <!-- Games Table with Filter and Sorting -->
        <h3 class="mb-3">Games List</h3>
        <form action="manage_games.php" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="game_filter" placeholder="Filter by game name" value="<?= htmlspecialchars($game_filter) ?>">
                <select class="form-control" name="game_sort">
                    <option value="id" <?= $game_sort === 'id' ? 'selected' : '' ?>>Sort by ID</option>
                    <option value="game_name" <?= $game_sort === 'game_name' ? 'selected' : '' ?>>Sort by Game Name</option>
                    <option value="type" <?= $game_sort === 'type' ? 'selected' : '' ?>>Sort by Type</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Game Name</th>
                    <th>Team Name</th>
                    <th>Type</th>
                    <th>Max Players</th>
                    <th>Allowed Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td><?= $game['id'] ?></td>
                    <td><?= $game['game_name'] ?></td>
                    <td><?= $game['team_name'] ?></td>
                    <td><?= $game['type'] ?></td>
                    <td><?= $game['max_players'] ?></td>
                    <td><?= nl2br(htmlspecialchars($game['allowed_users'])) ?></td>
                    <td>
                        <a href="manage_games.php?edit_game=<?= $game['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="manage_games.php?delete_game=<?= $game['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="manage-team-registrations-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Manage Team Registrations</h2>

        <!-- Team Registration/Edit Form -->
        <?php if (isset($_GET['register_team']) || isset($_GET['edit_team'])): ?>
        <form action="manage_games.php" method="POST" class="mb-4">
            <?php if (isset($edit_team)): ?>
                <input type="hidden" name="team_id" value="<?= $edit_team['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="team_name" class="form-label">Team Name</label>
                <input type="text" class="form-control" id="team_name" name="team_name" list="team_names" required>
                <datalist id="team_names">
                    <?php foreach ($team_names as $name): ?>
                    <option value="<?= $name ?>">
                    <?php endforeach; ?>
    </datalist>
</div>
            <div class="mb-3">
                <label for="sport" class="form-label">Sport</label>
                <select class="form-control" id="sport" name="sport" required>
                    <option value="Cricket" <?= (isset($edit_team) && $edit_team['sport'] === 'Cricket') ? 'selected' : '' ?>>Cricket</option>
                    
                    <option value="Volleyball" <?= (isset($edit_team) && $edit_team['sport'] === 'Volleyball') ? 'selected' : '' ?>>Volleyball</option>
                    <option value="Khokho" <?= (isset($edit_team) && $edit_team['sport'] === 'Khokho') ? 'selected' : '' ?>> Kho-Kho</option>
                    <option value="Chess" <?= (isset($edit_team) && $edit_team['sport'] === 'Chess') ? 'selected' : '' ?>>Chess</option>
                    <option value="Tennis" <?= (isset($edit_team) && $edit_team['sport'] === 'Tennis') ? 'selected' : '' ?>>Table Tennis</option>
                    <option value="Badminton" <?= (isset($edit_team) && $edit_team['sport'] === 'Badminton') ? 'selected' : '' ?>>Badminton</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="team_captain" class="form-label">Team Captain</label>
                <input type="text" class="form-control" id="team_captain" name="team_captain" value="<?= isset($edit_team) ? $edit_team['team_captain'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="vice_captain" class="form-label">Vice Captain</label>
                <input type="text" class="form-control" id="vice_captain" name="vice_captain" value="<?= isset($edit_team) ? $edit_team['vice_captain'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="players" class="form-label">Players</label>
                <textarea class="form-control" id="players" name="players" rows="3" required><?= isset($edit_team) ? $edit_team['players'] : '' ?></textarea>
                <small class="text-muted">Enter one player per line.</small>
            </div>
            <!-- Team Logo -->
            <div class="mb-3">
                    <label for="team_logo" class="form-label">Team Logo</label>
                    <input type="file" class="form-control" id="team_logo" name="team_logo">
                    <small class="form-text text-muted">Upload Your Team Logo (Display in website)</small>
                </div>
            <button type="submit" name="<?= isset($edit_team) ? 'update_team' : 'register_team' ?>" class="btn btn-primary"><?= isset($edit_team) ? 'Update Team' : 'Register Team' ?></button>
            <a href="manage_games.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php else: ?>
        <a href="manage_games.php?register_team=true" class="btn btn-primary mb-4">Register New Team</a>
        <?php endif; ?>

        <!-- Team Registration Filter and Sorting -->
        <form action="manage_games.php" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="team_filter" placeholder="Filter by team name or sport" value="<?= htmlspecialchars($team_filter) ?>">
                <select class="form-control" name="team_sort">
                    <option value="id" <?= $team_sort === 'id' ? 'selected' : '' ?>>Sort by ID</option>
                    <option value="team_name" <?= $team_sort === 'team_name' ? 'selected' : '' ?>>Sort by Team Name</option>
                    <option value="sport" <?= $team_sort === 'sport' ? 'selected' : '' ?>>Sort by Sport</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>

        <!-- Teams Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Team Name</th>
                    <th>Sport</th>
                    <th>Captain</th>
                    <th>Vice-Captain</th>
                    <th>Players</th>
                    <th>Team Logo</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $team): ?>
                <tr>
                    <td><?= $team['id'] ?></td>
                    <td><?= $team['team_name'] ?></td>
                    <td><?= $team['sport'] ?></td>
                    <td><?= $team['team_captain'] ?></td>
                    <td><?= $team['vice_captain'] ?></td>
                    <td><?= $team['players'] ?></td>
                    <td>
                        <?php if ($team['team_logo']): ?>
                            <img src="../../<?= $team['team_logo'] ?>" class="img-fluid" style="max-width: 100px;">
                        <?php else: ?>
                            No Logo
                        <?php endif; ?>
                    </td>
                    <td><?= $team['created_by_name'] ?></td>
                    <td>
                        <a href="manage_games.php?edit_team=<?= $team['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="manage_games.php?delete_team=<?= $team['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this team?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>