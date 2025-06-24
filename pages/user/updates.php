<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/header_user.php';
include '../../includes/functions.php';
include '../../includes/db_user.php';

if (!isLoggedIn()) {
    header("Location: ../../login.php");
    exit();
}

displayAlert();

// Check if a specific blog or news item is requested
$view_type = $_GET['view'] ?? ''; // 'blog' or 'news'
$item_id = $_GET['id'] ?? 0;

// Fetch the full content if a specific item is requested
if ($view_type && $item_id) {
    $table = ($view_type === 'blog') ? 'blogs' : 'news';
    $stmt = $conn_user->prepare("SELECT * FROM $table WHERE id = ? AND status = 'approved'");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $_SESSION['error'] = ucfirst($view_type) . " not found or not approved.";
        header("Location: updates.php");
        exit();
    }

    // Display the full content
    ?>
    <section class="details-section py-5">
        <div class="container">
            <h2 class="text-center mb-4"><?= htmlspecialchars($item['title']) ?></h2>
            <div class="card">
                <div class="card-body">
                    <p class="card-text"><?= nl2br(htmlspecialchars($item['content'])) ?></p>
                    <a href="updates.php" class="btn btn-primary">Back to Updates</a>
                </div>
            </div>
        </div>
    </section>
    <?php
    include '../../includes/footer.php';
    exit(); // Stop further execution
}

// Fetch all approved blogs and news
$blogs = $conn_user->query("SELECT * FROM blogs WHERE status = 'approved'")->fetchAll(PDO::FETCH_ASSOC);
$news = $conn_user->query("SELECT * FROM news WHERE status = 'approved'")->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush(); // End output buffering and send output to the browser
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updates</title>
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

        .updates-section {
            padding: 3rem 0;
        }

        .updates-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        .updates-section h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            margin-bottom: 1.5rem;
            animation: fadeInLeft 1s ease;
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%; /* Ensure all cards have equal height */
            display: flex;
            flex-direction: column;
            background: linear-gradient(45deg, #ffffff, #f8f9fa);
            animation: fadeInUp 1s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            flex: 1;
            padding: 20px;
            border-radius: 15px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #6a11cb;
        }

        .card-text {
            color: #555;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Grid */
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .row {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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
        <h3>Latest Updates</h3>
        <p>
            Stay informed with the latest updates from the community! This page features blogs and news articles 
            shared by users, covering a wide range of topics. From game highlights to personal stories, there’s 
            always something new to discover. Read, engage, and stay connected with what’s happening around you.
             Whether you're looking for inspiration, information, or just a good read, the updates page has it all.
              Don’t forget to share your own blogs and news to contribute to the conversation. Let’s keep the 
              community vibrant and informed!
        </p>
    </section>
    <section class="updates-section py-5">
        <div class="container">
            <h2>Updates</h2>

            
            <!-- Blogs Section -->
            <h3>Blogs</h3>
            <div class="row">
                <?php foreach ($blogs as $blog): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($blog['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($blog['content']), 0, 100) ?>...</p>
                            <a href="updates.php?view=blog&id=<?= $blog['id'] ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- News Section -->
            <h3>News</h3>
            <div class="row">
                <?php foreach ($news as $news_item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($news_item['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($news_item['content']), 0, 100) ?>...</p>
                            <a href="updates.php?view=news&id=<?= $news_item['id'] ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>