<?php
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password, empty if none
$dbname = "fileshare";
$port = 3306; // Your MySQL port

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
