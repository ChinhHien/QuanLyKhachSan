<?php
session_start();
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

if (isset($_COOKIE['remember_id'])) {
    setcookie('remember_id', '', time() - 3600, "/");
}
if (isset($_COOKIE['user_type'])) {
    setcookie('user_type', '', time() - 3600, "/");
}

header('Location: index.php');
exit();
?>