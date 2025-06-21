<?php
// db_user.php
$host = 'sql313.infinityfree.com';
$dbname = 'if0_38581364_gaming_website_db'; // Replace with your user database name
$username = 'if0_38581364'; // Replace with your database username
$password = 'Vaibhav85'; // Replace with your database password

try {
    $conn_user = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn_user->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("User Database Connection Failed: " . $e->getMessage());
}
?>