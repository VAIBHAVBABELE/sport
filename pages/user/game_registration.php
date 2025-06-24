<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';

// Check if the user is logged in
if (!isLoggedIn()) {
    header("Location: ../../login.php");
    exit();
}

displayAlert();

// Fetch logged-in user details
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn_user->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: game_registration.php");
    exit();
}

// Handle game registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_names = $_POST['selected_games'] ?? [];
    $player_name = $user['name']; // Automatically set player name to the logged-in user's name
    $age = $_POST['age'];
    $course = $user['course']; // Automatically set course from user details
    $branch = $user['branch']; // Automatically set branch from user details
    $year = $user['year']; // Automatically set year from user details
    $roll_no = $user['roll_no']; // Automatically set roll no. from user details
    $mobile = $user['mobile']; // Automatically set mobile no. from user details
    $gender = $_POST['gender'];
    $jersey_no = $_POST['jersey_no'];
    $nickname = $_POST['nickname'];
    $player_image = $user['profile_image']; // Automatically set player image from user details

    if (empty($game_names) || empty($player_name) || empty($age) || empty($gender)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: game_registration.php");
        exit();
    }

    // Save the registration to the database
    try {
        // Insert new registrations
        foreach ($game_names as $game_name) {
            $stmt = $conn_user->prepare("INSERT INTO game_registrations (selected_games, player_name, age, course, branch, year, roll_no, mobile, gender, player_image,nickname,jersey_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$game_name, $player_name, $age, $course, $branch, $year, $roll_no, $mobile, $gender, $player_image,$nickname,$jersey_no]);
        }
        $_SESSION['success'] = "Registration successful!";
        header("Location: game_registration.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: game_registration.php");
        exit();
    }
}

// Check if the user has already registered
$is_registered = false;
$registrations = [];
try {
    $stmt = $conn_user->prepare("SELECT * FROM game_registrations WHERE roll_no = ?");
    $stmt->execute([$user['roll_no']]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($registrations) {
        $is_registered = true;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
// Fetch all team registrations with filters
$game_filter = $_GET['game_filter'] ?? '';
$game_sort = $_GET['game_sort'] ?? 'id';
$query = "SELECT game_registrations.* FROM game_registrations WHERE status = 'approved'";
if ($game_filter) {
    $query .= " WHERE game_registrations.player_name LIKE :game_filter OR game_registrations.selected_games LIKE :game_filter OR game_registrations.year LIKE :game_filter";
}
$query .= " ORDER BY $game_sort";
$stmt = $conn_user->prepare($query);
if ($game_filter) {
    $stmt->bindValue(':game_filter', "%$game_filter%");
}
$stmt->execute();
$game_reg = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush(); // End output buffering and send output to the browser
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
    /* Background Gradient for Game Registration Page */
    body {
        background: linear-gradient(120deg, #edfbf9, #eecc92);
        min-height: 100vh;
    }

    .game-registration-section {
        background-color: rgba(255, 255, 255, 0.7);
        padding: 3rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 2rem auto;
        max-width: 800px;
    }

    .game-registration-section h2 {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #6a11cb;
        text-align: center;
        margin-bottom: 2rem;
    }

    .game-registration-section .form-control {
        border-radius: 25px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .game-registration-section .form-control:focus {
        border-color: #6a11cb;
        box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
    }

    .game-registration-section .form-label {
        font-weight: 600;
        color: #333;
    }

    .game-registration-section .btn-primary {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .game-registration-section .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .game-registration-section .alert {
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .game-registration-section .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .game-registration-section .table th {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        color: white;
    }

    .game-registration-section .table td {
        vertical-align: middle;
    }

    .game-registration-section .img-fluid {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .game-registration-section .form-check-input {
        margin-top: 0.3rem;
    }

    .game-registration-section .form-check-label {
        margin-left: 0.5rem;
    }

    .game-registration-section .form-check-input:checked {
        background-color: #6a11cb;
        border-color: #6a11cb;
    }
</style>

        <section class="description-section">
            <h3>Register for Games</h3>
            <p>
                Ready to join the action? Register for exciting games and competitions here! Select the games you 
                want to participate in, provide your details, and submit your registration. Whether you're a 
                seasoned player or a newcomer, there’s something for everyone. Make sure to review your 
                information before submitting to ensure everything is accurate. Once registered, you’ll 
                receive updates about match schedules, results, and more. Don’t miss this chance to showcase your 
                skills, make new friends, and be part of the excitement. Let’s get started and make your 
                mark in the games!
            </p>
        </section>

<section class="game-registration-section">
    <div class="container">
        
        <h2>Game Registration</h2>

        <!-- Display Alert Message if User Has Already Registered -->
        <?php if ($is_registered): ?>
            <div class="alert alert-info">You have already registered. To make changes, please contact the administrator.</div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="game_registration.php" method="POST" enctype="multipart/form-data">
            <!-- Hidden Fields for User Details -->
            <input type="hidden" name="course" value="<?= htmlspecialchars($user['course']) ?>">
            <input type="hidden" name="branch" value="<?= htmlspecialchars($user['branch']) ?>">
            <input type="hidden" name="year" value="<?= htmlspecialchars($user['year']) ?>">
            <input type="hidden" name="roll_no" value="<?= htmlspecialchars($user['roll_no']) ?>">
            <input type="hidden" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>">
            <input type="hidden" name="player_image" value="<?= htmlspecialchars($user['profile_image']) ?>">

            <!-- Display User Details in Read-Only Mode -->
            <div class="mb-3">
                <label class="form-label">Course</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['course']) ?>" readonly>
                <small class="form-text">Readonly .</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['branch']) ?>" readonly>
                <small class="form-text">Readonly .</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Year</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['year']) ?>" readonly>
                <small class="form-text">Readonly .</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Roll No.</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['roll_no']) ?>" readonly>
                <small class="form-text">Readonly .</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Mobile No.</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['mobile']) ?>" readonly>
                <small class="form-text">Readonly .</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Player Image</label>
                <img src="../../<?= htmlspecialchars($user['profile_image']) ?>" class="img-fluid" style="max-width: 100px;">
                <small class="form-text">Readonly/ upload from profile section (unable to update after submit) .</small>
            </div>

            <!-- Player Name (Automatically Filled) -->
            <div class="mb-4">
                <label for="player_name" class="form-label">Player Name</label>
                <input type="text" class="form-control" id="player_name" name="player_name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
            </div>

            <div class="alert alert-info mb-4">You cannot change the above details. They are for reference only.</div>

            <!-- Age -->
            <div class="mb-3">
                <label for="age" class="form-label">Age *</label>
                <input type="number" class="form-control" id="age" name="age" placeholder="eg.21" required <?= $is_registered ? 'readonly' : '' ?>>
                <small class="form-text">Enter Your Age</small>
            </div>

            <!-- Gender -->
            <div class="mb-3">
                <label for="gender" class="form-label">Gender *</label>
                <select class="form-control" id="gender" name="gender" required <?= $is_registered ? 'disabled' : '' ?>>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <small class="form-text">Enter Your Gender</small>
            </div>

            <!-- Jersey No. -->
            <div class="mb-3">
                <label for="jersey_no" class="form-label">Jersey No.</label>
                <input type="number" class="form-control" id="jersey_no" placeholder="eg.11" name="jersey_no" <?= $is_registered ? 'readonly' : '' ?>>
                <small class="form-text">Enter Jersey No.</small>
            </div>

            <!-- Nickname -->
            <div class="mb-3">
                <label for="nickname" class="form-label">Nick Name</label>
                <input type="text" class="form-control" id="nickname" name="nickname" <?= $is_registered ? 'readonly' : '' ?>>
                <small class="form-text">Enter NickName</small>
            </div>

            <!-- Game Name (Checkboxes) -->
            <div class="mb-3">
                <label class="form-label">Select Games</label>
                <div>
                    <?php
                    $games = ['Cricket', 'Volleyball', 'Khokho', 'Chess', 'Tennis', 'Badminton'];
                    foreach ($games as $game) {
                        $is_checked = in_array($game, array_column($registrations, 'selected_games'));
                        echo '<div class="form-check">';
                        echo '<input class="form-check-input" type="checkbox" id="' . $game . '" name="selected_games[]" value="' . $game . '" ' . ($is_checked ? 'checked' : '') . ' ' . ($is_registered ? 'disabled' : '') . '>';
                        echo '<label class="form-check-label" for="' . $game . '">' . ucfirst($game) . '</label>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <small class="form-text">Select atleast 1 game and atmost 2 game. </small>
            </div>

            <!-- Register Button (Disabled if Already Registered) -->
            <button type="submit" class="btn btn-primary w-100" <?= $is_registered ? 'disabled' : '' ?>>Register</button>
        </form>

        <!-- Display Registered Data in Read-Only Mode -->
        <?php if ($is_registered): ?>
            <div class="mt-5">
                <h3>Your Registered Data</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Player Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>NickName</th>
                            <th>Jersey No.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                            <tr>
                                <td><?= htmlspecialchars($registration['selected_games']) ?></td>
                                <td><?= htmlspecialchars($registration['player_name']) ?></td>
                                <td><?= htmlspecialchars($registration['age']) ?></td>
                                <td><?= htmlspecialchars($registration['gender']) ?></td>
                                <td><?= htmlspecialchars($registration['nickname']) ?></td>
                                <td><?= htmlspecialchars($registration['jersey_no']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Display List of users with Filters -->
        <h3 class="mt-5 py-5">Registered Players</h3>
        <form action="game_registration.php" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="game_filter" placeholder="Filter by player name, game, or year" value="<?= htmlspecialchars($game_filter) ?>">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Game Name</th>
                        <th>Player Name</th>
                        <th>Year</th>
                        <th>NickName</th>
                        <th>Jersey No.</th>
                        <th>Player Image</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($game_reg as $game): ?>
                    <tr>
                    <td><?= htmlspecialchars($game['selected_games']) ?></td>
                                <td><?= htmlspecialchars($game['player_name']) ?></td>
                                <td><?= htmlspecialchars($game['year']) ?></td>
                                <td><?= htmlspecialchars($game['nickname']) ?></td>
                                <td><?= htmlspecialchars($game['jersey_no']) ?></td>
                        <td>
                            <?php if (!empty($game['player_image'])): ?>
                                <img src="../../<?= htmlspecialchars($game['player_image']) ?>" class="img-fluid" style="max-width: 100px;">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- JavaScript for Dynamic Team Name Inputs and Form Validation -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const gameCheckboxes = document.querySelectorAll('input[name="selected_games[]"]');
    const teamNameContainer = document.getElementById('team-name-container');

    // Function to generate team name inputs
    function generateTeamNameInputs() {
        teamNameContainer.innerHTML = ''; // Clear previous inputs
        gameCheckboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const teamNameInput = document.createElement('div');
                teamNameInput.classList.add('mb-3');
                teamNameInput.innerHTML = `
                    <label for="team_name_${index}" class="form-label">Team Name for ${checkbox.nextElementSibling.textContent}</label>
                    <input type="text" class="form-control" id="team_name_${index}" name="team_name[]" required>
                `;
                teamNameContainer.appendChild(teamNameInput);
            }
        });
    }

    // Add event listeners to checkboxes
    gameCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', generateTeamNameInputs);
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        const checkboxes = document.querySelectorAll('input[name="selected_games[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault(); // Prevent form submission
            alert('Please select at least one game.');
        }
        else if (checkboxes.length > 2) {
            e.preventDefault(); // Prevent form submission
            alert('You can select maximum 2 games.');
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>