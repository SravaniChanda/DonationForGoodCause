<?php
$servername = "localhost"; // Usually "localhost"
$username = "root"; //  database username
$password = ""; // database password
$dbname = "donationsystem"; //  database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
