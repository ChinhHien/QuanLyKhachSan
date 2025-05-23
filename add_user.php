<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$cccd = $username = $password = $email = $phone = $gender = $birthdate = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cccd = $_POST['cccd'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = '123456'; // default password (not hashed, as requested)
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate từng trường
    if ($cccd === '') {
        $error = "Vui lòng nhập CCCD.";
    } elseif (!preg_match('/^\d{12}$/', $cccd)) {
        $error = "CCCD phải bao gồm đúng 12 chữ số.";
    } elseif ($username === '') {
        $error = "Vui lòng nhập tên đăng nhập.";
    } elseif ($email === '') {
        $error = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } elseif ($gender === '') {
        $error = "Vui lòng chọn giới tính.";
    } elseif ($birthdate === '') {
        $error = "Vui lòng chọn ngày sinh.";
    } elseif ($role === '') {
        $error = "Vui lòng chọn vai trò.";
    } else {
        // Kiểm tra trùng lặp CCCD, Email, Phone
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE cccd = ? OR email = ? OR phone = ?");
        $check_stmt->bind_param("sss", $cccd, $email, $phone);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['cccd'] === $cccd) {
                    $error = "CCCD đã tồn tại trong hệ thống.";
                    break;
                }
                if ($row['email'] === $email) {
                    $error = "Email đã tồn tại trong hệ thống.";
                    break;
                }
                if ($phone !== '' && $row['phone'] === $phone) {
                    $error = "Số điện thoại đã tồn tại trong hệ thống.";
                    break;
                }
            }
        } else {
            // Thêm người dùng mới
            $stmt = $conn->prepare("INSERT INTO users (cccd, username, password, email, phone, gender, birthdate, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $cccd, $username, $password, $email, $phone, $gender, $birthdate, $role);

            if ($stmt->execute()) {
                header('Location: admin.php?tab=users&message=Thêm người dùng thành công');
                exit;
            } else {
                $error = "Lỗi khi thêm người dùng: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm người dùng</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/product_add_edit.css">
</head>

<body>
    <!-- Alert Box -->
    <div id="alert-box" class="alert alert-danger alert-box" style="display: <?= empty($error) ? 'none' : 'block' ?>">
        <?= htmlspecialchars($error) ?>
    </div>

    <?php
    if (isset($_GET['message'])) {
        echo '<div id="alert-box" class="alert alert-success alert-box">' . htmlspecialchars(($_GET['message'])) . '</div>';
    }
    ?>

    <div class="container">
        <div class="product-form-container">
            <!-- Form Header -->
            <div class="form-header">
                <h2><i class="fas fa-box-open mr-2"></i> Thêm người dùng mới</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" id="productForm">
                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông tin cơ bản</h3>

                    <div class="form-group mb-3">
                        <label for="username" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone">
                    </div>

                    <div class="form-group mb-3">
                        <label for="cccd" class="form-label">CCCD</label>
                        <input type="text" class="form-control" name="cccd" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="gender" class="form-label">Giới tính</label>
                        <select class="form-control" name="gender" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Male">Nam</option>
                            <option value="Female">Nữ</option>
                            <option value="Other">Khác</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="birthdate" class="form-label">Ngày sinh</label>
                        <input type="date" class="form-control" name="birthdate" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="role" class="form-label">Vai trò</label>
                        <select class="form-control" name="role" required>
                            <option value="">-- Chọn vai trò --</option>
                            <option value="Customer">Customer</option>
                            <option value="Employee">Employee</option>
                            <option value="Sponsor">Sponsor</option>
                        </select>
                    </div>

                    <div class="action-buttons">
                        <a href="admin.php?tab=rooms" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Thêm người dùng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Hiển thị thông báo lỗi - Simplified from edit_product.php
        function showAlert(message, type = 'danger') {
            const alertBox = document.getElementById('alert-box');
            alertBox.className = `alert alert-${type} alert-box`;
            alertBox.textContent = message;
            alertBox.style.display = 'block';
            alertBox.classList.add('fade-alert-show');

            setTimeout(() => {
                alertBox.classList.remove('fade-alert-show');
                alertBox.classList.add('fade-alert-hide');

                setTimeout(() => {
                    alertBox.style.display = 'none';
                }, 500);
            }, 3000);
        }

        // Show image preview when selected
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const fileName = input.files[0]?.name || 'Chọn ảnh...';
            input.nextElementSibling.textContent = fileName;

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Ảnh xem trước" class="preview-image">`;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Show multiple image previews
        function previewMultipleImages(input) {
            const preview = document.getElementById('additionalImagesPreview');
            const fileCount = input.files.length;

            input.nextElementSibling.textContent = fileCount > 0 ? `Đã chọn ${fileCount} ảnh` : 'Chọn các ảnh phụ...';

            if (fileCount > 0) {
                preview.innerHTML = '';

                const previewContainer = document.createElement('div');
                previewContainer.className = 'additional-images-grid';

                for (let i = 0; i < fileCount; i++) {
                    const reader = new FileReader();
                    const file = input.files[i];

                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'additional-image-container';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = `Ảnh phụ ${i+1}`;
                        img.className = 'additional-preview-image';

                        imgContainer.appendChild(img);
                        previewContainer.appendChild(imgContainer);
                    }

                    reader.readAsDataURL(file);
                }

                preview.appendChild(previewContainer);
            } else {
                preview.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-images fa-3x mb-2"></i>
                        <p>Chưa có ảnh phụ được chọn</p>
                    </div>
                `;
            }
        }

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            // Show error message if exists
            <?php if (!empty($error)): ?>
                showAlert('<?= addslashes($error) ?>', 'danger');
            <?php endif; ?>

            <?php if (isset($_GET['message'])): ?>
                showAlert('<?= addslashes($_GET['message']) ?>', 'success');
            <?php endif; ?>
        });
    </script>
</body>

</html>