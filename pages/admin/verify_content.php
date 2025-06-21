<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_admin.php'; // Use the admin header
include '../../includes/functions.php';
include '../../includes/db_admin.php'; // Include admin database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login_admin.php");
    exit();
}

// Handle form submissions (approve, delete, edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($action && $type && $id) {
        try {
            if ($action === 'approve') {
                // Approve content
                $stmt = $conn_admin->prepare("UPDATE $type SET status = 'approved' WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = ucfirst($type) . " approved successfully!";
            } elseif ($action === 'delete') {
                // Delete content
                $stmt = $conn_admin->prepare("DELETE FROM $type WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = ucfirst($type) . " deleted successfully!";
            } elseif ($action === 'edit') {
                // Handle edit form submission
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $image_path = $_POST['image_path'] ?? '';
                $video_path = $_POST['video_path'] ?? '';

                if ($type === 'blogs' || $type === 'news') {
                    $stmt = $conn_admin->prepare("UPDATE $type SET title = ?, content = ? WHERE id = ?");
                    $stmt->execute([$title, $content, $id]);
                } elseif ($type === 'images') {
                    $stmt = $conn_admin->prepare("UPDATE $type SET image_path = ? WHERE id = ?");
                    $stmt->execute([$image_path, $id]);
                } elseif ($type === 'videos') {
                    $stmt = $conn_admin->prepare("UPDATE $type SET video_path = ? WHERE id = ?");
                    $stmt->execute([$video_path, $id]);
                }
                $_SESSION['success'] = ucfirst($type) . " updated successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Invalid request.";
    }
    header("Location: verify_content.php");
    exit();
}

// Fetch pending blogs, images, videos, and news with user details
try {
    $blogs = $conn_admin->query("
        SELECT blogs.*, users.name, users.roll_no 
        FROM blogs 
        JOIN users ON blogs.user_id = users.id 
        WHERE blogs.status = 'pending'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $images = $conn_admin->query("
        SELECT images.*, users.name, users.roll_no 
        FROM images 
        JOIN users ON images.user_id = users.id 
        WHERE images.status = 'pending'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $videos = $conn_admin->query("
        SELECT videos.*, users.name, users.roll_no 
        FROM videos 
        JOIN users ON videos.user_id = users.id 
        WHERE videos.status = 'pending'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $news = $conn_admin->query("
        SELECT news.*, users.name, users.roll_no 
        FROM news 
        JOIN users ON news.user_id = users.id 
        WHERE news.status = 'pending'
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Fetch approved blogs, images, videos, and news with user details
try {
    $appBlogs = $conn_admin->query("
        SELECT blogs.*, users.name, users.roll_no 
        FROM blogs 
        JOIN users ON blogs.user_id = users.id 
        WHERE blogs.status = 'approved'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $appImages = $conn_admin->query("
        SELECT images.*, users.name, users.roll_no 
        FROM images 
        JOIN users ON images.user_id = users.id 
        WHERE images.status = 'approved'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $appVideos = $conn_admin->query("
        SELECT videos.*, users.name, users.roll_no 
        FROM videos 
        JOIN users ON videos.user_id = users.id 
        WHERE videos.status = 'approved'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $appNews = $conn_admin->query("
        SELECT news.*, users.name, users.roll_no 
        FROM news 
        JOIN users ON news.user_id = users.id 
        WHERE news.status = 'approved'
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Handle edit form display
$editData = null;
if (isset($_GET['edit'])) {
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';

    if ($type && $id) {
        try {
            $stmt = $conn_admin->prepare("SELECT * FROM $type WHERE id = ?");
            $stmt->execute([$id]);
            $editData = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
}
ob_end_flush(); // End output buffering and send output to the browser
?>

<section class="verify-content-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Verify Content</h2>
        <p class="text-center">Verify blogs, images, videos, and news submitted by users.</p>

        <!-- Display Edit Form if in Edit Mode -->
        <?php if ($editData): ?>
        <div class="edit-form mb-5">
            <h3>Edit <?= ucfirst($_GET['type']) ?></h3>
            <form method="POST" action="verify_content.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="type" value="<?= htmlspecialchars($_GET['type']) ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">

                <?php if ($_GET['type'] === 'blogs' || $_GET['type'] === 'news'): ?>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($editData['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required><?= htmlspecialchars($editData['content']) ?></textarea>
                </div>
                <?php elseif ($_GET['type'] === 'images'): ?>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($editData['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="image_path" class="form-label">Image Path</label>
                    <input type="text" class="form-control" id="image_path" name="image_path" value="<?= htmlspecialchars($editData['image_path']) ?>" required>
                </div>
                <?php elseif ($_GET['type'] === 'videos'): ?>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($editData['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="video_path" class="form-label">Video Path</label>
                    <input type="text" class="form-control" id="video_path" name="video_path" value="<?= htmlspecialchars($editData['video_path']) ?>" required>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="verify_content.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- Blogs Section -->
        <h3>Blogs</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                    <tr>
                        <td><?= htmlspecialchars($blog['title']) ?></td>
                        <td><?= htmlspecialchars(substr($blog['content'], 0, 100)) . '...' ?></td>
                        <td><?= htmlspecialchars($blog['name']) ?></td>
                        <td><?= htmlspecialchars($blog['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="type" value="blogs">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="blogs">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=blogs&id=<?= $blog['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- News Section -->
        <h3>News</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $news_item): ?>
                    <tr>
                        <td><?= htmlspecialchars($news_item['title']) ?></td>
                        <td><?= htmlspecialchars(substr($news_item['content'], 0, 100)) . '...' ?></td>
                        <td><?= htmlspecialchars($news_item['name']) ?></td>
                        <td><?= htmlspecialchars($news_item['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="type" value="news">
                                <input type="hidden" name="id" value="<?= $news_item['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="news">
                                <input type="hidden" name="id" value="<?= $news_item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this news?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=news&id=<?= $news_item['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Images Section -->
        <h3>Images</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $image): ?>
                    <tr>
                        <td><?= htmlspecialchars($image['title']) ?></td>
                        <td><img src="../../<?= htmlspecialchars($image['image_path']) ?>" class="img-fluid" style="max-width: 100px;"></td>
                        <td><?= htmlspecialchars($image['name']) ?></td>
                        <td><?= htmlspecialchars($image['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="type" value="images">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="images">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=images&id=<?= $image['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Videos Section -->
        <h3>Videos</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Video</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <video controls style="max-width: 200px;">
                                <source src="../../<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </td>
                        <td><?= htmlspecialchars($video['name']) ?></td>
                        <td><?= htmlspecialchars($video['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="type" value="videos">
                                <input type="hidden" name="id" value="<?= $video['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="videos">
                                <input type="hidden" name="id" value="<?= $video['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this video?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=videos&id=<?= $video['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Manage Content Section -->
<section class="manage-content-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Manage Content</h2>
        <p class="text-center">Manage approved blogs, images, videos, and news submitted by users.</p>

        <!-- Approved Blogs Section -->
        <h3>Blogs</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appBlogs as $blog): ?>
                    <tr>
                        <td><?= htmlspecialchars($blog['title']) ?></td>
                        <td><?= htmlspecialchars(substr($blog['content'], 0, 100)) . '...' ?></td>
                        <td><?= htmlspecialchars($blog['name']) ?></td>
                        <td><?= htmlspecialchars($blog['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="blogs">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=blogs&id=<?= $blog['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Approved News Section -->
        <h3>News</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appNews as $news_item): ?>
                    <tr>
                        <td><?= htmlspecialchars($news_item['title']) ?></td>
                        <td><?= htmlspecialchars(substr($news_item['content'], 0, 100)) . '...' ?></td>
                        <td><?= htmlspecialchars($news_item['name']) ?></td>
                        <td><?= htmlspecialchars($news_item['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="news">
                                <input type="hidden" name="id" value="<?= $news_item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this news?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=news&id=<?= $news_item['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Approved Images Section -->
        <h3>Images</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appImages as $image): ?>
                    <tr>
                        <td><?= htmlspecialchars($image['title']) ?></td>
                        <td><img src="../../<?= htmlspecialchars($image['image_path']) ?>" class="img-fluid" style="max-width: 100px;"></td>
                        <td><?= htmlspecialchars($image['name']) ?></td>
                        <td><?= htmlspecialchars($image['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="images">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=images&id=<?= $image['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Approved Videos Section -->
        <h3>Videos</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Video</th>
                        <th>Uploaded By</th>
                        <th>Roll No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appVideos as $video): ?>
                    <tr>
                        <td><?= htmlspecialchars($video['title']) ?></td>
                        <td>
                            <video controls style="max-width: 200px;">
                                <source src="../../<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </td>
                        <td><?= htmlspecialchars($video['name']) ?></td>
                        <td><?= htmlspecialchars($video['roll_no']) ?></td>
                        <td>
                            <form method="POST" action="verify_content.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="videos">
                                <input type="hidden" name="id" value="<?= $video['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this video?')">Delete</button>
                            </form>
                            <a href="verify_content.php?edit=true&type=videos&id=<?= $video['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>