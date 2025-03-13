<?php
// Database credentials
$db_name = 'mysql:host=localhost;dbname=querybite'; // Access the querybite database
$user_name = 'root';
$user_password = '';

try {
    // Create a PDO connection
    $conn = new PDO($db_name, $user_name, $user_password);

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to the database!<br><br>";

} catch (PDOException $e) {
    // Handle connection failure
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>