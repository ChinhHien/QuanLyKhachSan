<?php
$host = 'localhost';
$dbname = 'db';
$username = 'root'; 
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>