<?php
$servername = "localhost";
$username = "root"; // or your MySQL username
$password = "";     // your MySQL password
$dbname = "voting_system"; // your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
