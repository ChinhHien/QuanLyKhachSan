<?php
session_start();
include('config/db.php');

$message = '';
$success = false;
$role = 'Customer';

if (!isset($_GET['token'])) {
    $message = "Liên kết không hợp lệ.";
} else {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM pending_users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $message = "Mã xác minh không đúng hoặc đã được sử dụng.";
    } elseif (time() > $user['expires']) {
        $message = "Liên kết đã hết hạn. Vui lòng gửi lại mã xác minh.";
        $_SESSION['pending_email'] = $user['email'];
    } else {
        // Chuyển sang bảng users
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, gender, birthdate, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $user['username'], $user['email'], $user['password'], $user['gender'], $user['birthdate'], $user['phone'], $role);
        if ($stmt->execute()) {
            // Xóa khỏi pending_users
            $stmt_delete = $conn->prepare("DELETE FROM pending_users WHERE email = ?");
            $stmt_delete->bind_param("s", $user['email']);
            $stmt_delete->execute();
            $stmt_delete->close();

            $success = true;
            $message = "Tài khoản đã được xác minh thành công.";
        } else {
            $message = "Có lỗi khi tạo tài khoản.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xác minh tài khoản</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }

        .message {
            font-size: 1.1em;
            margin-bottom: 20px;
            color: #333;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }

        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="box">
        <h2>Xác minh tài khoản</h2>
        <div class="message"><?= htmlspecialchars($message) ?></div>
        <div class="btn-group">
            <?php if ($success): ?>
                <button onclick="location.href='login.php'">Đăng nhập</button>
            <?php else: ?>
                <?php if (isset($user['email'])): ?>
                    <?php $_SESSION['pending_email'] = $user['email']; ?>
                <?php endif; ?>
                <button onclick="location.href='resend_verification.php'">Gửi lại mã xác minh</button>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>