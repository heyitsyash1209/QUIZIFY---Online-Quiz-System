<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "quiz_system_v2";
$port = 3307;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// echo "Database Connected Successfully!"; // remove this line
?>