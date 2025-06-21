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

// Handle content upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $content = $_POST['content'] ?? ''; // Optional for images and videos
    $file = $_FILES['file'];

    if (empty($type) || empty($title)) {
        $_SESSION['error'] = "Title and type are required.";
        header("Location: upload_content.php");
        exit();
    }

    // Validate file format based on type
    $allowedImageFormats = ['image/jpeg', 'image/png', 'image/gif'];
    $allowedVideoFormats = ['video/mp4', 'video/avi'];
    $fileFormat = $file['type'];

    if ($type === 'images' && !in_array($fileFormat, $allowedImageFormats)) {
        $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed.";
        header("Location: upload_content.php");
        exit();
    }

    if ($type === 'videos' && !in_array($fileFormat, $allowedVideoFormats)) {
        $_SESSION['error'] = "Only MP4 and AVI videos are allowed.";
        header("Location: upload_content.php");
        exit();
    }

    // Handle file upload
    $filePath = '';
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/' . $type . 's/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }
        $filePath = 'assets/' . $type . 's/' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], '../../' . $filePath);
    }

    // Insert into the database
    try {
        if ($type === 'blogs' || $type === 'news') {
            $stmt = $conn_admin->prepare("INSERT INTO $type (user_id, title, content, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['admin_id'], $title, $content]);
        } elseif ($type === 'images') {
            $stmt = $conn_admin->prepare("INSERT INTO $type (user_id, title, image_path, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['admin_id'], $title, $filePath]);
        } elseif ($type === 'videos') {
            $stmt = $conn_admin->prepare("INSERT INTO $type (user_id, title, video_path, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['admin_id'], $title, $filePath]);
        }

        $_SESSION['success'] = ucfirst($type) . " uploaded successfully!";
        header("Location: upload_content.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: upload_content.php");
        exit();
    }
}
ob_end_flush(); // End output buffering and send output to the browser
?>

<section class="upload-content-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Upload Content</h2>
        <form id="uploadForm" action="upload_content.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <select class="form-control" id="type" name="type" required onchange="toggleFields()">
                    <option value="blogs">Blog</option>
                    <option value="images">Image</option>
                    <option value="videos">Video</option>
                    <option value="news">News</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3" id="contentField">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content"></textarea>
            </div>
            <div class="mb-3" id="fileField">
                <label for="file" class="form-label">File</label>
                <input type="file" class="form-control" id="file" name="file">
            </div>
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#confirmationModal">Upload</button>
        </form>
    </div>
</section>

<!-- Bootstrap Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to upload this content?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUploadBtn">Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Toggle Fields and Handle Confirmation -->
<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    const contentField = document.getElementById('contentField');
    const fileField = document.getElementById('fileField');

    // Show/hide fields based on the selected type
    if (type === 'blogs' || type === 'news') {
        contentField.style.display = 'block';
        fileField.style.display = 'none';
    } else if (type === 'images' || type === 'videos') {
        contentField.style.display = 'none';
        fileField.style.display = 'block';
    }
}

// Initialize fields on page load
document.addEventListener('DOMContentLoaded', toggleFields);

// Handle confirmation modal
document.getElementById('confirmUploadBtn').addEventListener('click', function () {
    document.getElementById('uploadForm').submit();
});
</script>

<?php include '../../includes/footer.php'; ?>