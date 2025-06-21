<?php
session_start();
include 'includes/db_user.php';
include 'includes/functions.php';

displayAlert();

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate mobile number (10 digits)
function validateMobile($mobile) {
    return preg_match('/^\d{10}$/', $mobile);
}

// Function to validate password (minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, and 1 special character)
function validatePassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $roll_no = trim($_POST['roll_no']);
    $course = trim($_POST['course']);
    $branch = trim($_POST['branch']);
    $year = trim($_POST['year']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($roll_no)) {
        $errors[] = "Roll No. is required.";
    }
    if (empty($course)) {
        $errors[] = "Course is required.";
    }
    if (empty($branch)) {
        $errors[] = "Branch is required.";
    }
    if (empty($year)) {
        $errors[] = "Year is required.";
    }
    if (!validateMobile($mobile)) {
        $errors[] = "Mobile number must be 10 digits.";
    }
    if (!validateEmail($email)) {
        $errors[] = "Invalid email address.";
    }
    if (!validatePassword($password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert user into the database
            $sql = "INSERT INTO users (name, roll_no, course, branch, year, mobile, email, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn_user->prepare($sql);
            $stmt->execute([$name, $roll_no, $course, $branch, $year, $mobile, $email, $password_hash]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header('Location: login_user.php');
                exit();
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
                header('Location: register_user.php');
                exit();
            }
        } catch (PDOException $e) {
            // Check for duplicate entry error (SQLSTATE[23000]: Integrity constraint violation)
            if ($e->getCode() === '23000') {
                $_SESSION['error'] = "Duplicate entry: Email or Roll No. already exists.";
            } else {
                $_SESSION['error'] = "Database error: " . $e->getMessage();
            }
            header('Location: register_user.php');
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: register_user.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Background Gradient for Registration Page */
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            min-height: 100vh;
        }

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


        .register-section {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 800px;
        }

        .register-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-section .form-control {
            border-radius: 25px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .register-section .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
        }

        .register-section .form-label {
            font-weight: 600;
            color: #333;
        }

        .register-section .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .register-section .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .register-section .alert {
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .register-section .text-center a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 500;
        }

        .register-section .text-center a:hover {
            text-decoration: underline;
        }

        .register-section .form-text {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php displayAlert(); ?>

    <section class="description-section">
            <h3> User Registration guide</h3>
            <p>
            Please fill out the form carefully, as some details (*) cannot be changed after 
            registration. Ensure that you provide a valid email, mobile number, and roll number, 
            as these will be used for verification and communication. All fields marked with (*) 
            are required. Double-check your information before submitting to avoid errors. Thank you 
            for your cooperation!
            </p>
        </section>

    <!-- Registration Form -->
    <section class="register-section">
        <div class="container">
            <p>This form for all students of NITRA Technical Campus (NTC), not only for participating students.This is not a game registration form.it is user registration form for use this website.</p>
            <h2>User Registration</h2>
            <form action="" method="POST">
                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="eg. Ramesh Kumar" required>
                    <small class="form-text">Enter your full name.</small>
                </div>

                <!-- Roll No. -->
                <div class="mb-3">
                    <label for="roll_no" class="form-label">Roll No. *</label>
                    <input type="number" class="form-control" id="roll_no" name="roll_no" placeholder="eg.2208020100036" required>
                    <small class="form-text">Enter your university roll number.</small>
                </div>

                <!-- Course -->
                <div class="mb-3">
                    <label for="course" class="form-label">Course *</label>
                    <select class="form-control" id="course" name="course" required>
                        <option value="B.Tech">B.Tech</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Other">Other</option>
                    </select>
                    <small class="form-text">Select your course.</small>
                </div>

                <!-- Branch -->
                <div class="mb-3">
                    <label for="branch" class="form-label">Branch *</label>
                    <select class="form-control" id="branch" name="branch" required>
                        <option value="CSE">CSE</option>
                        <option value="AIML">AIML</option>
                        <option value="TT">TT</option>
                        <option value="Other">Other</option>
                    </select>
                    <small class="form-text">Select your branch.</small>
                </div>

                <!-- Year -->
                <div class="mb-3">
                    <label for="year" class="form-label">Year *</label>
                    <select class="form-control" id="year" name="year" required>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="Other">Other</option>
                    </select>
                    <small class="form-text">Select your current year.</small>
                </div>

                <!-- Mobile No. -->
                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile No. *</label>
                    <input type="number" class="form-control" id="mobile" name="mobile" placeholder="eg.8417054915" required>
                    <small class="form-text">Enter a 10-digit mobile number.</small>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="eg.nitra@gmail.com" required autocomplete="email">
                    <small class="form-text">Your email will be auto-filled, logged in with Google.</small>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text">
                        Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.
                    </small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">
                Already have an account? <a href="login_user.php">Login</a>
            </p>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>