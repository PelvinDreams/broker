<?php


$servername = "localhost";
$username = "pivo_astrofx";
$password = "Dinoboy123";
$dbname = "pivo_astrofx";

// Create a new MySQL connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
