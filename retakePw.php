<?php
session_start();
include('config/db.php');
require_once __DIR__ . '/vendor/autoload.php';

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";
$step = isset($_SESSION['forgot_pw_step']) ? $_SESSION['forgot_pw_step'] : 1;

if (isset($_POST['submit_email'])) {
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        // Check if email exists in system
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = 'Tài khoản không tồn tại hoặc sai thông tin';
        } else {
            $user = $result->fetch_assoc();
            $username = $user['username'];

            // Generate verification code
            $verificationCode = sprintf("%06d", mt_rand(100000, 999999));
            $expires = time() + 300; // 5 minutes

            // Delete any existing password reset requests for this email
            $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt_delete->bind_param("s", $email);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Store verification code in database
            $stmt = $conn->prepare("INSERT INTO password_resets (email, code, expires) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $email, $verificationCode, $expires);
            $stmt->execute();

            // Send verification email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'cloudfacebook123@gmail.com';
                $mail->Password = 'bxbk hqzl yuie sjaf';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('cloudfacebook123@gmail.com', 'Your domain');
                $mail->addAddress($email, $username);
                $mail->addReplyTo('cloudfacebook123@gmail.com', 'Your domain');
                $mail->isHTML(true);
                $mail->Subject = "=?UTF-8?B?" . base64_encode("Mã xác nhận đặt lại mật khẩu") . "?=";

                $mail->Body = "
                    <p>Chào <b>$username</b>,</p>
                    <p>Bạn vừa yêu cầu đặt lại mật khẩu tại <strong>Your domain</strong>.</p>
                    <p>Mã xác nhận của bạn là: <strong>$verificationCode</strong></p>
                    <p><small>Mã xác nhận có hiệu lực trong vòng 5 phút.</small></p>
                    <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
                ";
                $mail->AltBody = "Chào $username,\nMã xác nhận đặt lại mật khẩu của bạn là: $verificationCode\nMã có hiệu lực trong vòng 5 phút.";

                $mail->send();

                // Save email in session and move to step 2
                $_SESSION['forgot_email'] = $email;
                $_SESSION['forgot_pw_step'] = 2;
                $step = 2;
                $success = "Mã xác nhận đã được gửi đến email của bạn. Vui lòng kiểm tra và nhập mã xác nhận.";
            } catch (Exception $e) {
                $error = "Gửi mail thất bại: {$mail->ErrorInfo}";
            }
        }
        $stmt->close();
    }
}

if (isset($_POST['submit_code'])) {
    $code = trim($_POST['verification_code']);
    $email = $_SESSION['forgot_email'];

    if (empty($code)) {
        $error = 'Vui lòng nhập mã xác nhận';
    } else {
        // Check if verification code is valid
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND expires > ?");
        $now = time();
        $stmt->bind_param("ssi", $email, $code, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = 'Mã xác nhận không hợp lệ hoặc đã hết hạn';
        } else {
            // Generate new password
            $newPassword = bin2hex(random_bytes(4)); // Generate 8 character password

            // Update user password
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $newPassword, $email);
            $stmt_update->execute();

            if ($stmt_update->affected_rows > 0) {
                // Get username
                $stmt_user = $conn->prepare("SELECT username FROM users WHERE email = ?");
                $stmt_user->bind_param("s", $email);
                $stmt_user->execute();
                $user_result = $stmt_user->get_result();
                $user = $user_result->fetch_assoc();
                $username = $user['username'];

                // Send new password email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'lichinhhien@gmail.com';
                    $mail->Password = 'idso zasz whcg soop';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('lichinhhien@gmail.com', 'HBH Store');
                    $mail->addAddress($email, $username);
                    $mail->addReplyTo('lichinhhien@gmail.com', 'HBH Store');
                    $mail->isHTML(true);
                    $mail->Subject = "=?UTF-8?B?" . base64_encode("Mật khẩu mới của bạn") . "?=";

                    $mail->Body = "
                        <p>Chào <b>$username</b>,</p>
                        <p>Mật khẩu mới của bạn tại <strong>HBH Store</strong> là: <strong>$newPassword</strong></p>
                        <p>Vui lòng đăng nhập và thay đổi mật khẩu ngay sau khi đăng nhập.</p>
                        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
                    ";
                    $mail->AltBody = "Chào $username,\nMật khẩu mới của bạn là: $newPassword\nVui lòng đăng nhập và thay đổi mật khẩu ngay sau khi đăng nhập.";

                    $mail->send();

                    // Clean up
                    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmt_delete->bind_param("s", $email);
                    $stmt_delete->execute();

                    // Reset session and show success message
                    unset($_SESSION['forgot_email']);
                    unset($_SESSION['forgot_pw_step']);
                    $success = "Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra email và đăng nhập.";
                    $step = 3; // Success step

                } catch (Exception $e) {
                    $error = "Gửi mail thất bại: {$mail->ErrorInfo}";
                }

                $stmt_user->close();
            } else {
                $error = "Không thể cập nhật mật khẩu. Vui lòng thử lại sau.";
            }
            $stmt_update->close();
        }
        $stmt->close();
    }
}

// Reset process
if (isset($_GET['reset'])) {
    unset($_SESSION['forgot_email']);
    unset($_SESSION['forgot_pw_step']);
    $step = 1;
    header("Location: retakePw.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Lấy lại mật khẩu | HBH Store</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap4.5.2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login_register.css">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <div class="login-logo">
                            <img src="assets/img/HBH_Store/logo.png" alt="HBH Store">
                        </div>
                        <h1 class="login-title">LẤY LẠI MẬT KHẨU</h1>
                        <?php if ($step == 1): ?>
                            <p class="login-subtitle">Vui lòng nhập email để lấy lại mật khẩu</p>
                        <?php elseif ($step == 2): ?>
                            <p class="login-subtitle">Nhập mã xác nhận đã được gửi đến email của bạn</p>
                        <?php else: ?>
                            <p class="login-subtitle">Quá trình lấy lại mật khẩu hoàn tất</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($step == 1): ?>
                        <form action="" method="post" class="login-form">
                            <div class="form-group">
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>

                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success"><?= $success ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input name="email" id="email" type="email" class="form-control" placeholder="Nhập email đăng ký tài khoản" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="submit_email" class="btn-login">
                                    <i class="fas fa-paper-plane btn-icon"></i> Gửi mã xác nhận
                                </button>
                            </div>
                        </form>
                    <?php elseif ($step == 2): ?>
                        <form action="" method="post" class="login-form">
                            <div class="form-group">
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>

                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success"><?= $success ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="verification_code" class="form-label">Mã xác nhận</label>
                                <div class="input-group">
                                    <i class="fas fa-key input-icon"></i>
                                    <input name="verification_code" id="verification_code" type="text" class="form-control" placeholder="Nhập mã xác nhận" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="submit_code" class="btn-login">
                                    <i class="fas fa-check btn-icon"></i> Xác nhận
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="retakePw.php?reset=1">Nhập lại email</a>
                            </div>
                        </form>
                    <?php elseif ($step == 3): ?>
                        <!-- Step 3: Success -->
                        <div class="text-center">
                            <div class="form-group">
                                <p class="alert alert-success">Mật khẩu mới đã được gửi đến email của bạn.</p>
                            </div>
                            <a href="login.php" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="login-footer">
                        <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Particle effects -->
    <div class="particles" id="particles"></div>
</body>

</html>