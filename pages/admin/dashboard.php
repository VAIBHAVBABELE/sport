<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_admin.php'; // Use the admin header
include '../../includes/functions.php';
include '../../includes/db_admin.php'; // Include the PDO database connection file

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login_admin.php");
    exit();
}

displayAlert();

// Fetch user details for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $user_id = $_GET['edit'];
    try {
        $stmt = $conn_admin->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}

// Handle form submission for updating user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $roll_no = $_POST['roll_no'];
    $course = $_POST['course'];
    $branch = $_POST['branch'];
    $year = $_POST['year'];
    $mobile = $_POST['mobile'];

    // Validate required fields
    if (empty($name) || empty($email) || empty($roll_no) || empty($course) || empty($branch) || empty($year) || empty($mobile)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: dashboard.php?edit=$user_id");
        exit();
    }

    try {
        $stmt = $conn_admin->prepare("UPDATE users SET name = ?, email = ?, roll_no = ?, course = ?, branch = ?, year = ?, mobile = ? WHERE id = ?");
        $stmt->execute([$name, $email, $roll_no, $course, $branch, $year, $mobile, $user_id]);
        $_SESSION['success'] = "User updated successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: dashboard.php?edit=$user_id");
        exit();
    }
}

// Handle delete user request
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    try {
        $stmt = $conn_admin->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "User deleted successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}

// Fetch all users from the users table
$query = "SELECT * FROM users";
$stmt = $conn_admin->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to fetch statistical data using PDO
function getStatisticalData($conn_admin) {
    $data = [];

    // Users Table
    $query = "SELECT COUNT(*) as total_users FROM users";
    $stmt = $conn_admin->query($query);
    $data['total_users'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as first_year_users FROM users WHERE year = 1";
    $stmt = $conn_admin->query($query);
    $data['first_year_users'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as second_year_users FROM users WHERE year = 2";
    $stmt = $conn_admin->query($query);
    $data['second_year_users'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as third_year_users FROM users WHERE year = 3";
    $stmt = $conn_admin->query($query);
    $data['third_year_users'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fourth_year_users FROM users WHERE year = 4";
    $stmt = $conn_admin->query($query);
    $data['fourth_year_users'] = $stmt->fetchColumn();

    // Fixtures Table
    $query = "SELECT COUNT(*) as total_fixtures FROM fixtures";
    $stmt = $conn_admin->query($query);
    $data['total_fixtures'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fix_volleyball FROM fixtures WHERE game_name = 'Volleyball'";
    $stmt = $conn_admin->query($query);
    $data['fix_volleyball'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fix_cricket FROM fixtures WHERE game_name = 'Cricket'";
    $stmt = $conn_admin->query($query);
    $data['fix_cricket'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fix_khokho FROM fixtures WHERE game_name = 'Khokho'";
    $stmt = $conn_admin->query($query);
    $data['fix_khokho'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fix_chess FROM fixtures WHERE game_name = 'Chess'";
    $stmt = $conn_admin->query($query);
    $data['fix_chess'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as fix_badminton FROM fixtures WHERE game_name = 'Badminton'";
    $stmt = $conn_admin->query($query);
    $data['fix_badminton'] = $stmt->fetchColumn();
    $query = "SELECT COUNT(*) as tabletennis FROM fixtures WHERE game_name = 'Tennis'";
    $stmt = $conn_admin->query($query);
    $data['fix_tabletennis'] = $stmt->fetchColumn();

    // Game Registrations Table
    $query = "SELECT COUNT(*) as total_game_registrations FROM game_registrations";
    $stmt = $conn_admin->query($query);
    $data['total_game_registrations'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as game_volleyball FROM game_registrations WHERE selected_games = 'Volleyball'";
    $stmt = $conn_admin->query($query);
    $data['game_volleyball'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as game_cricket FROM game_registrations WHERE selected_games = 'Cricket'";
    $stmt = $conn_admin->query($query);
    $data['game_cricket'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as game_khokho FROM game_registrations WHERE selected_games = 'Khokho'";
    $stmt = $conn_admin->query($query);
    $data['game_khokho'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as game_chess FROM game_registrations WHERE selected_games = 'Chess'";
    $stmt = $conn_admin->query($query);
    $data['game_chess'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as game_badminton FROM game_registrations WHERE selected_games = 'Badminton'";
    $stmt = $conn_admin->query($query);
    $data['game_badminton'] = $stmt->fetchColumn();
    $query = "SELECT COUNT(*) as game_tabletennis FROM game_registrations WHERE selected_games = 'Tennis'";
    $stmt = $conn_admin->query($query);
    $data['game_tabletennis'] = $stmt->fetchColumn();

    // teams Table
    $query = "SELECT COUNT(*) as total_teams FROM teams";
    $stmt = $conn_admin->query($query);
    $data['total_teams'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as team_volleyball FROM teams WHERE sport = 'Volleyball'";
    $stmt = $conn_admin->query($query);
    $data['team_volleyball'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as team_cricket FROM teams WHERE sport = 'Cricket'";
    $stmt = $conn_admin->query($query);
    $data['team_cricket'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as team_khokho FROM teams WHERE sport = 'Khokho'";
    $stmt = $conn_admin->query($query);
    $data['team_khokho'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as team_chess FROM teams WHERE sport = 'Chess'";
    $stmt = $conn_admin->query($query);
    $data['team_chess'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as team_badminton FROM teams WHERE sport = 'Badminton'";
    $stmt = $conn_admin->query($query);
    $data['team_badminton'] = $stmt->fetchColumn();
    $query = "SELECT COUNT(*) as team_tabletennis FROM teams WHERE sport = 'Tennis'";
    $stmt = $conn_admin->query($query);
    $data['team_tabletennis'] = $stmt->fetchColumn();

    // News Table
    $query = "SELECT COUNT(*) as total_news FROM news";
    $stmt = $conn_admin->query($query);
    $data['total_news'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as pending_news FROM news WHERE status = 'pending'";
    $stmt = $conn_admin->query($query);
    $data['pending_news'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as approved_news FROM news WHERE status = 'approved'";
    $stmt = $conn_admin->query($query);
    $data['approved_news'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as rejected_news FROM news WHERE status = 'rejected'";
    $stmt = $conn_admin->query($query);
    $data['rejected_news'] = $stmt->fetchColumn();

    // Images Table
    $query = "SELECT COUNT(*) as total_images FROM images";
    $stmt = $conn_admin->query($query);
    $data['total_images'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as pending_images FROM images WHERE status = 'pending'";
    $stmt = $conn_admin->query($query);
    $data['pending_images'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as approved_images FROM images WHERE status = 'approved'";
    $stmt = $conn_admin->query($query);
    $data['approved_images'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as rejected_images FROM images WHERE status = 'rejected'";
    $stmt = $conn_admin->query($query);
    $data['rejected_images'] = $stmt->fetchColumn();

    // Videos Table
    $query = "SELECT COUNT(*) as total_videos FROM videos";
    $stmt = $conn_admin->query($query);
    $data['total_videos'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as pending_videos FROM videos WHERE status = 'pending'";
    $stmt = $conn_admin->query($query);
    $data['pending_videos'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as approved_videos FROM videos WHERE status = 'approved'";
    $stmt = $conn_admin->query($query);
    $data['approved_videos'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as rejected_videos FROM videos WHERE status = 'rejected'";
    $stmt = $conn_admin->query($query);
    $data['rejected_videos'] = $stmt->fetchColumn();

    // Blogs Table
    $query = "SELECT COUNT(*) as total_blogs FROM blogs";
    $stmt = $conn_admin->query($query);
    $data['total_blogs'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as pending_blogs FROM blogs WHERE status = 'pending'";
    $stmt = $conn_admin->query($query);
    $data['pending_blogs'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as approved_blogs FROM blogs WHERE status = 'approved'";
    $stmt = $conn_admin->query($query);
    $data['approved_blogs'] = $stmt->fetchColumn();

    $query = "SELECT COUNT(*) as rejected_blogs FROM blogs WHERE status = 'rejected'";
    $stmt = $conn_admin->query($query);
    $data['rejected_blogs'] = $stmt->fetchColumn();

    // Add more queries for other tables as needed

    return $data;

}

// Fetch statistical data
$statisticalData = getStatisticalData($conn_admin);
ob_end_flush(); // End output buffering and send output to the browser
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<section class="admin-dashboard-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Welcome, Admin!</h2>
        <p class="text-center">This is your dashboard. Manage the website from here.</p>

        <div class="row">
            

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Fixtures</h5>
                        <p class="card-text">Total : <?php echo $statisticalData['total_fixtures']; ?></p>
                        <p class="card-text">Volleyball : <?php echo $statisticalData['fix_volleyball']; ?></p>
                        <p class="card-text">Cricket: <?php echo $statisticalData['fix_cricket']; ?></p>
                        <p class="card-text">Kho-Kho: <?php echo $statisticalData['fix_khokho']; ?></p>
                        <p class="card-text">Chess: <?php echo $statisticalData['fix_chess']; ?></p>
                        <p class="card-text">Badminton: <?php echo $statisticalData['fix_badminton']; ?></p>
                        <p class="card-text">Table-Tennis: <?php echo $statisticalData['fix_tabletennis']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Game Registrations</h5>
                        <p class="card-text">Total : <?php echo $statisticalData['total_game_registrations']; ?></p>
                        <p class="card-text">Volleyball : <?php echo $statisticalData['game_volleyball']; ?></p>
                        <p class="card-text">Cricket: <?php echo $statisticalData['game_cricket']; ?></p>
                        <p class="card-text">Kho-Kho: <?php echo $statisticalData['game_khokho']; ?></p>
                        <p class="card-text">Chess: <?php echo $statisticalData['game_chess']; ?></p>
                        <p class="card-text">Badminton: <?php echo $statisticalData['game_badminton']; ?></p>
                        <p class="card-text">Table-Tennis: <?php echo $statisticalData['game_tabletennis']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">teams</h5>
                        <p class="card-text">Total : <?php echo $statisticalData['total_teams']; ?></p>
                        <p class="card-text">Volleyball : <?php echo $statisticalData['team_volleyball']; ?></p>
                        <p class="card-text">Cricket: <?php echo $statisticalData['team_cricket']; ?></p>
                        <p class="card-text">Kho-Kho: <?php echo $statisticalData['team_khokho']; ?></p>
                        <p class="card-text">Chess: <?php echo $statisticalData['team_chess']; ?></p>
                        <p class="card-text">Badminton: <?php echo $statisticalData['team_badminton']; ?></p>
                        <p class="card-text">Table-Tennis: <?php echo $statisticalData['team_tabletennis']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Images</h5>
                        <p class="card-text">Total Images: <?php echo $statisticalData['total_images']; ?></p>
                        <p class="card-text">Pending Images: <?php echo $statisticalData['pending_images']; ?></p>
                        <p class="card-text">Approved Images: <?php echo $statisticalData['approved_images']; ?></p>
                        <p class="card-text">Rejected Images: <?php echo $statisticalData['rejected_images']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">News</h5>
                        <p class="card-text">Total News: <?php echo $statisticalData['total_news']; ?></p>
                        <p class="card-text">Pending News: <?php echo $statisticalData['pending_news']; ?></p>
                        <p class="card-text">Approved News: <?php echo $statisticalData['approved_news']; ?></p>
                        <p class="card-text">Rejected News: <?php echo $statisticalData['rejected_news']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Videos</h5>
                        <p class="card-text">Total Videos: <?php echo $statisticalData['total_videos']; ?></p>
                        <p class="card-text">Pending Videos: <?php echo $statisticalData['pending_videos']; ?></p>
                        <p class="card-text">Approved Videos: <?php echo $statisticalData['approved_videos']; ?></p>
                        <p class="card-text">Rejected Videos: <?php echo $statisticalData['rejected_videos']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Blogs</h5>
                        <p class="card-text">Total Blogs: <?php echo $statisticalData['total_blogs']; ?></p>
                        <p class="card-text">Pending Blogs: <?php echo $statisticalData['pending_blogs']; ?></p>
                        <p class="card-text">Approved Blogs: <?php echo $statisticalData['approved_blogs']; ?></p>
                        <p class="card-text">Rejected Blogs: <?php echo $statisticalData['rejected_blogs']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text">Total : <?php echo $statisticalData['total_users']; ?></p>
                        <p class="card-text">First Year : <?php echo $statisticalData['first_year_users']; ?></p>
                        <p class="card-text">Second Year: <?php echo $statisticalData['second_year_users']; ?></p>
                        <p class="card-text">Third Year: <?php echo $statisticalData['third_year_users']; ?></p>
                        <p class="card-text">Fourth Year: <?php echo $statisticalData['fourth_year_users']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="manage-users-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Manage Users</h2>

            <!-- Users Table -->
            <table id="usersTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roll No.</th>
                        <th>Course</th>
                        <th>Branch</th>
                        <th>Year</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><?= $user['roll_no'] ?></td>
                        <td><?= $user['course'] ?></td>
                        <td><?= $user['branch'] ?></td>
                        <td><?= $user['year'] ?></td>
                        <td><?= $user['mobile'] ?></td>
                        <td>
                            <a href="dashboard.php?edit=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="dashboard.php?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    
<!-- Edit User Form -->
<?php if ($edit_user): ?>
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= $edit_user['name'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $edit_user['email'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="roll_no" class="form-label">Roll No.</label>
                            <input type="text" class="form-control" id="roll_no" name="roll_no" value="<?= $edit_user['roll_no'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="course" class="form-label">Course</label>
                            <input type="text" class="form-control" id="course" name="course" value="<?= $edit_user['course'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="branch" class="form-label">Branch</label>
                            <input type="text" class="form-control" id="branch" name="branch" value="<?= $edit_user['branch'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <input type="text" class="form-control" id="year" name="year" value="<?= $edit_user['year'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" class="form-control" id="mobile" name="mobile" value="<?= $edit_user['mobile'] ?>" required>
                        </div>
                        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to Open Modal Automatically -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        });
    </script>
<?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                paging: true, // Enable pagination
                searching: true, // Enable search
                ordering: true, // Enable sorting
                responsive: true, // Enable responsive design
                lengthMenu: [10, 25, 50, 100], // Set page length options
                pageLength: 10, // Default page length
                order: [[0, 'asc']] // Default sorting by ID in ascending order
            });
        });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>