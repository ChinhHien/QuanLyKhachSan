<?php
session_start();
include('config/db.php');
require_once __DIR__ . '/vendor/autoload.php';

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

use PHPMailer\PHPMailer\PHPMailer;

function sendVerificationEmail($username, $email, $token) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'cloudfacebook123@gmail.com';
    $mail->Password = 'bxbk hqzl yuie sjaf';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('cloudfacebook123@gmail.com', 'Your domain');
    $mail->addAddress($email, $username);
    $mail->isHTML(true);
    $mail->Subject = "=?UTF-8?B?" . base64_encode("Gửi lại mã xác minh tài khoản") . "?=";

    $link = BASE_URL . "/verify.php?token=$token";
    $mail->Body = "
        <p>Chào <b>$username</b>,</p>
        <p>Đây là mã xác minh mới cho tài khoản tại <strong>Your domain</strong>:</p>
        <p><a href='$link' target='_blank'>$link</a></p>
        <p><small>Liên kết có hiệu lực trong vòng 1 phút.</small></p>
    ";
    $mail->send();
}

$email = $_SESSION['pending_email'] ?? '';
if (empty($email)) {
    die("Không tìm thấy email chưa xác minh. Vui lòng thử đăng ký lại.");
}

$email = $_SESSION['pending_email'];
$stmt = $conn->prepare("SELECT * FROM pending_users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Không tìm thấy dữ liệu đăng ký. Vui lòng thử đăng ký lại.");
}

$new_token = bin2hex(random_bytes(16));
$new_expiry = time() + 300;

$stmt = $conn->prepare("UPDATE pending_users SET token = ?, expires = ? WHERE email = ?");
$stmt->bind_param("sis", $new_token, $new_expiry, $email);
$stmt->execute();

sendVerificationEmail($user['username'], $email, $new_token);

header("Location: layout/resend_verify_check.html");
exit;
?>
