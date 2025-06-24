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

// Fetch all game registrations
try {
    $query = "SELECT * FROM game_registrations";

    // Handle sorting
    $sort_column = $_GET['sort'] ?? 'id';
    $sort_order = $_GET['order'] ?? 'asc';
    $allowed_columns = ['id', 'selected_games', 'player_name', 'age', 'gender', 'course', 'branch', 'year', 'roll_no', 'mobile', 'nickname', 'jersey_no'];
    if (in_array($sort_column, $allowed_columns)) {
        $query .= " ORDER BY $sort_column $sort_order";
    }

    $stmt = $conn_admin->prepare($query);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn_admin->prepare("DELETE FROM game_registrations WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = "Registration deleted successfully!";
        header("Location: manage_game_registration.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_game_registration.php");
        exit();
    }
}

// Handle approve action
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id'];
    try {
        // Correct UPDATE syntax with SET clause
        $stmt = $conn_admin->prepare("UPDATE game_registrations 
                                     SET status = 'approved' 
                                     WHERE id = ?");
        $stmt->execute([$approve_id]);
        
        $_SESSION['success'] = "Registration updated successfully!";
        header("Location: manage_game_registration.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_game_registration.php");
        exit();
    }
}

// Handle edit action
$edit_mode = false;
$edit_data = [];

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    try {
        $stmt = $conn_admin->prepare("SELECT * FROM game_registrations WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $edit_mode = true;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_game_registration.php");
        exit();
    }
}

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_registration'])) {
    $id = $_POST['id'];
    $selected_games = $_POST['selected_games'];
    $player_name = $_POST['player_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $nickname = $_POST['nickname'];
    $jersey_no = $_POST['jersey_no'];

    try {
        $stmt = $conn_admin->prepare("UPDATE game_registrations SET selected_games = ?, player_name = ?, age = ?, gender = ?, nickname = ?, jersey_no = ? WHERE id = ?");
        $stmt->execute([$selected_games, $player_name, $age, $gender, $nickname, $jersey_no, $id]);
        $_SESSION['success'] = "Registration updated successfully!";
        header("Location: manage_game_registration.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_game_registration.php");
        exit();
    }
}

ob_end_flush(); // End output buffering and send output to the browser
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Game Registrations</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            min-height: 100vh;
        }

        .admin-section {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
        }

        .admin-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-section .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-section .table th {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            cursor: pointer;
        }

        .admin-section .table td {
            vertical-align: middle;
        }

        .admin-section .btn {
            margin: 0 5px;
        }

        .admin-section .btn-edit {
            background: linear-gradient(45deg, #28a745, #218838);
            border: none;
        }

        .admin-section .btn-delete {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
        }

        .admin-section .btn:hover {
            opacity: 0.9;
        }

        .search-box {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <section class="admin-section">
        <div class="container">
            <h2>Game Registrations</h2>

            <!-- Display Success or Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Search Box -->
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
            </div>

            <!-- Edit Form (Visible in Edit Mode) -->
            <?php if ($edit_mode): ?>
                <form method="POST" class="mb-5">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <div class="mb-3">
                        <label for="selected_games" class="form-label">Game</label>
                        <select class="form-control" id="selected_games" name="selected_games" required>
                            <?php
                            $games = ['Cricket', 'Volleyball', 'Khokho', 'Chess', 'Tennis', 'Badminton'];
                            foreach ($games as $game) {
                                $selected = ($game === $edit_data['selected_games']) ? 'selected' : '';
                                echo "<option value='$game' $selected>" . ucfirst($game) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="player_name" class="form-label">Player Name</label>
                        <input type="text" class="form-control" id="player_name" name="player_name" value="<?= htmlspecialchars($edit_data['player_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" value="<?= htmlspecialchars($edit_data['age']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="male" <?= $edit_data['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $edit_data['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $edit_data['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nickname</label>
                        <input type="text" class="form-control" id="nickname" name="nickname" value="<?= htmlspecialchars($edit_data['nickname']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="jersey_no" class="form-label">Jersey No.</label>
                        <input type="number" class="form-control" id="jersey_no" name="jersey_no" value="<?= htmlspecialchars($edit_data['jersey_no']) ?>">
                    </div>
                    <button type="submit" name="update_registration" class="btn btn-primary">Update</button>
                    <a href="manage_game_registration.php" class="btn btn-secondary">Cancel</a>
                </form>
            <?php endif; ?>

            <!-- Game Registrations Table -->
            <table class="table table-bordered" id="registrationTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('id')">ID</th>
                        <th onclick="sortTable('selected_games')">Game</th>
                        <th onclick="sortTable('player_name')">Player Name</th>
                        <th onclick="sortTable('age')">Age</th>
                        <th onclick="sortTable('gender')">Gender</th>
                        <th onclick="sortTable('course')">Course</th>
                        <th onclick="sortTable('branch')">Branch</th>
                        <th onclick="sortTable('year')">Year</th>
                        <th onclick="sortTable('roll_no')">Roll No.</th>
                        <th onclick="sortTable('mobile')">Mobile</th>
                        <th onclick="sortTable('nickname')">Nickname</th>
                        <th onclick="sortTable('jersey_no')">Jersey No.</th>
                        <th>Player Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?= htmlspecialchars($registration['id']) ?></td>
                            <td><?= htmlspecialchars($registration['selected_games']) ?></td>
                            <td><?= htmlspecialchars($registration['player_name']) ?></td>
                            <td><?= htmlspecialchars($registration['age']) ?></td>
                            <td><?= htmlspecialchars($registration['gender']) ?></td>
                            <td><?= htmlspecialchars($registration['course']) ?></td>
                            <td><?= htmlspecialchars($registration['branch']) ?></td>
                            <td><?= htmlspecialchars($registration['year']) ?></td>
                            <td><?= htmlspecialchars($registration['roll_no']) ?></td>
                            <td><?= htmlspecialchars($registration['mobile']) ?></td>
                            <td><?= htmlspecialchars($registration['nickname']) ?></td>
                            <td><?= htmlspecialchars($registration['jersey_no']) ?></td>
                            <td>
                                <img src="../../<?= htmlspecialchars($registration['player_image']) ?>" class="img-fluid" style="max-width: 100px;">
                            </td>
                            <td>
                            <a href="manage_game_registration.php?approve_id=<?= $registration['id'] ?>" class="btn btn-primary btn-sm">approve</a>
                                <!-- Edit Button -->
                                <a href="manage_game_registration.php?edit_id=<?= $registration['id'] ?>" class="btn btn-edit btn-sm">Edit</a>
                                <!-- Delete Button -->
                                <a href="manage_game_registration.php?delete_id=<?= $registration['id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this registration?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS for Search and Sort -->
    <script>
        // Search Functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#registrationTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Sort Functionality
        function sortTable(column) {
            const url = new URL(window.location.href);
            const sortOrder = url.searchParams.get('order') === 'asc' ? 'desc' : 'asc';
            url.searchParams.set('sort', column);
            url.searchParams.set('order', sortOrder);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>