<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';

if (!isLoggedIn()) {
    header("Location: ../../login.php");
    exit();
}

displayAlert();

// Define team and individual games
$team_games = ['cricket', 'volleyball', 'khokho'];
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
$stmt_team = $conn_user->prepare($query_team);
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
$team_per_page = 5; // Number of entries per page
$team_total_entries = count($leaderboard_team);
$team_total_pages = ceil($team_total_entries / $team_per_page);
$team_offset = ($team_page - 1) * $team_per_page;
$leaderboard_team = array_slice($leaderboard_team, $team_offset, $team_per_page);

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
$stmt_individual = $conn_user->prepare($query_individual);
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
$individual_per_page = 5; // Number of entries per page
$individual_total_entries = count($leaderboard_individual);
$individual_total_pages = ceil($individual_total_entries / $individual_per_page);
$individual_offset = ($individual_page - 1) * $individual_per_page;
$leaderboard_individual = array_slice($leaderboard_individual, $individual_offset, $individual_per_page);
ob_end_flush(); // End output buffering and send output to the browser
?>

<!-- Custom CSS for Enhanced Design -->
<style>
    /* Background Gradient for Leaderboard Page */
    body {
        background: linear-gradient(120deg, #edfbf9, #eecc92);
        min-height: 100vh;
        color: white;
    }

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

    .leaderboard-section {
        background-color: rgba(255, 255, 255, 0.7);
        padding: 3rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 2rem auto;
        max-width: 1200px;
    }

    .leaderboard-section h2 {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #6a11cb;
        text-align: center;
        margin-bottom: 2rem;
    }

    .leaderboard-section h3 {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #6a11cb;
        margin-bottom: 1.5rem;
    }

    .leaderboard-section .form-control {
        border-radius: 25px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .leaderboard-section .form-control:focus {
        border-color: #6a11cb;
        box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
    }

    .leaderboard-section .btn-primary {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .leaderboard-section .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .leaderboard-section .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .leaderboard-section .table th {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .leaderboard-section .table td {
        vertical-align: middle;
    }

    .leaderboard-section .table tr:hover {
        background-color: rgba(106, 17, 203, 0.1);
    }

    .leaderboard-section .pagination .page-item.active .page-link {
        background-color: #6a11cb;
        border-color: #6a11cb;
    }

    .leaderboard-section .pagination .page-link {
        color: #6a11cb;
    }

    .leaderboard-section .pagination .page-link:hover {
        background-color: #6a11cb;
        color: white;
    }

    .leaderboard-section img {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

        <section class="description-section">
            <h3> About LeaderBoard</h3>
            <p>
                Check out the leaderboard to see the latest rankings for team and individual games. 
                Track your progress, celebrate your achievements, and see where you stand among the best. 
                The leaderboard is updated regularly to reflect the latest results and performances. Whether 
                you're aiming for the top spot or just enjoying the competition, the leaderboard is your go-to 
                resource for all things rankings. Use the filters and sorting options to customize your view 
                and find the information you need. Let’s celebrate the spirit of competition and strive for 
                greatness together!
            </p>
        </section>

<section class="leaderboard-section">
    <div class="container">
        <h2>Leaderboard</h2>
        

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

$stmt_team = $conn_user->prepare($query_team);

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
                <th>P</th>
                <th>W</th>
                <th>L</th>
                <th>D/W.O</th>
                <th>NRR</th>
                
                <th>
                    <a href="?team_sort=points&team_order=<?= $team_sort === 'points' && $team_order === 'asc' ? 'desc' : 'asc' ?>&team_filter=<?= $team_filter ?>&team_search=<?= $team_search ?>&team_page=<?= $team_page ?>">
                        Points <?= $team_sort === 'points' ? ($team_order === 'asc' ? '▲' : '▼') : '' ?>
                    </a>
                </th>
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
                <td><?= htmlspecialchars($entry['play']) ?></td>
                <td><?= htmlspecialchars($entry['win']) ?></td>
                <td><?= htmlspecialchars($entry['lost']) ?></td>
                <td><?= htmlspecialchars($entry['draw']) ?></td>
                <td><?= htmlspecialchars($entry['run_rate']) ?></td>
                <td><?= htmlspecialchars($entry['points']) ?></td>
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

$stmt_individual = $conn_user->prepare($query_individual);

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
                
                <td><?= htmlspecialchars($entry['points']) ?></td>
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

<?php include '../../includes/footer.php'; ?>