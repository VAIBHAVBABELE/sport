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

// Fetch all approved images and videos, sorted by creation date (newest first)
$images = $conn_user->query("SELECT * FROM images WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$videos = $conn_user->query("SELECT * FROM videos WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush(); // End output buffering and send output to the browser
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Base Styles */
        body {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        /* Description Section */
        .description-section {
            background: linear-gradient(45deg, #ff7675, #d63031);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 1200px;
        }

        .description-section h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .description-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Gallery Section */
        .gallery-section {
            padding: 3rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-section h2 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
        }

        .gallery-section h3 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: #6a11cb;
            margin: 2rem 0 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        /* Gallery Item */
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .gallery-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .media-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #000;
        }

        .media-container img,
        .media-container video {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            display: block;
        }

        .gallery-item .title {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Video Controls */
        video {
            width: 100%;
            height: auto;
        }

        /* Filter and Sort Options */
        .filter-sort-options {
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-sort-options select,
        .filter-sort-options button {
            border-radius: 25px;
            border: 2px solid #6a11cb;
            background: white;
            color: #6a11cb;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-sort-options select:focus,
        .filter-sort-options button:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(106, 17, 203, 0.5);
        }

        .filter-sort-options button:hover {
            background: #6a11cb;
            color: white;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .description-section {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .description-section h3 {
                font-size: 1.5rem;
            }
            
            .gallery-section h2 {
                font-size: 2rem;
            }
            
            .gallery-section h3 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-sort-options {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <section class="description-section">
        <h3>Gallery Section</h3>
        <p>
            Explore the gallery to view a collection of images and videos shared by the community.  
            From thrilling game moments to creative content, the gallery showcases the best of what our 
            platform has to offer. You can filter and sort the content to find what interests you most. 
            Whether you're looking for inspiration or simply want to enjoy the visuals, the gallery is the 
            perfect place to immerse yourself in the vibrant culture of our community.
        </p>
    </section>
    <section class="gallery-section py-5">
        <div class="container">
            <h2>Gallery</h2>

            <div class="filter-sort-options">
                <select id="filter" class="form-control">
                    <option value="all">Show All</option>
                    <option value="images">Images Only</option>
                    <option value="videos">Videos Only</option>
                </select>
                <select id="sort" class="form-control">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

            <!-- Images Section -->
            <h3>Images</h3>
            <div class="gallery-grid" id="imagesContainer">
                <?php foreach ($images as $image): ?>
                <div class="gallery-item" data-timestamp="<?= strtotime($image['created_at']) ?>">
                    <div class="media-container">
                        <img src="../../<?= $image['image_path'] ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                    </div>
                    <div class="title"><?= htmlspecialchars($image['title']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Videos Section -->
            <h3>Videos</h3>
            <div class="gallery-grid" id="videosContainer">
                <?php foreach ($videos as $video): ?>
                <div class="gallery-item" data-timestamp="<?= strtotime($video['created_at']) ?>">
                    <div class="media-container">
                        <video controlsList="nodownload" disablePictureInPicture controls oncontextmenu="return false;">
                            <source src="../../<?= $video['video_path'] ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="title"><?= htmlspecialchars($video['title']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JS for Filtering and Sorting -->
    <script>
        $(document).ready(function () {
            // Initialize with all content visible
            $('#imagesContainer').show();
            $('#videosContainer').show();

            // Filter by type (images or videos)
            $('#filter').change(function () {
                const filterValue = $(this).val();
                if (filterValue === 'images') {
                    $('#imagesContainer').show();
                    $('#videosContainer').hide();
                    $('.gallery-section h3').eq(1).show();
                    $('.gallery-section h3').eq(2).hide();
                } else if (filterValue === 'videos') {
                    $('#imagesContainer').hide();
                    $('#videosContainer').show();
                    $('.gallery-section h3').eq(1).hide();
                    $('.gallery-section h3').eq(2).show();
                } else {
                    $('#imagesContainer').show();
                    $('#videosContainer').show();
                    $('.gallery-section h3').eq(1).show();
                    $('.gallery-section h3').eq(2).show();
                }
            });

            // Sort by newest or oldest
            $('#sort').change(function () {
                const sortValue = $(this).val();
                
                // Sort images
                const $images = $('#imagesContainer .gallery-item').get();
                $images.sort(function(a, b) {
                    return sortValue === 'newest' ? 
                        $(b).data('timestamp') - $(a).data('timestamp') : 
                        $(a).data('timestamp') - $(b).data('timestamp');
                });
                $('#imagesContainer').empty().append($images);
                
                // Sort videos
                const $videos = $('#videosContainer .gallery-item').get();
                $videos.sort(function(a, b) {
                    return sortValue === 'newest' ? 
                        $(b).data('timestamp') - $(a).data('timestamp') : 
                        $(a).data('timestamp') - $(b).data('timestamp');
                });
                $('#videosContainer').empty().append($videos);
            });

            // Make videos responsive
            $('video').each(function() {
                $(this).attr('width', '100%');
                $(this).attr('height', 'auto');
            });
        });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>