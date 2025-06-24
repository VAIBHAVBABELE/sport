<?php
ob_start();
session_start();
include '../../includes/header_admin.php';
include '../../includes/functions.php';
include '../../includes/db_admin.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login_admin.php");
    exit();
}

displayAlert();

// Fetch all teams grouped by sport
$stmt_teams = $conn_admin->query("SELECT team_name, sport FROM teams");
$teams_by_sport = [];
while ($team = $stmt_teams->fetch(PDO::FETCH_ASSOC)) {
    $teams_by_sport[$team['sport']][] = $team['team_name'];
}

// Handle form submission for creating a new fixture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_fixture'])) {
    $game_name = $_POST['game_name'];
    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];
    $match_date = $_POST['match_date'];
    // Convert from HTML5 datetime-local format to MySQL DATETIME format
    $mysql_datetime = date('Y-m-d H:i:s', strtotime($match_date));
    $venue = $_POST['venue'];
    $match_type = $_POST['match_type'];
    $stmt = $conn_admin->prepare("INSERT INTO fixtures (game_name, team1, team2, match_date, venue, match_type) VALUES (:game_name, :team1, :team2, :match_date, :venue, :match_type)");
    $stmt->execute([
        ':game_name' => $game_name,
        ':team1' => $team1,
        ':team2' => $team2,
        ':match_date' => $mysql_datetime,
        ':venue' => $venue,
        ':match_type' => $match_type
    ]);

    $_SESSION['success'] = "Fixture created successfully!";
    header("Location: manage_fixtures.php");
    exit();
}

// Handle form submission for updating a fixture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fixture'])) {
    $id = $_POST['id'];
    $game_name = $_POST['game_name'];
    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];
    $match_date = $_POST['match_date'];
    // Convert from HTML5 datetime-local format to MySQL DATETIME format
    $mysql_datetime = date('Y-m-d H:i:s', strtotime($match_date));
    $venue = $_POST['venue'];
    $match_type = $_POST['match_type'];
    $stmt = $conn_admin->prepare("UPDATE fixtures SET game_name = :game_name, team1 = :team1, team2 = :team2, match_date = :match_date, venue = :venue, match_type = :match_type WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':game_name' => $game_name,
        ':team1' => $team1,
        ':team2' => $team2,
        ':match_date' => $mysql_datetime,
        ':venue' => $venue,
        ':match_type' => $match_type
    ]);

    $_SESSION['success'] = "Fixture updated successfully!";
    header("Location: manage_fixtures.php");
    exit();
}

// Handle deletion of a fixture
if (isset($_GET['delete_fixture_id'])) {
    $id = $_GET['delete_fixture_id'];
    
    try {
        $conn_admin->beginTransaction();
        
        // First delete related match results
        $stmt = $conn_admin->prepare("DELETE FROM match_results WHERE fixture_id = :id");
        $stmt->execute([':id' => $id]);
        
        // Then delete the fixture
        $stmt = $conn_admin->prepare("DELETE FROM fixtures WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $conn_admin->commit();
        
        $_SESSION['success'] = "Fixture deleted successfully!";
    } catch (PDOException $e) {
        $conn_admin->rollBack();
        $_SESSION['error'] = "Error deleting fixture: " . $e->getMessage();
    }
    
    header("Location: manage_fixtures.php");
    exit();
}

// Handle form submission for inserting scores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert_score'])) {
    $fixture_id = $_POST['fixture_id'];
    $team1_score = $_POST['team1_score'];
    $team2_score = $_POST['team2_score'];

    // Fetch team names from the fixtures table
    $stmt_fixture = $conn_admin->prepare("SELECT team1, team2 FROM fixtures WHERE id = :fixture_id");
    $stmt_fixture->execute([':fixture_id' => $fixture_id]);
    $fixture = $stmt_fixture->fetch(PDO::FETCH_ASSOC);

    // Determine the winner based on scores
    if ($team1_score > $team2_score) {
        $winner = $fixture['team1'];
    } elseif ($team1_score < $team2_score) {
        $winner = $fixture['team2'];
    } else {
        $winner = 'Draw';
    }

    $stmt = $conn_admin->prepare("INSERT INTO match_results (fixture_id, team1_score, team2_score, winner) VALUES (:fixture_id, :team1_score, :team2_score, :winner)");
    $stmt->execute([
        ':fixture_id' => $fixture_id,
        ':team1_score' => $team1_score,
        ':team2_score' => $team2_score,
        ':winner' => $winner
    ]);

    $_SESSION['success'] = "Score added successfully!";
    header("Location: manage_fixtures.php");
    exit();
}

// Handle form submission for updating scores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_score'])) {
    $id = $_POST['id'];
    $team1_score = $_POST['team1_score'];
    $team2_score = $_POST['team2_score'];

    // Fetch fixture_id and team names from the match_results and fixtures tables
    $stmt = $conn_admin->prepare("
        SELECT f.team1, f.team2
        FROM match_results mr
        JOIN fixtures f ON mr.fixture_id = f.id
        WHERE mr.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determine the winner based on scores
    if ($team1_score > $team2_score) {
        $winner = $result['team1'];
    } elseif ($team1_score < $team2_score) {
        $winner = $result['team2'];
    } else {
        $winner = 'Draw';
    }

    $stmt = $conn_admin->prepare("UPDATE match_results SET team1_score = :team1_score, team2_score = :team2_score, winner = :winner WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':team1_score' => $team1_score,
        ':team2_score' => $team2_score,
        ':winner' => $winner
    ]);

    $_SESSION['success'] = "Score updated successfully!";
    header("Location: manage_fixtures.php");
    exit();
}

// Handle deletion of a score
if (isset($_GET['delete_score_id'])) {
    $id = $_GET['delete_score_id'];
    $stmt = $conn_admin->prepare("DELETE FROM match_results WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['success'] = "Score deleted successfully!";
    header("Location: manage_fixtures.php");
    exit();
}

// Fetch all fixtures
$stmt_fixtures = $conn_admin->query("SELECT * FROM fixtures");
$fixtures = $stmt_fixtures->fetchAll(PDO::FETCH_ASSOC);

// Fetch all match results with team names from fixtures
$stmt_results = $conn_admin->query("
    SELECT mr.id, mr.fixture_id, f.game_name,f.match_type, f.team1, f.team2, mr.team1_score, mr.team2_score, mr.winner
    FROM match_results mr
    JOIN fixtures f ON mr.fixture_id = f.id
");
$match_results = $stmt_results->fetchAll(PDO::FETCH_ASSOC);

// Convert teams_by_sport to JSON for JavaScript use
$teams_by_sport_json = json_encode($teams_by_sport);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fixtures and Scores</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .table-section {
            margin-bottom: 30px;
        }
        .modal-header {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4">Manage Fixtures and Scores</h2>

        <!-- Create New Fixture Section -->
        <div class="form-section">
            <h3>Create New Fixture</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="game_name">Game Name</label>
                        <select class="form-control" id="game_name" name="game_name" required>
                            <option value="">Select a game</option>
                            <option value="Cricket">Cricket</option>
                            <option value="Volleyball">Volleyball</option>
                            <option value="Khokho">Khokho</option>
                            <option value="Badminton">Badminton</option>
                            <option value="Chess">Chess</option>
                            <option value="Tennis">Tennis</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="match_type">Match Type</label>
                        <select class="form-control" id="match_type" name="match_type" required>
                            <option value="knockout">Knockout</option>
                            <option value="league" selected>League</option>
                            <option value="final">Final</option>
                            <option value="semi-final">Semi Final</option>
                            <option value="quarter-final">Quarter Final</option>
                            <option value="friendly">Friendly</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="match_date">Match Date</label>
                        <input type="datetime-local" class="form-control" id="match_date" name="match_date" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="team1">Team 1</label>
                        <select class="form-control" id="team1" name="team1" required>
                            <option value="">Select a game first</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="team2">Team 2</label>
                        <select class="form-control" id="team2" name="team2" required>
                            <option value="">Select Team 1 first</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="venue">Venue</label>
                    <select class="form-control" id="venue" name="venue" required>
                        <option value="" selected>Select a ground</option>
                        <option value="Cricket ground">Cricket ground</option>
                        <option value="Volleyball ground" >Volleyball ground</option>
                        <option value="Kho kho ground">Kho kho ground</option>
                        <option value="Badminton Ground">Badminton Ground</option>
                        <option value="Table Tennis court">Table Tennis court</option>
                    </select>    
                </div>
                <button type="submit" name="create_fixture" class="btn btn-primary">
                    Create Fixture
                </button>
            </form>
        </div>

        <!-- Fixtures Table -->
        <div class="table-section">
            <h3>Fixtures</h3>
            <table id="fixturesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Game Name</th>
                        <th>Match Type</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                        <th>Match Date</th>
                        <th>Venue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fixtures as $fixture): ?>
                    <tr>
                        <td><?= $fixture['id'] ?></td>
                        <td><?= ucfirst($fixture['game_name']) ?></td>
                        <td><?= ucwords(str_replace('-', ' ', $fixture['match_type'])) ?></td>
                        <td><?= $fixture['team1'] ?></td>
                        <td><?= $fixture['team2'] ?></td>
                        <td><?= date('d M Y h:i A', strtotime($fixture['match_date'])) ?></td>
                        <td><?= $fixture['venue'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-fixture-btn" 
                                    data-id="<?= $fixture['id'] ?>"
                                    data-game-name="<?= $fixture['game_name'] ?>"
                                    data-match-type="<?= $fixture['match_type'] ?>"
                                    data-team1="<?= $fixture['team1'] ?>"
                                    data-team2="<?= $fixture['team2'] ?>"
                                    data-match-date="<?= $fixture['match_date'] ?>"
                                    data-venue="<?= $fixture['venue'] ?>">
                                Edit
                            </button>
                            <a href="?delete_fixture_id=<?= $fixture['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this fixture?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Score Section -->
        <div class="form-section">
            <h3>Add Score</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fixture_id">Fixture</label>
                        <select class="form-control" id="fixture_id" name="fixture_id" required>
                            <?php foreach ($fixtures as $f): ?>
                                <option value="<?= $f['id'] ?>">
                                    <?= ucfirst($f['game_name']) ?> of <?= $f['match_type'] ?> Match : <?= $f['team1'] ?> vs <?= $f['team2'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="team1_score">Team 1 Score</label>
                        <input type="number" class="form-control" id="team1_score" name="team1_score" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="team2_score">Team 2 Score</label>
                        <input type="number" class="form-control" id="team2_score" name="team2_score" required>
                    </div>
                </div>
                <button type="submit" name="insert_score" class="btn btn-primary">
                    Add Score
                </button>
            </form>
        </div>

        <!-- Match Results Table -->
        <div class="table-section">
            <h3>Match Results</h3>
            <table id="resultsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Game</th>
                        <th>Match Type</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>Team 2</th>
                        <th>Score</th>
                        <th>Winner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($match_results as $result): ?>
                    <tr>
                        <td><?= $result['id'] ?></td>
                        <td><?= ucfirst($result['game_name']) ?></td>
                        <td><?= $result['match_type'] ?></td>
                        <td><?= $result['team1'] ?></td>
                        <td><?= $result['team1_score'] ?></td>
                        <td><?= $result['team2'] ?></td>
                        <td><?= $result['team2_score'] ?></td>
                        <td><?= $result['winner'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-score-btn" 
                                    data-id="<?= $result['id'] ?>"
                                    data-fixture-id="<?= $result['fixture_id'] ?>"
                                    data-team1-score="<?= $result['team1_score'] ?>"
                                    data-team2-score="<?= $result['team2_score'] ?>">
                                Edit
                            </button>
                            <a href="?delete_score_id=<?= $result['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this score?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Fixture Modal -->
    <div class="modal fade" id="editFixtureModal" tabindex="-1" role="dialog" aria-labelledby="editFixtureModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFixtureModalLabel">Edit Fixture</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_fixture_id">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_game_name">Game Name</label>
                                <select class="form-control" id="edit_game_name" name="game_name" required>
                                    <option value="">Select a game</option>
                                    <option value="Cricket">Cricket</option>
                                    <option value="Volleyball">Volleyball</option>
                                    <option value="Khokho">Khokho</option>
                                    <option value="Badminton">Badminton</option>
                                    <option value="Chess">Chess</option>
                                    <option value="Tennis">Tennis</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_match_type">Match Type</label>
                                <select class="form-control" id="edit_match_type" name="match_type" required>
                                    <option value="knockout">Knockout</option>
                                    <option value="league">League</option>
                                    <option value="final">Final</option>
                                    <option value="semi-final">Semi Final</option>
                                    <option value="quarter-final">Quarter Final</option>
                                    <option value="friendly">Friendly</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edit_match_date">Match Date</label>
                                <input type="datetime-local" class="form-control" id="edit_match_date" name="match_date" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_team1">Team 1</label>
                                <select class="form-control" id="edit_team1" name="team1" required>
                                    <option value="">Select a game first</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edit_team2">Team 2</label>
                                <select class="form-control" id="edit_team2" name="team2" required>
                                    <option value="">Select Team 1 first</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="venue">Venue</label>
                            <select class="form-control" id="venue" name="venue" required>
                                <option value="">Select a ground</option>
                                <option value="Cricket ground">Cricket ground</option>
                                <option value="Volleyball ground">Volleyball ground</option>
                                <option value="Kho kho ground">Kho kho ground</option>
                                <option value="Badminton Ground">Badminton Ground</option>
                                <option value="Table Tennis court">Table Tennis court</option>
                            </select>    
                </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_fixture" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Score Modal -->
    <div class="modal fade" id="editScoreModal" tabindex="-1" role="dialog" aria-labelledby="editScoreModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScoreModalLabel">Edit Score</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_score_id">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_score_fixture_id">Fixture</label>
                                <select class="form-control" id="edit_score_fixture_id" name="fixture_id" required>
                                    <?php foreach ($fixtures as $f): ?>
                                        <option value="<?= $f['id'] ?>">
                                            <?= ucfirst($f['game_name']) ?> of <?= $f['match_type'] ?> Match : <?= $f['team1'] ?> vs <?= $f['team2'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_team1_score">Team 1 Score</label>
                                <input type="number" class="form-control" id="edit_team1_score" name="team1_score" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="edit_team2_score">Team 2 Score</label>
                                <input type="number" class="form-control" id="edit_team2_score" name="team2_score" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_score" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#fixturesTable').DataTable({
                responsive: true
            });

            $('#resultsTable').DataTable({
                responsive: true
            });

            // Teams data from PHP
            const teamsBySport = <?= $teams_by_sport_json ?>;
            
            // Function to populate team dropdowns
            function populateTeamDropdowns(gameName, team1Value, team2Value) {
                const teams = teamsBySport[gameName] || [];
                
                // Populate Team 1 dropdown
                const $team1 = $('#edit_team1');
                $team1.empty();
                if (teams.length > 0) {
                    $team1.append('<option value="">Select Team 1</option>');
                    teams.forEach(team => {
                        $team1.append(`<option value="${team}">${team}</option>`);
                    });
                    if (team1Value) {
                        $team1.val(team1Value);
                    }
                } else {
                    $team1.append('<option value="">No teams available</option>');
                }

                // Populate Team 2 dropdown
                const $team2 = $('#edit_team2');
                $team2.empty();
                if (teams.length > 1) {
                    $team2.append('<option value="">Select Team 2</option>');
                    teams.forEach(team => {
                        if (!team1Value || team !== team1Value) {
                            $team2.append(`<option value="${team}">${team}</option>`);
                        }
                    });
                    if (team2Value) {
                        $team2.val(team2Value);
                    }
                } else {
                    $team2.append('<option value="">No other teams available</option>');
                }
            }

            // When game name changes in create form
            $('#game_name').change(function() {
                const gameName = $(this).val();
                const teams = teamsBySport[gameName] || [];

                // Populate Team 1 dropdown
                const $team1 = $('#team1');
                $team1.empty();
                if (teams.length > 0) {
                    $team1.append('<option value="">Select Team 1</option>');
                    teams.forEach(team => {
                        $team1.append(`<option value="${team}">${team}</option>`);
                    });
                } else {
                    $team1.append('<option value="">No teams available</option>');
                }

                // Clear Team 2 dropdown
                $('#team2').empty().append('<option value="">Select Team 1 first</option>');
            });

            // When Team 1 changes in create form
            $('#team1').change(function() {
                const selectedTeam = $(this).val();
                if (!selectedTeam) return;

                const gameName = $('#game_name').val();
                const teams = teamsBySport[gameName] || [];

                // Populate Team 2 dropdown with all teams except the selected one
                const $team2 = $('#team2');
                $team2.empty();
                if (teams.length > 1) {
                    $team2.append('<option value="">Select Team 2</option>');
                    teams.forEach(team => {
                        if (team !== selectedTeam) {
                            $team2.append(`<option value="${team}">${team}</option>`);
                        }
                    });
                } else {
                    $team2.append('<option value="">No other teams available</option>');
                }
            });

            // When game name changes in edit modal
            $('#edit_game_name').change(function() {
                const gameName = $(this).val();
                const team1Value = $('#edit_team1').val();
                populateTeamDropdowns(gameName, team1Value, null);
            });

            // When Team 1 changes in edit modal
            $('#edit_team1').change(function() {
                const selectedTeam = $(this).val();
                if (!selectedTeam) return;

                const gameName = $('#edit_game_name').val();
                const teams = teamsBySport[gameName] || [];

                // Populate Team 2 dropdown with all teams except the selected one
                const $team2 = $('#edit_team2');
                $team2.empty();
                if (teams.length > 1) {
                    $team2.append('<option value="">Select Team 2</option>');
                    teams.forEach(team => {
                        if (team !== selectedTeam) {
                            $team2.append(`<option value="${team}">${team}</option>`);
                        }
                    });
                } else {
                    $team2.append('<option value="">No other teams available</option>');
                }
            });

            // Edit Fixture button click handler
            $('.edit-fixture-btn').click(function() {
                const fixtureId = $(this).data('id');
                const gameName = $(this).data('game-name');
                const matchType = $(this).data('match-type');

                const team1 = $(this).data('team1');
                const team2 = $(this).data('team2');
                const matchDate = $(this).data('match-date');


                $('#edit_fixture_id').val(fixtureId);
                $('#edit_game_name').val(gameName);
                $('#edit_match_type').val(matchType);
                // Convert MySQL datetime to datetime-local format (remove the seconds if present)
                const localDatetime = matchDate.replace(' ', 'T').substring(0, 16);
                $('#edit_match_date').val(localDatetime);
                const venue = $(this).data('venue');
                $('#edit_venue').val(venue);
                
                // Populate team dropdowns
                populateTeamDropdowns(gameName, team1, team2);

                $('#editFixtureModal').modal('show');
            });

            // Edit Score button click handler
            $('.edit-score-btn').click(function() {
                const scoreId = $(this).data('id');
                const fixtureId = $(this).data('fixture-id');
                const team1Score = $(this).data('team1-score');
                const team2Score = $(this).data('team2-score');

                $('#edit_score_id').val(scoreId);
                $('#edit_score_fixture_id').val(fixtureId);
                $('#edit_team1_score').val(team1Score);
                $('#edit_team2_score').val(team2Score);

                $('#editScoreModal').modal('show');
            });
        });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>