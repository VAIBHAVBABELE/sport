<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';

// Check if the user is logged in
if (!isLoggedIn()) {
    header("Location: ../../login_user.php");
    exit();
}

displayAlert();

// Fetch user details
$stmt = $conn_user->prepare("SELECT * FROM users WHERE id = ?");
if ($stmt->execute([$_SESSION['user_id']])) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("User not found in the database."); // Debugging
    }
} else {
    die("Database query failed."); // Debugging
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $course = $_POST['course'];
    $branch = $_POST['branch'];
    $year = $_POST['year'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $profile_image = $_FILES['profile_image'];

    // Validate inputs
    if (empty($name) || empty($roll_no) || empty($course) || empty($branch) || empty($year) || empty($mobile) || empty($email)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: profile.php");
        exit();
    }

    // Validate roll number (13 digits)
    if (!preg_match('/^\d{13}$/', $roll_no)) {
        $_SESSION['error'] = "Roll number must be exactly 13 digits.";
        header("Location: profile.php");
        exit();
    }

    // Validate mobile number (10 digits)
    if (!preg_match('/^\d{10}$/', $mobile)) {
        $_SESSION['error'] = "Mobile number must be exactly 10 digits.";
        header("Location: profile.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: profile.php");
        exit();
    }

    // Handle file upload
    $profile_image_path = $user['profile_image'];
    if ($profile_image['error'] === UPLOAD_ERR_OK) {
        // Check file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (!in_array($profile_image['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPEG, PNG, and GIF images are allowed.";
            header("Location: profile.php");
            exit();
        }
        if ($profile_image['size'] > $max_size) {
            $_SESSION['error'] = "Image size must be less than 2MB.";
            header("Location: profile.php");
            exit();
        }
        // Save the file
        $upload_dir = '../../assets/images/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
        }
        $profile_image_path = 'assets/images/profiles/' . uniqid() . '_' . basename($profile_image['name']);
        move_uploaded_file($profile_image['tmp_name'], '../../' . $profile_image_path);
    }

    // Update the profile in the database
    try {
        $stmt = $conn_user->prepare("UPDATE users SET name = ?, roll_no = ?, course = ?, branch = ?, year = ?, mobile = ?, email = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$name, $roll_no, $course, $branch, $year, $mobile, $email, $profile_image_path, $_SESSION['user_id']]);
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
        header("Location: profile.php");
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
    <title>Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Animate.css for Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        /* Background and General Styling */
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            font-family: 'Arial', sans-serif;
        }

            /* Description Section Styling */
        .description-section {
            background: linear-gradient(45deg, #ff7675, #d63031);
            color: white;
            padding: 2rem;
            margin: 2rem;
            border-radius: 15px;
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

        .profile-section {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 800px;
        }

        .profile-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        /* Form Styling */
        .profile-section .form-control {
            border-radius: 25px;
            border: 1px solid #ddd;
            
            transition: all 0.3s ease;
        }

        .profile-section .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
        }

        .profile-section .form-label {
            font-weight: 600;
            color: #333;
        }

        .profile-section .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-section .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-section img {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        <h3>Welcome to Your Profile</h3>
            <p>
                    Welcome to your profile page! Here, you can update and manage your personal information, 
                    including your name, roll number, course, branch, year, mobile number, email, and profile image.
                     Keeping your details accurate and up-to-date ensures a seamless experience on the platform. 
                     Whether you're participating in games, uploading content, or engaging with the community, 
                     your profile is the foundation of your presence here. Take a moment to review your information
                      and make any necessary changes. If you have any questions or need assistance, feel free to 
                      reach out to the support team. Let’s make your profile shine and keep it ready for all 
                      the exciting opportunities ahead!
            </p>
        </section>
    <section class="profile-section py-5">
            
        <div class="container">
            <h2>Profile</h2>
            
            <form action="profile.php" method="POST" enctype="multipart/form-data" onsubmit="return confirmUpdate()">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="roll_no" class="form-label">Roll No.</label>
                    <input type="text" class="form-control" id="roll_no" name="roll_no" value="<?= htmlspecialchars($user['roll_no'] ?? '') ?>" readonly>
                    <small class="form-text text-muted">ReadOnly</small>
                </div>
                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-control" id="course" name="course" required>
                        <option value="BTech" <?= ($user['course'] ?? '') === 'BTech' ? 'selected' : '' ?>>BTech</option>
                        <option value="Diploma" <?= ($user['course'] ?? '') === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                        <option value="Other" <?= ($user['course'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="branch" class="form-label">Branch</label>
                    <select class="form-control" id="branch" name="branch" required>
                        <option value="CSE" <?= ($user['branch'] ?? '') === 'CSE' ? 'selected' : '' ?>>CSE</option>
                        <option value="AIML" <?= ($user['branch'] ?? '') === 'AIML' ? 'selected' : '' ?>>AIML</option>
                        <option value="TT" <?= ($user['branch'] ?? '') === 'TT' ? 'selected' : '' ?>>TT</option>
                        <option value="Other" <?= ($user['branch'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="year" class="form-label">Year</label>
                    <select class="form-control" id="year" name="year" required>
                        <option value="1" <?= ($user['year'] ?? '') == 1 ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= ($user['year'] ?? '') == 2 ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= ($user['year'] ?? '') == 3 ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= ($user['year'] ?? '') == 4 ? 'selected' : '' ?>>4</option>
                        <option value="Other" <?= ($user['year'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile No.</label>
                    <input type="text" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" readonly>
                    <small class="form-text text-muted">ReadOnly</small>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                    <small class="form-text text-muted">ReadOnly</small>
                </div>
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image">
                    <?php if (!empty($user['profile_image'])): ?>
                    <img src="../../<?= htmlspecialchars($user['profile_image']) ?>" class="img-fluid mt-2" alt="Profile Image" style="max-width: 200px;">
                    <?php endif; ?>
                    <small class="form-text text-muted">u can update it /Only JPEG, PNG, and GIF images are allowed (max 2MB).</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Profile</button>
            </form>
        </div>
    </section>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JavaScript -->
    <script>
        function confirmUpdate() {
            return confirm("Are you sure you want to update your profile?");
        }
    </script>
</body>
</html>

<?php
ob_end_flush(); // End output buffering and send output to the browser
include '../../includes/footer.php';
?>