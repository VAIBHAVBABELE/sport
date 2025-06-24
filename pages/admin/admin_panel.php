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

// Handle form submission for creating a new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $notice = $_POST['notice'];
    $stmt = $conn_admin->prepare("INSERT INTO announcements (notice) VALUES (:notice)");
    $stmt->execute([':notice' => $notice]);

    header("Location: admin_panel.php");
    exit();
}

// Handle form submission for updating an announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_announcement'])) {
    $id = $_POST['id'];
    $notice = $_POST['notice'];
    $stmt = $conn_admin->prepare("UPDATE announcements SET notice = :notice WHERE id = :id");
    $stmt->execute([':id' => $id, ':notice' => $notice]);

    header("Location: admin_panel.php");
    exit();
}

// Handle deletion of an announcement
if (isset($_GET['delete_announcement_id'])) {
    $id = $_GET['delete_announcement_id'];
    $stmt = $conn_admin->prepare("DELETE FROM announcements WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: admin_panel.php");
    exit();
}

// Handle form submission for creating a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];
    $stmt = $conn_admin->prepare("INSERT INTO events (event_name, event_date, event_description) VALUES (:event_name, :event_date, :event_description)");
    $stmt->execute([
        ':event_name' => $event_name,
        ':event_date' => $event_date,
        ':event_description' => $event_description
    ]);

    header("Location: admin_panel.php");
    exit();
}

// Handle form submission for updating an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_event'])) {
    $id = $_POST['id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];
    $stmt = $conn_admin->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_description = :event_description WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':event_name' => $event_name,
        ':event_date' => $event_date,
        ':event_description' => $event_description
    ]);

    header("Location: admin_panel.php");
    exit();
}

// Handle deletion of an event
if (isset($_GET['delete_event_id'])) {
    $id = $_GET['delete_event_id'];
    $stmt = $conn_admin->prepare("DELETE FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: admin_panel.php");
    exit();
}

// Fetch all announcements
$announcements = $conn_admin->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all events
$events = $conn_admin->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush(); // End output buffering and send output to the browser
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <section class="admin-panel-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Admin Panel</h2>

            <!-- Announcements Table -->
            <h3>Announcements</h3>
            <table id="announcementsTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Notice</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?= $announcement['id'] ?></td>
                        <td><?= $announcement['notice'] ?></td>
                        <td><?= $announcement['created_at'] ?></td>
                        <td>
                            <a href="?edit_announcement_id=<?= $announcement['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_announcement_id=<?= $announcement['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this announcement?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Form for creating/updating an announcement -->
            <form method="POST" action="">
                <h3><?= isset($_GET['edit_announcement_id']) ? 'Edit Announcement' : 'Create New Announcement' ?></h3>
                <?php if (isset($_GET['edit_announcement_id'])): ?>
                    <?php
                    $edit_announcement_id = $_GET['edit_announcement_id'];
                    $stmt = $conn_admin->prepare("SELECT * FROM announcements WHERE id = :id");
                    $stmt->execute([':id' => $edit_announcement_id]);
                    $edit_announcement = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <input type="hidden" name="id" value="<?= $edit_announcement['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="notice">Notice</label>
                    <textarea class="form-control" id="notice" name="notice" required><?= $edit_announcement['notice'] ?? '' ?></textarea>
                </div>
                <button type="submit" name="<?= isset($_GET['edit_announcement_id']) ? 'edit_announcement' : 'add_announcement' ?>" class="btn btn-primary">
                    <?= isset($_GET['edit_announcement_id']) ? 'Update Announcement' : 'Create Announcement' ?>
                </button>
                <?php if (isset($_GET['edit_announcement_id'])): ?>
                    <a href="admin_panel.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>

            <!-- Events Table -->
            <h3 class="mt-5">Events</h3>
            <table id="eventsTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Event Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= $event['id'] ?></td>
                        <td><?= $event['event_name'] ?></td>
                        <td><?= $event['event_date'] ?></td>
                        <td><?= $event['event_description'] ?></td>
                        <td>
                            <a href="?edit_event_id=<?= $event['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_event_id=<?= $event['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Form for creating/updating an event -->
            <form method="POST" action="">
                <h3><?= isset($_GET['edit_event_id']) ? 'Edit Event' : 'Create New Event' ?></h3>
                <?php if (isset($_GET['edit_event_id'])): ?>
                    <?php
                    $edit_event_id = $_GET['edit_event_id'];
                    $stmt = $conn_admin->prepare("SELECT * FROM events WHERE id = :id");
                    $stmt->execute([':id' => $edit_event_id]);
                    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="event_name">Event Name</label>
                    <input type="text" class="form-control" id="event_name" name="event_name" value="<?= $edit_event['event_name'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" class="form-control" id="event_date" name="event_date" value="<?= $edit_event['event_date'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="event_description">Event Description</label>
                    <textarea class="form-control" id="event_description" name="event_description" required><?= $edit_event['event_description'] ?? '' ?></textarea>
                </div>
                <button type="submit" name="<?= isset($_GET['edit_event_id']) ? 'edit_event' : 'add_event' ?>" class="btn btn-primary">
                    <?= isset($_GET['edit_event_id']) ? 'Update Event' : 'Create Event' ?>
                </button>
                <?php if (isset($_GET['edit_event_id'])): ?>
                    <a href="admin_panel.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#announcementsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                responsive: true
            });

            $('#eventsTable').DataTable({
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