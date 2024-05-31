<?php
$host = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "music_blog";

$conn = new mysqli($host, $username, $password, $database); // створення нового об'єкта класу mysqli

if ($conn->connect_error) {
    die("Помилка з'єднання: " . $conn->connect_error);
}
?>
