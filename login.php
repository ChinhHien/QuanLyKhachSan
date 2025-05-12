<?php
session_start();
include('config/db.php');

if (isset($_COOKIE['remember_id'])) {
    $role = $_COOKIE['role'];
    $_SESSION['id'] = $_COOKIE['remember_id'];
    $_SESSION['role'] = $role;

    redirectByRole($role);
    exit();
}

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
    redirectByRole($_SESSION['role']);
    exit();
}

$user = '';
$pass = '';
$error = '';

function redirectByRole($role) {
    switch ($role) {
        case 'Admin':
            header('Location: admin.php');
            break;
        case 'Employee':
            header('Location: employee.php');
            break;
        case 'Sponsor':
            header('Location: sponsor.php');
            break;
        case 'Customer':
        default:
            header('Location: index.php');
            break;
    }
}

if (isset($_POST["user"]) && isset($_POST["pass"])) {
    $user = trim($_POST["user"]);
    $pass = $_POST["pass"];

    if (empty($user)) {
        $error = 'Vui lòng nhập tên đăng nhập hoặc email';
    } else if (empty($pass)) {
        $error = 'Vui lòng nhập mật khẩu';
    } else if (strlen($pass) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 kí tự';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ?");
        $stmt->bind_param("sss", $user, $user, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if (!empty($row['is_locked'])) {
                $error = 'Tài khoản của bạn đã bị khóa.';
            } else {
                $_SESSION['id'] = $row['id'];
                $_SESSION['role'] = $row['role'];

                if (isset($_POST['remember'])) {
                    setcookie('remember_id', $row['id'], time() + (86400 * 365), "/");
                    setcookie('role', $row['role'], time() + (86400 * 365), "/");
                }

                redirectByRole($row['role']);
                exit();
            }
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Đăng nhập | </title>
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
                            <!-- <img src="assets/img/HBH_Store/logo.png" alt="HBH Store"> -->
                        </div>
                        <h1 class="login-title">ĐĂNG NHẬP</h1>
                        <p class="login-subtitle">Vui lòng nhập thông tin tài khoản</p>
                    </div>
                    
                    <form action="" method="post" class="login-form">
                        <div class="form-group">
                            <label for="username" class="form-label">Tên đăng nhập hoặc Email</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input value="<?= htmlspecialchars($user) ?>" name="user" id="username" type="text" class="form-control" placeholder="Nhập tên đăng nhập hoặc email">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input value="<?= htmlspecialchars($pass) ?>" name="pass" id="password" type="password" class="form-control" placeholder="Nhập mật khẩu">
                            </div>
                        </div>
                        
                        <div class="form-group custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                            <label class="custom-control-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                        
                        <div class="form-group">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt btn-icon"></i> ĐĂNG NHẬP
                            </button>
                        </div>
                        
                        <div class="login-footer">
                            <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                            <p>Quên mật khẩu? <a href="retakePw.php">Lấy lại mật khẩu</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>