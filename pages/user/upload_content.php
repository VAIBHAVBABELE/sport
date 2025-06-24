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
            $stmt = $conn_user->prepare("INSERT INTO $type (user_id, title, content, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $title, $content]);
        } elseif ($type === 'images') {
            $stmt = $conn_user->prepare("INSERT INTO $type (user_id, title, image_path, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $title, $filePath]);
        } elseif ($type === 'videos') {
            $stmt = $conn_user->prepare("INSERT INTO $type (user_id, title, video_path, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $title, $filePath]);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Content</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Animate.css for Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
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

        /* Background and General Styling */
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            font-family: 'Arial', sans-serif;
        }

        .upload-content-section {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 800px;
        }

        .upload-content-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        /* Form Styling */
        .upload-content-section .form-control {
            border-radius: 25px;
            border: 1px solid #ddd;
            
            transition: all 0.3s ease;
        }

        .upload-content-section .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
        }

        .upload-content-section .form-label {
            font-weight: 600;
            color: #333;
        }

        .upload-content-section .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .upload-content-section .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-title {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
        }

        .modal-footer .btn-secondary {
            border-radius: 25px;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <section class="description-section">
        <h3>Share Your Content</h3>
        <p>
            Share your creativity with the community by uploading blogs, images, videos, or 
            news articles. Whether it’s a game highlight, a personal story, or a creative project, your content 
            can inspire and engage others. Ensure your content adheres to the guidelines and is appropriate for 
            all audiences. Once uploaded, your content will be reviewed and made available for others to view and 
            enjoy. Don’t miss this opportunity to showcase your talent and contribute to the vibrant culture of
             our platform. Let’s make your voice heard and your creativity shine!
        </p>
    </section>
    <section class="upload-content-section py-5">
        <div class="container">
            <h2>Upload Content</h2>
            
            <form id="uploadForm" action="upload_content.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control" id="type" name="type" required onchange="toggleFields()">
                        <option value="blogs">Blog</option>
                        <option value="images">Image</option>
                        <option value="videos">Video</option>
                        <option value="news">News</option>
                    </select>
                    <small class="form-text">Select any one option (blog,image,news,videos) </small>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                    <small class="form-text">Enter a valid Title </small>
                </div>
                <div class="mb-3" id="contentField">
                    <label for="content" class="form-label">Content *</label>
                    <textarea class="form-control" id="content" name="content"></textarea>
                    <small class="form-text">Describe it .</small>
                </div>
                <div class="mb-3" id="fileField">
                    <label for="file" class="form-label">File *</label>
                    <input type="file" class="form-control" id="file" name="file">
                    <small class="form-text">Upload content with max (2MB)</small>
                </div>
                
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#confirmationModal">Upload</button>
                <small class="form-text">Confirm all the details , after submission not edit.</small>
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

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
</body>
</html>

<?php include '../../includes/footer.php'; ?>