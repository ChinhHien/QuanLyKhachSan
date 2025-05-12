<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Customer') {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome to the Customer Page</h1>
    <p>This is a protected page for customers only.</p>
    <a href="logout.php">Logout</a>

    <script>
        // JavaScript code can be added here if needed
    </script>
</body>
</html>