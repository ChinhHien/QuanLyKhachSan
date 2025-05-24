<?php
include('config/db.php');

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);
    $new_gender = $_POST['gender'];
    $new_birthdate = $_POST['birthdate'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $avatar = $user['avatar'];

    // Kiểm tra mật khẩu hiện tại
    if (empty($current_password)) {
        header('Location: employee.php?tab=account-info&error=Vui lòng nhập mật khẩu hiện tại để xác thực');
        exit();
    } else if ($current_password !== $user['password']) {
        header('Location: employee.php?tab=account-info&error=Mật khẩu hiện tại không chính xác!');
        exit();
    }

    // Không được để trống dữ liệu cơ bản
    if (empty($new_email)) {
        header('Location: employee.php?tab=account-info&error=Email không được để trống');
        exit();
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            header('Location: employee.php?tab=account-info&error=Email đã được sử dụng bởi người dùng khác');
            exit();
        }
        $stmt->close();
    }

    if (empty($new_phone)) {
        header('Location: employee.php?tab=account-info&error=Số điện thoại không được để trống');
        exit();
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->bind_param("si", $new_phone, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            header('Location: employee.php?tab=account-info&error=Số điện thoại đã được sử dụng bởi người dùng khác');
            exit();
        }
        $stmt->close();
    }

    if (empty($new_address)) {
        header('Location: employee.php?tab=account-info&error=Địa chỉ không được để trống');
        exit();
    }

    // Xử lý avatar nếu có tải lên
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = $_FILES['avatar']['name'];
        $avatar_tmp = $_FILES['avatar']['tmp_name'];
        $avatar_ext = pathinfo($avatar_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($avatar_ext), $allowed_extensions)) {
            $avatar_new_name = "avatar_" . $user['username'] . "_" . time() . "." . $avatar_ext;
            $avatar_upload_path = "assets/img/avatars/" . $avatar_new_name;

            if (move_uploaded_file($avatar_tmp, $avatar_upload_path)) {
                $avatar = $avatar_upload_path;
            } else {
                header('Location: employee.php?tab=account-info&error=Lỗi khi tải ảnh đại diện lên. Vui lòng thử lại');
                exit();
            }
        } else {
            header('Location: employee.php?tab=account-info&error=Chỉ chấp nhận các định dạng ảnh JPG, JPEG, PNG, GIF');
            exit();
        }
    }

    // Xử lý cập nhật thông tin
    if (empty($new_password)) {
        // Nếu không nhập mật khẩu mới, giữ nguyên mật khẩu cũ
        $password_to_update = $user['password'];

        $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, address = ?, gender = ?, birthdate = ?, avatar = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $new_email, $new_phone, $new_address, $new_gender, $new_birthdate, $avatar, $user_id);
    } else {
        // Nếu có nhập mật khẩu mới
        if (strlen($new_password) < 6) {
            header('Location: employee.php?tab=account-info&error=Mật khẩu mới phải có ít nhất 6 kí tự');
            exit();
        }

        $password_to_update = $new_password;

        $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, address = ?, gender = ?, birthdate = ?, avatar = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $new_email, $new_phone, $new_address, $new_gender, $new_birthdate, $avatar, $password_to_update, $user_id);
    }

    if ($stmt->execute()) {
        header('Location: employee.php?tab=account-info&message=Cập nhật thông tin thành công');
        exit();
    } else {
        header('Location: employee.php?tab=account-info&error=Lỗi khi lưu thay đổi. Vui lòng thử lại');
        exit();
    }
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const formProfileImage = document.querySelector('.profile-picture-container .profile-image');

        if (avatarInput && formProfileImage) {
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        formProfileImage.src = e.target.result;
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>