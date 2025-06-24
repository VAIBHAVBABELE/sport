<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php'; // Use the user header
include '../../includes/functions.php';
include '../../includes/db_user.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login_user.php");
    exit();
}

displayAlert();

// Fetch all fixtures
$stmt_fixtures = $conn_user->query("SELECT * FROM fixtures");
$fixtures = $stmt_fixtures->fetchAll(PDO::FETCH_ASSOC);

// Fetch all match results with team names from fixtures
$stmt_results = $conn_user->query("
    SELECT mr.id, f.game_name,f.match_type, f.team1, f.team2, mr.team1_score, mr.team2_score, mr.winner
    FROM match_results mr
    JOIN fixtures f ON mr.fixture_id = f.id
");
$match_results = $stmt_results->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush(); // End output buffering and send output to the browser
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fixtures and Scores</title>
    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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

        /* Background Gradient for Fixtures Page */
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            min-height: 100vh;
            color: #343a40;
            font-family: 'Arial', sans-serif;
        }

        .manage-fixtures-section {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
        }

        .manage-fixtures-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
        }

        .manage-fixtures-section h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            margin-bottom: 1.5rem;
        }

        .manage-fixtures-section .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .manage-fixtures-section .table th {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .manage-fixtures-section .table td {
            vertical-align: middle;
        }

        .manage-fixtures-section .table tr:hover {
            background-color: rgba(106, 17, 203, 0.1);
        }

        .manage-fixtures-section .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .manage-fixtures-section .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .manage-fixtures-section .pagination .page-item.active .page-link {
            background-color: #6a11cb;
            border-color: #6a11cb;
        }

        .manage-fixtures-section .pagination .page-link {
            color: #6a11cb;
        }

        .manage-fixtures-section .pagination .page-link:hover {
            background-color: #6a11cb;
            color: white;
        }
    </style>
</head>
<body>

        <section class="description-section">
            <h3> Match Fixtures & Results</h3>
            <p>
                View the latest fixtures for upcoming games and matches. Stay updated with match dates, venues, and participating teams.
                Plan ahead and don’t miss out on the action. Check back regularly for updates and changes to the schedule.

                Check out the latest match results and see how your favorite teams are performing. Find detailed scores, winners,
    and high    lights from recent games. Celebrate victories and analyze performances to improve for the next match!
            </p>
        </section>
    <section class="manage-fixtures-section py-5">
        <div class="container">
        
            <h2>View Fixtures and Scores</h2>

            <!-- Fixtures Table -->
            <h3><i class="fas fa-calendar-alt"></i> Fixtures</h3>
            <div class="table-responsive">
                <table id="fixturesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Game Name</th>
                            <th>Match Type</th>
                            <th>Team 1</th>
                            <th>Team 2</th>
                            <th>Match Date</th>
                            <th>Venue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixtures as $fixture): ?>
                        <tr>
                            
                            <td><?= $fixture['game_name'] ?></td>
                            <td><?= $fixture['match_type'] ?></td>
                            <td><?= $fixture['team1'] ?></td>
                            <td><?= $fixture['team2'] ?></td>
                            <td><?= date('d M Y h:i A', strtotime($fixture['match_date'])) ?></td>
                            <td><?= $fixture['venue'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Match Results Table -->
            <h3 class="mt-5"><i class="fas fa-trophy"></i> Match Results</h3>
            <div class="table-responsive">
                <table id="resultsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game Name</th>
                            <th>Match Type</th>
                            <th>Team 1</th>
                            <th>Team 1 Score</th>
                            <th>Team 2</th>
                            <th>Team 2 Score</th>
                            <th>Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($match_results as $result): ?>
                        <tr>
                            <td><?= $result['id'] ?></td>
                            <td><?= $result['game_name'] ?></td>
                            <td><?= $result['match_type'] ?></td>
                            <td><?= $result['team1'] ?></td>
                            <td><?= $result['team1_score'] ?></td>
                            <td><?= $result['team2'] ?></td>
                            <td><?= $result['team2_score'] ?></td>
                            <td><?= $result['winner'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#fixturesTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                responsive: true
            });

            $('#resultsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                responsive: true
            });
        });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>