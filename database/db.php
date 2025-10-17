<?php
$servername = "localhost";
$username = "phpuser";
$password = "StrongPassword123!";
$dbname = "E_Katanalotis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
