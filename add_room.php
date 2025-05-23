<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$name = $type_id = $description = $image = '';

$room_types = [];
$result = $conn->query("SELECT id, typename FROM room_types");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $room_types[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $type_id = $_POST['type_id'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($name === '') {
        $error = 'Tên phòng không được để trống.';
    } elseif ($type_id === '') {
        $error = 'Vui lòng chọn loại phòng.';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $error = 'Vui lòng chọn ảnh phòng.';
    } else {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($image_ext), $allowed_extensions)) {
            $image_new_name = "room_" . uniqid() . "." . $image_ext;
            $image_upload_path = "assets/img/room_img/" . $image_new_name;

            $upload_dir = "assets/img/room_img/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($image_tmp, $image_upload_path)) {
                $stmt = $conn->prepare("INSERT INTO rooms (name, type_id, description, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siss", $name, $type_id, $description, $image_upload_path);

                if ($stmt->execute()) {
                    header('Location: admin.php?tab=rooms&message=Thêm phòng thành công');
                    exit;
                } else {
                    $error = 'Lỗi khi thêm phòng: ' . $conn->error;
                }
            } else {
                $error = 'Không thể tải ảnh lên.';
            }
        } else {
            $error = 'Định dạng ảnh không hợp lệ.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm phòng</title>
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
                <h2><i class="fas fa-box-open mr-2"></i> Thêm phòng</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" id="productForm">
                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông tin cơ bản</h3>

                    <div class="form-group mb-3">
                        <label for="name">Tên phòng</label>
                        <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="type_id">Loại phòng</label>
                        <select name="type_id" id="type_id" class="form-control" required>
                            <option value="">-- Chọn loại phòng --</option>
                            <?php foreach ($room_types as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= (isset($type_id) && $type_id == $type['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['typename']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Mô tả</label>
                        <textarea name="description" id="description" rows="4" class="form-control"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Ảnh phòng</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    </div>

                    <div class="action-buttons">
                        <a href="admin.php?tab=rooms" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Thêm phòng
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