<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sport Fest</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        /* Hide all sections by default */
        .page-section {
            display: none;
        }

        /* Scoped Styles for Footer Only */
        .sport-fest-footer {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }

        .sport-fest-footer h5 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .sport-fest-footer a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .sport-fest-footer a:hover {
            color: #ffdd57;
        }

        .sport-fest-footer .social-icons a {
            color: white;
            transition: transform 0.3s ease;
        }

        .sport-fest-footer .social-icons a:hover {
            transform: translateY(-5px);
            color: #ffdd57;
        }

        .sport-fest-footer .list-unstyled li {
            margin-bottom: 0.5rem;
        }

        .sport-fest-footer .text-center {
            margin-top: 2rem;
        }

        .sport-fest-footer .text-center a {
            color: #ffdd57;
            text-decoration: underline;
        }

        .sport-fest-footer .text-center a:hover {
            color: #ffcc00;
        }

        /* Custom Styling for Organizer Section */
        #organizers .card {
            background: linear-gradient(120deg, #edfbf9, #eecc92);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        #organizers .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        #organizers .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        #organizers .btn-primary {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
        }

    </style>
</head>
<body>
    

    <!-- Main Content -->
    <main class="container my-5">
        <!-- About Us Section -->
        <section id="about-us" class="page-section">
            <h2 class="text-center mb-4">About Us</h2>
            <p>Nitra Technical Campus is proud to present the Nitra Sport Fest, a premier platform for sports and gaming enthusiasts. Our mission is to bring together students, faculty, and sports lovers from all walks of life to celebrate the spirit of sportsmanship, teamwork, and competition. Whether you're a professional athlete, a casual gamer, or simply a fan of sports, there's something for everyone at Nitra Sport Fest.

Founded in 2025, the Nitra Sport Fest has grown to become one of the most anticipated events in the region. It offers a wide range of activities, including eSports tournaments, traditional sports competitions, and fun gaming challenges. Our goal is to provide a platform where participants can showcase their skills, connect with like-minded individuals, and create unforgettable memories.

This website serves as your one-stop destination for all things related to the Nitra Sport Fest. Here, you can register for events, view fixtures, check leaderboards, and stay updated with the latest news and announcements. Join us for an exhilarating experience filled with excitement, camaraderie, and fun!
                </p>
        </section>

       <!-- Organizer Section -->
        <section id="organizers" class="page-section">
            <h2 class="text-center mb-4">Organizers & Developers</h2>
            <div class="row">
                <!-- Organizer 1 -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        
                        <div class="card-body">
                            <h5 class="card-title">Nitra Technical Campus</h5>
                            <p class="card-text">
                            Nitra Technical Campus proudly organizes Nitra Sport Fest to foster sportsmanship and teamwork, creating unforgettable experiences for participants. Our dedicated Sports Management Faculty provides expert guidance, ensuring smooth execution and competitive spirit.</p>
                            <a href="https://nitra.ac.in" class="btn btn-primary" target="_blank">Visit Website</a>
                        </div>
                    </div>
                </div>

                <!-- Organizer 2 -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        
                        <div class="card-body">
                            <h5 class="card-title">Sports Committee</h5>
                            <p class="card-text">
                            The Sports Committee, led by dedicated 3rd-year students, oversees the planning and execution of Nitra Sport Fest, ensuring smooth management of all games. Their commitment guarantees an exciting and well-organized experience for all participants and attendees.</p>
                            <a href="mailto:vaibhavbabele15@gmail.com" class="btn btn-primary">Contact Us</a>
                        </div>
                    </div>
                </div>

                <!-- Developer -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        
                        <div class="card-body">
                            <h5 class="card-title">Website Developers</h5>
                            <p class="card-text">
                            This platform is proudly developed by a dedicated team from Nitra Technical Campus, led by Vaibhav Babele, a passionate 3rd-year B.Tech CSE student and tech enthusiast. His expertise and leadership ensure a seamless and engaging experience for all participants.
                            </p>
                            <a href="mailto:vaibhavbabele15@gmail.com" class="btn btn-primary">Contact Developers</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Privacy Policy Section -->
        <section id="privacy-policy" class="page-section">
            <h2 class="text-center mb-4">Privacy Policy</h2>
            <p>
                At Nitra Sport Fest, we are committed to protecting your privacy. This Privacy Policy outlines how we collect,
                use, and safeguard your personal information when you visit our website or participate in our events.
            </p>
            <h4>Information We Collect</h4>
            <ul>
                <li>Name, email address, and contact details.</li>
                <li>Payment information for event registrations.</li>
                <li>Demographic information such as age and location.</li>
            </ul>
            <h4>How We Use Your Information</h4>
            <ul>
                <li>To process event registrations and payments.</li>
                <li>To communicate with you about upcoming events.</li>
                <li>To improve our services and website.</li>
            </ul>
            
        </section>

        <!-- Terms & Conditions Section -->
        <section id="terms-conditions" class="page-section">
            <h2 class="text-center mb-4">Terms & Conditions</h2>
            <p>
                By participating in Nitra Sport Fest, you agree to the following terms and conditions:
            </p>
            <h4>Event Participation</h4>
            <ul>
                <li>All participants must register online before the deadline.</li>
                <li>Participants are responsible for their own safety and equipment.</li>
                <li>Refunds are not available for cancellations.</li>
            </ul>
            <h4>Code of Conduct</h4>
            <ul>
                <li>Respect all participants, organizers, and volunteers.</li>
                <li>Any form of harassment or misconduct will result in immediate disqualification.</li>
            </ul>
            <h4>Liability</h4>
            <p>
                Nitra Sport Fest is not responsible for any injuries or damages incurred during the event.
            </p>
        </section>
    </main>

    <!-- Footer -->
    <footer class="sport-fest-footer py-5">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase text-white">About Sport Fest</h5>
                    <p class="text-white">Sport Fest is your ultimate destination for gaming and sports events. Join us for an unforgettable experience!</p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase text-white">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#about-us" class="text-white text-decoration-none" onclick="showSection('about-us')">About Us</a></li>
                        <li><a href="#organizers" class="text-white text-decoration-none" onclick="showSection('organizers')">Event Organizer</a></li>
                        <li><a href="#privacy-policy" class="text-white text-decoration-none" onclick="showSection('privacy-policy')">Privacy Policy</a></li>
                        <li><a href="#terms-conditions" class="text-white text-decoration-none" onclick="showSection('terms-conditions')">Terms & Conditions</a></li>
                    </ul>
                </div>

                <!-- Social Media Links -->
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase text-white">Follow Us</h5>
                    <ul class="list-unstyled d-flex justify-content-start gap-3">
                        <li><a href="https://www.instagram.com/my_vlog.spot/" class="text-white text-decoration-none"><i class="fab fa-instagram fa-2x"></i></a></li>
                        <li><a href="https://www.youtube.com/@my.vlog_spot" class="text-white text-decoration-none"><i class="fab fa-youtube fa-2x"></i></a></li>
                        <li><a href="https://www.linkedin.com/in/vaibhavbabele/" class="text-white text-decoration-none"><i class="fab fa-linkedin fa-2x"></i></a></li>
                    </ul>
                </div>
            </div>
            

            <!-- Copyright -->
            <div class="text-center mt-4">
                <p>TOTAL VISITS <script type="text/javascript" src="https://www.freevisitorcounters.com/en/home/counter/1320877/t/13"></script></p>
                
                <p class="text-white">&copy; 2025 Nitra Sport Fest. All rights reserved.</p>
                <p class="text-white">Contact: <a href="mailto:vaibhavbabele15@gmail.com" class="text-white text-decoration-none">support@nitrasportfest.com</a></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Function to show the selected section and hide others
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.page-section').forEach(section => {
                section.style.display = 'none';
            });

            // Show the selected section
            document.getElementById(sectionId).style.display = 'block';
        }

        // Function to show event details
        function showEventDetails(eventId) {
            // Hide all sections
            document.querySelectorAll('.page-section').forEach(section => {
                section.style.display = 'none';
            });

            // Show the event details section
            const eventDetails = document.getElementById('event-details');
            eventDetails.style.display = 'block';

            // Set event details based on the eventId
            const eventTitle = document.getElementById('event-title');
            const eventDescription = document.getElementById('event-description');

            switch (eventId) {
                case 'football':
                    eventTitle.textContent = 'Football Tournament';
                    eventDescription.textContent = 'Join our annual football tournament and showcase your skills. Open to all age groups.';
                    break;
                case 'cricket':
                    eventTitle.textContent = 'Cricket League';
                    eventDescription.textContent = 'Participate in our cricket league and compete for the championship trophy.';
                    break;
                case 'esports':
                    eventTitle.textContent = 'E-Sports Championship';
                    eventDescription.textContent = 'Compete in our E-Sports championship featuring popular games like PUBG, Valorant, and more.';
                    break;
            }
        }

        // Function to hide event details and return to the events section
        function hideEventDetails() {
            // Hide the event details section
            document.getElementById('event-details').style.display = 'none';

            // Show the events section
            showSection('events');
        }
    </script>
</body>
</html>