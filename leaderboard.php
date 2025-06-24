<?php
ob_start(); // Start output buffering
session_start();
include 'includes/header.php';
include 'includes/functions.php';
include 'includes/db_user.php';



displayAlert();

// Define team and individual games
$cricket = ['cricket'];
$volleyball = ['volleyball'];
$khokho = ['khokho'];


// Fetch team games leaderboard

$team_search = $_GET['team_search'] ?? '';
$team_sort = $_GET['team_sort'] ?? 'points';
$team_order = $_GET['team_order'] ?? 'desc';
$team_page = $_GET['team_page'] ?? 1;

$query_team = "SELECT * FROM leaderboard WHERE game_name ='cricket'";
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
                The leaderboard is updated regularly to reflect the latest results and performances. 
            </p>
        </section>

<section class="leaderboard-section">
    <div class="container">
        <h2>Leaderboard</h2>
        

       <!-- Team Games Leaderboard -->
<h3>Cricket Leaderboard</h3>




<?php
// Fetch team games leaderboard with joined data from teams table
$query_team = "SELECT l.*, t.team_captain, t.vice_captain, t.team_logo 
               FROM leaderboard l
               LEFT JOIN teams t ON l.game_name = t.sport AND l.team_name = t.team_name
               WHERE l.game_name IN ('" . implode("','", $cricket) . "')";

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
$team_per_page = 10;
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
                <td><?= htmlspecialchars($entry['team_name']) ?></td>
                <td><?= isset($entry['team_captain']) ? htmlspecialchars($entry['team_captain']) : '-' ?></td>
                <td><?= isset($entry['vice_captain']) ? htmlspecialchars($entry['vice_captain']) : '-' ?></td>
                <td>
                    <?php if (!empty($entry['team_logo'])): ?>
                        <img src="<?= htmlspecialchars($entry['team_logo']) ?>" width="60" alt="Team Logo">
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


<h3>Volleyball Leaderboard</h3>




<?php
// Fetch team games leaderboard with joined data from teams table
$query_team = "SELECT l.*, t.team_captain, t.vice_captain, t.team_logo 
               FROM leaderboard l
               LEFT JOIN teams t ON l.game_name = t.sport AND l.team_name = t.team_name
               WHERE l.game_name IN ('" . implode("','", $volleyball) . "')";

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
$team_per_page = 10;
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
                
                <th>Team Name</th>
                <th>Captain</th>
                <th>Vice Captain</th>
                <th>Team Logo</th>
                <th>P</th>
                <th>W</th>
                <th>L</th>
                <th>D/W.O</th>
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
                <td><?= htmlspecialchars($entry['team_name']) ?></td>
                <td><?= isset($entry['team_captain']) ? htmlspecialchars($entry['team_captain']) : '-' ?></td>
                <td><?= isset($entry['vice_captain']) ? htmlspecialchars($entry['vice_captain']) : '-' ?></td>
                <td>
                    <?php if (!empty($entry['team_logo'])): ?>
                        <img src="<?= htmlspecialchars($entry['team_logo']) ?>" width="60" alt="Team Logo">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($entry['play']) ?></td>
                <td><?= htmlspecialchars($entry['win']) ?></td>
                <td><?= htmlspecialchars($entry['lost']) ?></td>
                <td><?= htmlspecialchars($entry['draw']) ?></td>
                <td><?= htmlspecialchars($entry['points']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h3>Kho-Kho Leaderboard</h3>




<?php
// Fetch team games leaderboard with joined data from teams table
$query_team = "SELECT l.*, t.team_captain, t.vice_captain, t.team_logo 
               FROM leaderboard l
               LEFT JOIN teams t ON l.game_name = t.sport AND l.team_name = t.team_name
               WHERE l.game_name IN ('" . implode("','", $khokho) . "')";

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
$team_per_page = 10;
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
                
                <th>Team Name</th>
                <th>Captain</th>
                <th>Vice Captain</th>
                <th>Team Logo</th>
                <th>P</th>
                <th>W</th>
                <th>L</th>
                <th>D/W.O</th>
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
                <td><?= htmlspecialchars($entry['team_name']) ?></td>
                <td><?= isset($entry['team_captain']) ? htmlspecialchars($entry['team_captain']) : '-' ?></td>
                <td><?= isset($entry['vice_captain']) ? htmlspecialchars($entry['vice_captain']) : '-' ?></td>
                <td>
                    <?php if (!empty($entry['team_logo'])): ?>
                        <img src="<?= htmlspecialchars($entry['team_logo']) ?>" width="60" alt="Team Logo">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($entry['play']) ?></td>
                <td><?= htmlspecialchars($entry['win']) ?></td>
                <td><?= htmlspecialchars($entry['lost']) ?></td>
                <td><?= htmlspecialchars($entry['draw']) ?></td>
                <td><?= htmlspecialchars($entry['points']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

   
    </div>
</section>

<?php include 'includes/footer.php'; ?>