<?php
session_start();
include('config/db.php');
require_once __DIR__ . '/vendor/autoload.php';

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$username = $email = $password = $gender = $birthdate = $phone = '';
$error = $success = '';

if (
    isset($_POST["username"]) &&
    isset($_POST["email"]) &&
    isset($_POST["password"]) &&
    isset($_POST["gender"]) &&
    isset($_POST["birthdate"]) &&
    isset($_POST["phone"])
) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $gender = $_POST["gender"];
    $birthdate = $_POST["birthdate"];
    $phone = $_POST["phone"];

    // Validate
    if (empty($username)) {
        $error = 'Vui lòng nhập tên đăng nhập';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        $error = 'Số điện thoại không hợp lệ';
    } elseif (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other'])) {
        $error = 'Vui lòng chọn giới tính';
    } elseif (empty($birthdate)) {
        $error = 'Vui lòng chọn ngày sinh';
    } else {
        // Kiểm tra username tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? UNION SELECT id FROM pending_users WHERE username = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Tên đăng nhập đã được sử dụng';
        }
        $stmt->close();

        // Kiểm tra email tồn tại
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? UNION SELECT id FROM pending_users WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Email đã được sử dụng';
            }
            $stmt->close();
        }

        if (empty($error)) {
            $stmt_delete = $conn->prepare("DELETE FROM pending_users WHERE email = ?");
            $stmt_delete->bind_param("s", $email);
            $stmt_delete->execute();
            $stmt_delete->close();

            $token = bin2hex(random_bytes(16));
            $expires = time() + 60;

            $stmt = $conn->prepare("INSERT INTO pending_users (username, email, password, gender, birthdate, phone, token, expires) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $username, $email, $password, $gender, $birthdate, $phone, $token, $expires);
            $stmt->execute();

            $_SESSION['pending_email'] = $email;

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
                $mail->Subject = "=?UTF-8?B?" . base64_encode("Xác minh đăng ký tài khoản") . "?=";

                $link = BASE_URL . "/verify.php?token=$token";
                $mail->Body = "
                    <p>Chào <b>$username</b>,</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Your domain</strong>.</p>
                    <p>Vui lòng xác minh tài khoản bằng cách nhấn vào liên kết sau:</p>
                    <p><a href='$link' target='_blank'>$link</a></p>
                    <p><small>Liên kết có hiệu lực trong vòng 1 phút.</small></p>
                ";
                $mail->AltBody = "Chào $username,\nVui lòng xác minh tài khoản tại liên kết: $link";

                $mail->send();
                header("Location: layout/verify_check.html");
                exit;
            } catch (Exception $e) {
                $error = "Gửi mail thất bại: {$mail->ErrorInfo}";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Đăng ký tài khoản</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #dfe3ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        body::before {
            content: "\\frac{1}{2}";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30rem;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.05);
            /* Màu đỏ nhạt */
            z-index: -1;
            pointer-events: none;
        }

        .register-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(2px);
        }

        .register-header {
            background: linear-gradient(135deg, #2f3542, #1e272e);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .register-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-logo img {
            max-width: 100px;
            max-height: 100px;
        }

        .register-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .register-subtitle {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .register-form {
            padding: 30px;
        }

        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-control {
            padding-left: 45px;
            height: 50px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: #ff4757;
            box-shadow: 0 0 0 0.25rem rgba(255, 71, 87, 0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #ff4757, #ff6b81);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        .register-footer a {
            color: #ff4757;
            font-weight: 600;
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-container shadow-lg">
                    <div class="register-header">
                        <div class="register-logo">
                            <!-- <img src="assets/img/HBH_Store/logo.png" alt="HBH Store"> -->
                        </div>
                        <h1 class="register-title">ĐĂNG KÝ TÀI KHOẢN</h1>
                        <p class="register-subtitle">Vui lòng nhập thông tin của bạn</p>
                    </div>

                    <form method="post" class="register-form">
                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="username" class="form-label fw-bold">Tên đăng nhập</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?= htmlspecialchars($username) ?>" placeholder="Nhập tên đăng nhập" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($email) ?>" placeholder="Nhập email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="<?= htmlspecialchars($phone) ?>" placeholder="Nhập số điện thoại" maxlength="10" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="gender" class="form-label fw-bold">Giới tính</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">-- Chọn giới tính --</option>
                                        <option value="Male" <?= $gender == 'Male' ? 'selected' : '' ?>>Nam</option>
                                        <option value="Female" <?= $gender == 'Female' ? 'selected' : '' ?>>Nữ</option>
                                        <option value="Other" <?= $gender == 'Other' ? 'selected' : '' ?>>Khác</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="birthdate" class="form-label fw-bold">Ngày sinh</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate"
                                        value="<?= htmlspecialchars($birthdate) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-register">
                                <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ
                            </button>
                        </div>

                        <div class="register-footer">
                            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>