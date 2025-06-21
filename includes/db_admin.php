<?php
// db_admin.php
$host = 'sql313.infinityfree.com';
$dbname = 'if0_38581364_gaming_website_db'; // Replace with your user database name
$username = 'if0_38581364'; // Replace with your database username
$password = 'Vaibhav85'; // Replace with your database password

try {
    $conn_admin = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn_admin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Admin Database Connection Failed: " . $e->getMessage());
}
?>