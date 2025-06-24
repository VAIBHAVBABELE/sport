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


// Fetch all teams grouped by sport for dropdowns
$stmt_teams = $conn_admin->query("SELECT team_name, sport FROM teams ORDER BY sport, team_name");
$teams_by_sport = [];
while ($team = $stmt_teams->fetch(PDO::FETCH_ASSOC)) {
    $teams_by_sport[$team['sport']][] = $team['team_name'];
}

// Convert to JSON for JavaScript
$teams_by_sport_json = json_encode($teams_by_sport);

// Fetch all approved players grouped by game
$stmt_players = $conn_admin->query("SELECT player_name, selected_games FROM game_registrations WHERE status = 'approved' ORDER BY selected_games, player_name");
$players_by_game = [];
while ($player = $stmt_players->fetch(PDO::FETCH_ASSOC)) {
    $players_by_game[$player['selected_games']][] = $player['player_name'];
}

// Convert to JSON for JavaScript
$players_by_game_json = json_encode($players_by_game);

// Handle form submissions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or Update Leaderboard Entry
    if (isset($_POST['add_entry']) || isset($_POST['update_entry'])) {
        $id = $_POST['id'] ?? null; // For update
        $game_name = $_POST['game_name'];
        $team_name = $_POST['team_name'] ?? null;
        $player_name = $_POST['player_name'] ?? null;
        $points = $_POST['points'];
       

        // Validate required fields
        if (empty($game_name) ) {
            $_SESSION['error'] = "Game name and points are required.";
            header("Location: manage_leaderboard.php");
            exit();
        }

        // Validate team/player fields based on game type
        if (in_array($game_name, ['cricket', 'volleyball', 'khokho'])) {
            if (empty($team_name)) {
                $_SESSION['error'] = "Team name is required for team games.";
                header("Location: manage_leaderboard.php");
                exit();
            }
        } else {
            if (empty($player_name)) {
                $_SESSION['error'] = "Player name is required for individual games.";
                header("Location: manage_leaderboard.php");
                exit();
            }
        }

        try {
            if (isset($_POST['add_entry'])) {
                // Insert new entry
                $stmt = $conn_admin->prepare("INSERT INTO leaderboard (game_name, team_name, player_name, points) VALUES (?, ?, ?, ?)");
                $stmt->execute([$game_name, $team_name, $player_name, $points]);
                $_SESSION['success'] = "Leaderboard entry added successfully!";
            } elseif (isset($_POST['update_entry'])) {
                // Update existing entry
                $stmt = $conn_admin->prepare("UPDATE leaderboard SET game_name = ?, team_name = ?, player_name = ?, points = ? WHERE id = ?");
                $stmt->execute([$game_name, $team_name, $player_name, $points, $id]);
                $_SESSION['success'] = "Leaderboard entry updated successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    // Delete Leaderboard Entry
    if (isset($_POST['delete_entry'])) {
        $id = $_POST['id'];

        try {
            $stmt = $conn_admin->prepare("DELETE FROM leaderboard WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Leaderboard entry deleted successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    // Update All Stats (Play, Win, Lost, Draw, Run Rate, Points)
if (isset($_POST['update_all_stats'])) {
    $id = $_POST['id'];
    $play = $_POST['play'] ?? 0;
    $win = $_POST['win'] ?? 0;
    $lost = $_POST['lost'] ?? 0;
    $draw = $_POST['draw'] ?? 0;
    $run_rate = $_POST['run_rate'] ?? 0.00;
    $points = $_POST['points'] ?? 0;

    // Validate that play = win + lost + draw
    if ($play != ($win + $lost + $draw)) {
        $_SESSION['error'] = "Play must equal Win + Lost + Draw";
        header("Location: manage_leaderboard.php");
        exit();
    }

    try {
        $stmt = $conn_admin->prepare("UPDATE leaderboard SET play = ?, win = ?, lost = ?, draw = ?, run_rate = ?, points = ? WHERE id = ?");
        $stmt->execute([$play, $win, $lost, $draw, $run_rate, $points, $id]);
        $_SESSION['success'] = "Team stats updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    header("Location: manage_leaderboard.php");
    exit();
}
}

// Fetch all leaderboard entries
$selected_game = $_GET['game'] ?? ''; // Filter by game
$query = "SELECT * FROM leaderboard";
if ($selected_game) {
    $query .= " WHERE game_name = ?";
    $stmt = $conn_admin->prepare($query);
    $stmt->execute([$selected_game]);
} else {
    $stmt = $conn_admin->query($query);
}
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sort entries by points in descending order
usort($leaderboard, function ($a, $b) {
    return $b['points'] - $a['points'];
});

// Fetch entry details for editing
$edit_entry = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn_admin->prepare("SELECT * FROM leaderboard WHERE id = ?");
    $stmt->execute([$id]);
    $edit_entry = $stmt->fetch(PDO::FETCH_ASSOC);
}
ob_end_flush(); // End output buffering and send output to the browser
?>
<?php
// Define game mappings (form values => database values)
$game_mappings = [
    'cricket' => 'Cricket',
    'volleyball' => 'Volleyball',
    'khokho' => 'Khokho',
    'tennis' => 'Tennis',
    'badminton' => 'Badminton',
    'chess' => 'Chess'
];

// Fetch all teams grouped by sport (with original capitalization)
$teams_by_sport = [];
$stmt_teams = $conn_admin->query("SELECT team_name, sport FROM teams ORDER BY sport, team_name");
while ($team = $stmt_teams->fetch(PDO::FETCH_ASSOC)) {
    $teams_by_sport[$team['sport']][] = $team['team_name'];
}

// Debug: Show teams data
echo "<!-- TEAMS BY SPORT:\n" . print_r($teams_by_sport, true) . "\n-->";

// Fetch all approved players grouped by game (with original capitalization)
$players_by_game = [];
$stmt_players = $conn_admin->query("SELECT player_name, selected_games FROM game_registrations WHERE status = 'approved' ORDER BY selected_games, player_name");
while ($player = $stmt_players->fetch(PDO::FETCH_ASSOC)) {
    $players_by_game[$player['selected_games']][] = $player['player_name'];
}

// Debug: Show players data
echo "<!-- PLAYERS BY GAME:\n" . print_r($players_by_game, true) . "\n-->";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // [Previous form handling code remains the same]
    // ...
}

// [Previous leaderboard fetching code remains the same]
// ...
?>

<section class="manage-leaderboard-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Manage Leaderboard</h2>

        <!-- Add/Edit Leaderboard Entry Form -->
        <div class="mb-4">
            <h3><?= $edit_entry ? 'Edit Entry' : 'Add New Entry' ?></h3>
            <?php if ($edit_entry): ?>
                <a href="manage_leaderboard.php" class="btn btn-secondary mb-3">Add New Entry</a>
            <?php endif; ?>
            <form action="manage_leaderboard.php" method="POST">
                <?php if ($edit_entry): ?>
                <input type="hidden" name="id" value="<?= $edit_entry['id'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="game_name" class="form-label">Game Name</label>
                    <select class="form-control" id="game_name" name="game_name" required onchange="updateDropdowns(this.value)">
                        <option value="">Select Game</option>
                        <?php foreach ($game_mappings as $form_value => $db_value): ?>
                            <option value="<?= $form_value ?>" <?= ($edit_entry && strtolower($edit_entry['game_name']) === strtolower($form_value)) ? 'selected' : '' ?>>
                                <?= $db_value ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="team_fields" style="display: <?= (isset($edit_entry) && in_array(strtolower($edit_entry['game_name']), ['cricket', 'volleyball', 'khokho'])) ? 'block' : 'none' ?>;">
                    <div class="mb-3">
                        <label for="team_name" class="form-label">Team Name</label>
                        <select class="form-control" id="team_name" name="team_name" >
                            <option value="">Select Team</option>
                            <?php 
                            if (isset($edit_entry) && in_array(strtolower($edit_entry['game_name']), ['cricket', 'volleyball', 'khokho'])) {
                                $current_game = $game_mappings[strtolower($edit_entry['game_name'])];
                                if (isset($teams_by_sport[$current_game])) {
                                    foreach ($teams_by_sport[$current_game] as $team) {
                                        $selected = ($edit_entry && $edit_entry['team_name'] === $team) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($team) . "' $selected>" . htmlspecialchars($team) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div id="individual_fields" style="display: <?= (isset($edit_entry) && in_array(strtolower($edit_entry['game_name']), ['tennis', 'badminton', 'chess'])) ? 'block' : 'none' ?>;">
                    <div class="mb-3">
                        <label for="player_name" class="form-label">Player Name</label>
                        <select class="form-control" id="player_name" name="player_name">
                            <option value="">Select Player</option>
                            <?php 
                            if (isset($edit_entry) && in_array(strtolower($edit_entry['game_name']), ['tennis', 'badminton', 'chess'])) {
                                $current_game = $game_mappings[strtolower($edit_entry['game_name'])];
                                if (isset($players_by_game[$current_game])) {
                                    foreach ($players_by_game[$current_game] as $player) {
                                        $selected = ($edit_entry && $edit_entry['player_name'] === $player) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($player) . "' $selected>" . htmlspecialchars($player) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="points" class="form-label">Points</label>
                    <input type="number" class="form-control" id="points" name="points" value="<?= $edit_entry['points'] ?? '0' ?>" min="0" required>
                </div>
                
                <button type="submit" name="<?= $edit_entry ? 'update_entry' : 'add_entry' ?>" class="btn btn-primary w-100">
                    <?= $edit_entry ? 'Update Entry' : 'Add Entry' ?>
                </button>
            </form>
        </div>
    </div>
</section>

<script>
function toggleFormFields() {
    const gameName = document.getElementById('game_name').value;
    const teamFields = document.getElementById('team_fields');
    const individualFields = document.getElementById('individual_fields');

    if (['cricket', 'volleyball', 'khokho'].includes(gameName)) {
        teamFields.style.display = 'block';
        individualFields.style.display = 'none';
    } else {
        teamFields.style.display = 'none';
        individualFields.style.display = 'block';
    }
}

// Initialize form fields on page load
document.addEventListener('DOMContentLoaded', toggleFormFields);
</script>

<?php
// Define team and individual games
$team_games = ['cricket', 'volleyball', 'khokho'] ;
$individual_games = ['tennis', 'badminton', 'chess'];

// Fetch team games leaderboard
$team_filter = $_GET['team_filter'] ?? '';
$team_search = $_GET['team_search'] ?? '';
$team_sort = $_GET['team_sort'] ?? 'points';
$team_order = $_GET['team_order'] ?? 'desc';
$team_page = $_GET['team_page'] ?? 1;

$query_team = "SELECT * FROM leaderboard WHERE game_name IN ('" . implode("','", $team_games) . "')";
if (!empty($team_filter)) {
    $query_team .= " AND game_name = :team_filter";
}
if (!empty($team_search)) {
    $query_team .= " AND (team_name LIKE :team_search OR player_name LIKE :team_search)";
}
$stmt_team = $conn_admin->prepare($query_team);
if (!empty($team_filter)) {
    $stmt_team->bindValue(':team_filter', $team_filter);
}
if (!empty($team_search)) {
    $stmt_team->bindValue(':team_search', "%$team_search%");
}
$stmt_team->execute();
$leaderboard_team = $stmt_team->fetchAll(PDO::FETCH_ASSOC);



// Fetch individual games leaderboard
$individual_filter = $_GET['individual_filter'] ?? '';
$individual_search = $_GET['individual_search'] ?? '';
$individual_sort = $_GET['individual_sort'] ?? 'points';
$individual_order = $_GET['individual_order'] ?? 'desc';
$individual_page = $_GET['individual_page'] ?? 1;

$query_individual = "SELECT * FROM leaderboard WHERE game_name IN ('" . implode("','", $individual_games) . "')";
if (!empty($individual_filter)) {
    $query_individual .= " AND game_name = :individual_filter";
}
if (!empty($individual_search)) {
    $query_individual .= " AND (team_name LIKE :individual_search OR player_name LIKE :individual_search)";
}
$stmt_individual = $conn_admin->prepare($query_individual);
if (!empty($individual_filter)) {
    $stmt_individual->bindValue(':individual_filter', $individual_filter);
}
if (!empty($individual_search)) {
    $stmt_individual->bindValue(':individual_search', "%$individual_search%");
}
$stmt_individual->execute();
$leaderboard_individual = $stmt_individual->fetchAll(PDO::FETCH_ASSOC);


?>

<section class="leaderboard-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Leaderboard</h2>

        <!-- Team Games Leaderboard -->
<h3>Team Games Leaderboard</h3>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <select name="team_filter" class="form-control">
                <option value="">All Team Games</option>
                <option value="cricket" <?= $team_filter === 'cricket' ? 'selected' : '' ?>>Cricket</option>
                <option value="volleyball" <?= $team_filter === 'volleyball' ? 'selected' : '' ?>>Volleyball</option>
                <option value="khokho" <?= $team_filter === 'khokho' ? 'selected' : '' ?>>Kho-Kho</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="team_search" class="form-control" placeholder="Search by team name" value="<?= htmlspecialchars($team_search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<?php
// Fetch team games leaderboard with joined data from teams table
$query_team = "SELECT l.*, t.team_captain, t.vice_captain, t.team_logo 
               FROM leaderboard l
               LEFT JOIN teams t ON l.game_name = t.sport AND l.team_name = t.team_name
               WHERE l.game_name IN ('" . implode("','", $team_games) . "')";

if (!empty($team_filter)) {
    $query_team .= " AND l.game_name = :team_filter";
}
if (!empty($team_search)) {
    $query_team .= " AND (l.team_name LIKE :team_search OR l.player_name LIKE :team_search)";
}

$stmt_team = $conn_admin->prepare($query_team);

if (!empty($team_filter)) {
    $stmt_team->bindValue(':team_filter', $team_filter);
}
if (!empty($team_search)) {
    $stmt_team->bindValue(':team_search', "%$team_search%");
}

$stmt_team->execute();
$leaderboard_team = $stmt_team->fetchAll(PDO::FETCH_ASSOC);

// Sort team games leaderboard
usort($leaderboard_team, function ($a, $b) use ($team_sort, $team_order) {
    if ($team_order === 'asc') {
        return $a[$team_sort] <=> $b[$team_sort];
    } else {
        return $b[$team_sort] <=> $a[$team_sort];
    }
});

// Pagination for team games leaderboard
$team_per_page = 5;
$team_total_entries = count($leaderboard_team);
$team_total_pages = ceil($team_total_entries / $team_per_page);
$team_offset = ($team_page - 1) * $team_per_page;
$leaderboard_team = array_slice($leaderboard_team, $team_offset, $team_per_page);
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Rank</th>
                <th>
                    <a href="?team_sort=game_name&team_order=<?= $team_sort === 'game_name' && $team_order === 'asc' ? 'desc' : 'asc' ?>&team_filter=<?= $team_filter ?>&team_search=<?= $team_search ?>&team_page=<?= $team_page ?>">
                        Game Name <?= $team_sort === 'game_name' ? ($team_order === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>Team Name</th>
                <th>Captain</th>
                <th>Vice Captain</th>
                <th>Team Logo</th>
                <th>Play</th>
                <th>Win</th>
                <th>Lost</th>
                <th>Draw</th>
                <th>Run Rate</th>
                <th>Points</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaderboard_team as $index => $entry): ?>
                <tr>
    <td><?= $team_offset + $index + 1 ?></td>
    <td><?= htmlspecialchars($entry['game_name']) ?></td>
    <td><?= htmlspecialchars($entry['team_name']) ?></td>
    <td><?= isset($entry['team_captain']) ? htmlspecialchars($entry['team_captain']) : '-' ?></td>
    <td><?= isset($entry['vice_captain']) ? htmlspecialchars($entry['vice_captain']) : '-' ?></td>
    <td>
        <?php if (!empty($entry['team_logo'])): ?>
            <img src="../../<?= htmlspecialchars($entry['team_logo']) ?>" width="60" alt="Team Logo">
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td>
        <form action="manage_leaderboard.php" method="POST" style="display: inline;">
            <input type="hidden" name="id" value="<?= $entry['id'] ?>">
            <input type="number" class="form-control" name="play" value="<?= $entry['play'] ?>" style="width: 60px; display: inline;">
    </td>
    <td>
            <input type="number" class="form-control" name="win" value="<?= $entry['win'] ?>" style="width: 60px; display: inline;">
    </td>
    <td>
            <input type="number" class="form-control" name="lost" value="<?= $entry['lost'] ?>" style="width: 60px; display: inline;">
    </td>
    <td>
            <input type="number" class="form-control" name="draw" value="<?= $entry['draw'] ?>" style="width: 60px; display: inline;">
    </td>
    <td>
            <input type="number" step="0.01" class="form-control" name="run_rate" value="<?= $entry['run_rate'] ?>" style="width: 80px; display: inline;">
    </td>
    <td>
            <input type="number" class="form-control" name="points" value="<?= $entry['points'] ?>" style="width: 80px; display: inline;">
    </td>
    <td>
            <button type="submit" name="update_all_stats" class="btn btn-sm btn-success">Update</button>
        </form>
        <form action="manage_leaderboard.php" method="POST" style="display: inline;">
            <input type="hidden" name="id" value="<?= $entry['id'] ?>">
            <a href="manage_leaderboard.php?edit=<?= $entry['id'] ?>" class="btn btn-warning btn-sm mt-1">Edit</a>
            <button type="submit" name="delete_entry" class="btn btn-danger btn-sm mt-1">Delete</button>
        </form>
    </td>
</tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination for Team Games -->
<nav aria-label="Page navigation">
    <ul class="pagination">
        <?php for ($i = 1; $i <= $team_total_pages; $i++): ?>
        <li class="page-item <?= $i == $team_page ? 'active' : '' ?>">
            <a class="page-link" href="?team_page=<?= $i ?>&team_filter=<?= $team_filter ?>&team_search=<?= $team_search ?>&team_sort=<?= $team_sort ?>&team_order=<?= $team_order ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>

       <!-- Individual Games Leaderboard -->
       <h3 class="mt-5">Individual Games Leaderboard</h3>
<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <select name="individual_filter" class="form-control">
                <option value="">All Individual Games</option>
                <option value="tennis" <?= $individual_filter === 'tennis' ? 'selected' : '' ?>>Tennis</option>
                <option value="badminton" <?= $individual_filter === 'badminton' ? 'selected' : '' ?>>Badminton</option>
                <option value="chess" <?= $individual_filter === 'chess' ? 'selected' : '' ?>>Chess</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="individual_search" class="form-control" placeholder="Search by player name" value="<?= htmlspecialchars($individual_search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<?php
// Fetch individual games leaderboard with joined data from game_registrations table
$query_individual = "SELECT l.*, gr.jersey_no, gr.nickname, gr.player_image, gr.year 
                     FROM leaderboard l
                     LEFT JOIN game_registrations gr ON l.game_name = gr.selected_games AND l.player_name = gr.player_name
                     WHERE l.game_name IN ('" . implode("','", $individual_games) . "')";

if (!empty($individual_filter)) {
    $query_individual .= " AND l.game_name = :individual_filter";
}
if (!empty($individual_search)) {
    $query_individual .= " AND (l.player_name LIKE :individual_search OR l.team_name LIKE :individual_search)";
}

$stmt_individual = $conn_admin->prepare($query_individual);

if (!empty($individual_filter)) {
    $stmt_individual->bindValue(':individual_filter', $individual_filter);
}
if (!empty($individual_search)) {
    $stmt_individual->bindValue(':individual_search', "%$individual_search%");
}

$stmt_individual->execute();
$leaderboard_individual = $stmt_individual->fetchAll(PDO::FETCH_ASSOC);

// Sort individual games leaderboard
usort($leaderboard_individual, function ($a, $b) use ($individual_sort, $individual_order) {
    if ($individual_order === 'asc') {
        return $a[$individual_sort] <=> $b[$individual_sort];
    } else {
        return $b[$individual_sort] <=> $a[$individual_sort];
    }
});

// Pagination for individual games leaderboard
$individual_per_page = 5;
$individual_total_entries = count($leaderboard_individual);
$individual_total_pages = ceil($individual_total_entries / $individual_per_page);
$individual_offset = ($individual_page - 1) * $individual_per_page;
$leaderboard_individual = array_slice($leaderboard_individual, $individual_offset, $individual_per_page);
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Rank</th>
                <th>
                    <a href="?individual_sort=game_name&individual_order=<?= $individual_sort === 'game_name' && $individual_order === 'asc' ? 'desc' : 'asc' ?>&individual_filter=<?= $individual_filter ?>&individual_search=<?= $individual_search ?>&individual_page=<?= $individual_page ?>">
                        Game Name <?= $individual_sort === 'game_name' ? ($individual_order === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
                <th>Player Name</th>
                <th>Year</th>
                <th>Nickname</th>
                <th>Jersey No.</th>
                <th>Player Image</th>
                <th>
                    <a href="?individual_sort=points&individual_order=<?= $individual_sort === 'points' && $individual_order === 'asc' ? 'desc' : 'asc' ?>&individual_filter=<?= $individual_filter ?>&individual_search=<?= $individual_search ?>&individual_page=<?= $individual_page ?>">
                        Points <?= $individual_sort === 'points' ? ($individual_order === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaderboard_individual as $index => $entry): ?>
            <tr>
                <td><?= $individual_offset + $index + 1 ?></td>
                <td><?= htmlspecialchars($entry['game_name']) ?></td>
                <td><?= htmlspecialchars($entry['player_name']) ?></td>
                <td><?= isset($entry['year']) ? htmlspecialchars($entry['year']) : '-' ?></td>
                <td><?= isset($entry['nickname']) ? htmlspecialchars($entry['nickname']) : '-' ?></td>
                <td><?= isset($entry['jersey_no']) ? htmlspecialchars($entry['jersey_no']) : '-' ?></td>
                <td>
                    <?php if (!empty($entry['player_image'])): ?>
                        <img src="../../<?= htmlspecialchars($entry['player_image']) ?>" width="60" alt="Player Image">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                
                <td>
                    <form action="manage_leaderboard.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                        <input type="number" class="form-control" name="points" value="<?= $entry['points'] ?>" style="width: 80px; display: inline;">
                        <button type="submit" name="update_points" class="btn btn-sm btn-success">Update</button>
                    </form>                    
                </td>
                <td>
                    <a href="manage_leaderboard.php?edit=<?= $entry['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form action="manage_leaderboard.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                        <button type="submit" name="delete_entry" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination for Individual Games -->
<nav aria-label="Page navigation">
    <ul class="pagination">
        <?php for ($i = 1; $i <= $individual_total_pages; $i++): ?>
        <li class="page-item <?= $i == $individual_page ? 'active' : '' ?>">
            <a class="page-link" href="?individual_page=<?= $i ?>&individual_filter=<?= $individual_filter ?>&individual_search=<?= $individual_search ?>&individual_sort=<?= $individual_sort ?>&individual_order=<?= $individual_order ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>        

        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (e.submitter && e.submitter.name === 'update_all_stats') {
                const play = parseInt(this.elements['play'].value) || 0;
                const win = parseInt(this.elements['win'].value) || 0;
                const lost = parseInt(this.elements['lost'].value) || 0;
                const draw = parseInt(this.elements['draw'].value) || 0;
                
                if (play !== (win + lost + draw)) {
                    alert('Error: Play must equal Win + Lost + Draw');
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        });
    });
});
// Game mappings between form values (lowercase) and database values (capitalized)
const gameMappings = {
    'cricket': 'Cricket',
    'volleyball': 'Volleyball',
    'khokho': 'Khokho',
    'tennis': 'Tennis',
    'badminton': 'Badminton',
    'chess': 'Chess'
};

// Convert PHP arrays to JS with proper capitalization
const teamsBySport = <?= json_encode($teams_by_sport) ?>;
const playersByGame = <?= json_encode($players_by_game) ?>;

function updateDropdowns(formGameName) {
    const teamFields = document.getElementById('team_fields');
    const individualFields = document.getElementById('individual_fields');
    const teamSelect = document.getElementById('team_name');
    const playerSelect = document.getElementById('player_name');
    
    // Reset dropdowns
    teamSelect.innerHTML = '<option value="">Select Team</option>';
    playerSelect.innerHTML = '<option value="">Select Player</option>';
    
    // Get database game name
    const dbGameName = gameMappings[formGameName.toLowerCase()] || formGameName;
    
    if (['cricket', 'volleyball', 'khokho'].includes(formGameName.toLowerCase())) {
        teamFields.style.display = 'block';
        individualFields.style.display = 'none';
        
        if (teamsBySport[dbGameName]) {
            teamsBySport[dbGameName].forEach(team => {
                const option = document.createElement('option');
                option.value = team;
                option.textContent = team;
                teamSelect.appendChild(option);
            });
        }
    } 
    else if (['tennis', 'badminton', 'chess'].includes(formGameName.toLowerCase())) {
        teamFields.style.display = 'none';
        individualFields.style.display = 'block';
        
        if (playersByGame[dbGameName]) {
            playersByGame[dbGameName].forEach(player => {
                const option = document.createElement('option');
                option.value = player;
                option.textContent = player;
                playerSelect.appendChild(option);
            });
        }
    } 
    else {
        teamFields.style.display = 'none';
        individualFields.style.display = 'none';
    }
}

// Initialize form fields on page load
document.addEventListener('DOMContentLoaded', function() {
    const gameSelect = document.getElementById('game_name');
    if (gameSelect.value) {
        updateDropdowns(gameSelect.value);
    }
    
    // If editing, preselect the correct value
    <?php if (isset($edit_entry)): ?>
        setTimeout(() => {
            if (['cricket', 'volleyball', 'khokho'].includes('<?= strtolower($edit_entry['game_name']) ?>')) {
                document.getElementById('team_name').value = '<?= $edit_entry['team_name'] ?? '' ?>';
            } else {
                document.getElementById('player_name').value = '<?= $edit_entry['player_name'] ?? '' ?>';
            }
        }, 100);
    <?php endif; ?>
});

</script>

<?php include '../../includes/footer.php'; ?>