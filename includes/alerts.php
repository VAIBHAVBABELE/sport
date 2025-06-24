<?php
session_start();

// Display success/error messages
function displayAlert() {
    if (isset($_SESSION['error'])) {
        $errorMessage = $_SESSION['error'];

        // Customize error messages for better user understanding
        if (strpos($errorMessage, 'Duplicate entry') !== false) {
            // Handle duplicate entry errors
            preg_match("/Duplicate entry '(.+?)' for key/", $errorMessage, $matches);
            $duplicateValue = $matches[1] ?? '';
            echo '<div class="alert alert-danger">Duplicate entry detected! The value "' . htmlspecialchars($duplicateValue) . '" already exists. Please use a unique value.</div>';
        } elseif (strpos($errorMessage, 'cannot be null') !== false) {
            // Handle null value errors
            preg_match("/Column '(.+?)' cannot be null/", $errorMessage, $matches);
            $columnName = $matches[1] ?? '';
            echo '<div class="alert alert-danger">Required field "' . htmlspecialchars($columnName) . '" is missing. Please fill in all required fields.</div>';
        } elseif (strpos($errorMessage, 'Data too long') !== false) {
            // Handle data too long errors
            preg_match("/Data too long for column '(.+?)'/", $errorMessage, $matches);
            $columnName = $matches[1] ?? '';
            echo '<div class="alert alert-danger">The data entered for "' . htmlspecialchars($columnName) . '" is too long. Please shorten it and try again.</div>';
        } elseif (strpos($errorMessage, 'SQLSTATE[HY000] [2002]') !== false) {
            // Handle database connection errors
            echo '<div class="alert alert-danger">Unable to connect to the database. Please check your database configuration and try again.</div>';
        } elseif (strpos($errorMessage, 'SQLSTATE[42S02]') !== false) {
            // Handle table not found errors
            preg_match("/Table '(.+?)' doesn't exist/", $errorMessage, $matches);
            $tableName = $matches[1] ?? '';
            echo '<div class="alert alert-danger">The table "' . htmlspecialchars($tableName) . '" does not exist. Please check your database setup.</div>';
        } elseif (strpos($errorMessage, 'SQLSTATE[23000]') !== false) {
            // Handle foreign key constraint violations
            echo '<div class="alert alert-danger">A related record is missing. Please ensure all required data is entered correctly.</div>';
        } elseif (strpos($errorMessage, 'SQLSTATE[42000]') !== false) {
            // Handle syntax errors in SQL queries
            echo '<div class="alert alert-danger">There was an error in the database query. Please contact support.</div>';
        } elseif (strpos($errorMessage, 'Invalid input') !== false) {
            // Handle custom input validation errors
            echo '<div class="alert alert-danger">' . htmlspecialchars($errorMessage) . '</div>';
        } else {
            // Generic error message for other cases
            echo '<div class="alert alert-danger">An unexpected error occurred. Please try again later.</div>';
        }
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
}

// Example usage in a form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate a database error (e.g., duplicate roll number)
    $simulatedError = "Duplicate entry '12345' for key 'roll_no'";
    $_SESSION['error'] = $simulatedError;

    // Redirect back to the form
    header('Location: form.php');
    exit();
}
?>