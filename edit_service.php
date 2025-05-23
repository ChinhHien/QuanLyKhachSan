<?php
session_start();
include('config/db.php');

if (!isset($_GET['id'])) {
    header('Location: admin.php?tab=services');
    exit();
}

$service_id = $_GET['id'];

// Lấy thông tin dịch vụ
$stmt = $conn->prepare("SELECT * FROM service WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();

if (!$service) {
    header('Location: admin.php?tab=services');
    exit();
}

$error = '';

// Gán giá trị mặc định ban đầu
$name = $service['name'];
$description = $service['description'];
$price = $service['price'];
$unit = $service['unit'];
$status = $service['status'];
$image = $service['image'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    $status = $_POST['status'];

    if (empty($name) || empty($unit) || empty($status)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Giá dịch vụ không hợp lệ.';
    } else {
        // Xử lý ảnh nếu có cập nhật
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $new_image_name = "service_" . $service_id . "." . $ext;
                $upload_path = "assets/img/service_img/" . $new_image_name;

                if ($service['image'] && file_exists($service['image']) && $service['image'] != $upload_path) {
                    unlink($service['image']);
                }

                if (move_uploaded_file($image_tmp, $upload_path)) {
                    $image = $upload_path;
                } else {
                    $error = 'Lỗi khi tải ảnh lên.';
                }
            } else {
                $error = 'Chỉ chấp nhận ảnh JPG, JPEG, PNG, GIF.';
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE service SET name = ?, description = ?, price = ?, unit = ?, status = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssisssi", $name, $description, $price, $unit, $status, $image, $service_id);

            if ($stmt->execute()) {
                header('Location: admin.php?tab=services&message=Chỉnh sửa dịch vụ thành công');
                exit();
            } else {
                $error = 'Lỗi khi lưu thay đổi.';
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
    <title>Sửa dịch vụ</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/product_add_edit.css">
</head>

<body>
    <!-- Alert Box -->
    <?php
    if (isset($_GET['message'])) {
        echo '<div id="alert-box" class="alert alert-success fade-alert text-center">' . htmlspecialchars(($_GET['message'])) . '</div>';
    }
    ?>

    <div class="container">
        <div class="product-form-container">
            <!-- Form Header -->
            <div class="form-header">
                <h2><i class="fas fa-box-open mr-2"></i>Chỉnh sửa dịch vụ</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" id="productForm">
                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông tin cơ bản</h3>

                    <div class="form-group mb-3">
                        <label for="name">Tên dịch vụ</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="price">Giá (VNĐ)</label>
                        <input type="number" class="form-control" id="price" name="price" required value="<?= htmlspecialchars($price) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="unit">Đơn vị</label>
                        <input type="text" class="form-control" id="unit" name="unit" value="<?= htmlspecialchars($unit) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="Khả dụng" <?= $status == 'Khả dụng' ? 'selected' : '' ?>>Khả dụng</option>
                            <option value="Không khả dụng" <?= $status == 'Không khả dụng' ? 'selected' : '' ?>>Không khả dụng</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Ảnh dịch vụ</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <div class="action-buttons">
                        <a href="admin.php?tab=services" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Display alert messages
        function showAlert(message, type = 'danger') {
            const alertBox = document.getElementById('alert-box');
            alertBox.className = `alert alert-${type} alert-box`;
            alertBox.textContent = message;
            alertBox.classList.add('fade-alert-show');

            setTimeout(() => {
                alertBox.classList.remove('fade-alert-show');
                alertBox.classList.add('fade-alert-hide');
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

            input.nextElementSibling.textContent = fileCount > 0 ? `Đã chọn ${fileCount} ảnh` : 'Chọn thêm ảnh phụ...';

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
                        img.alt = `Ảnh phụ mới ${i+1}`;
                        img.className = 'additional-preview-image';

                        imgContainer.appendChild(img);
                        previewContainer.appendChild(imgContainer);
                    }

                    reader.readAsDataURL(file);
                }

                preview.appendChild(previewContainer);
            } else {
                preview.innerHTML = '';
            }
        }

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            // Show error message if exists
            <?php if (!empty($error)): ?>
                showAlert('<?= addslashes($error) ?>', 'danger');
            <?php endif; ?>
        });
    </script>
</body>

</html>