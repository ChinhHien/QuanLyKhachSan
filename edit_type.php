<?php
session_start();
include('config/db.php');

if (!isset($_GET['id'])) {
    header('Location: admin.php?tab=room_types');
    exit();
}

$room_type_id = $_GET['id'];

// Fetch existing room type data
$stmt = $conn->prepare("SELECT * FROM room_types WHERE id = ?");
$stmt->bind_param("i", $room_type_id);
$stmt->execute();
$room_type_result = $stmt->get_result();
$room_type = $room_type_result->fetch_assoc();

if (!$room_type) {
    header('Location: admin.php?tab=room_types');
    exit();
}

$error = '';
// Set default values
$typename = $room_type['typename'];
$price_per_hour = $room_type['price_per_hour'];
$price_per_day = $room_type['price_per_day'];
$max_amounts = $room_type['max_amounts'];
$description = $room_type['description'];
$image = $room_type['image'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typename = $_POST['typename'];
    $price_per_hour = $_POST['price_per_hour'];
    $price_per_day = $_POST['price_per_day'];
    $max_amounts = $_POST['max_amounts'];
    $description = $_POST['description'];
    $image = $room_type['image'];

    if (!is_numeric($price_per_hour) || $price_per_hour < 0) {
        $error = 'Giá theo giờ không hợp lệ.';
    } elseif (!is_numeric($price_per_day) || $price_per_day < 0) {
        $error = 'Giá theo ngày không hợp lệ.';
    } elseif (!is_numeric($max_amounts) || $max_amounts < 0) {
        $error = 'Số người tối đa không hợp lệ.';
    } elseif (empty($typename)) {
        $error = 'Vui lòng điền tên loại phòng.';
    } else {
        // Handle main image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($image_ext), $allowed_extensions)) {
                $image_new_name = "room_type_" . $room_type_id . "_main." . $image_ext;
                $image_upload_path = "assets/img/room_types/" . $image_new_name;

                // Delete old image if exists
                if ($room_type['image'] && file_exists($room_type['image']) && $room_type['image'] != $image_upload_path) {
                    unlink($room_type['image']);
                }

                if (move_uploaded_file($image_tmp, $image_upload_path)) {
                    $image = $image_upload_path;
                } else {
                    $error = 'Lỗi khi tải ảnh lên. Vui lòng thử lại.';
                }
            } else {
                $error = 'Chỉ chấp nhận các định dạng ảnh JPG, JPEG, PNG, GIF.';
            }
        }

        // Update room type data
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE room_types SET typename = ?, price_per_hour = ?, price_per_day = ?, max_amounts = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sdisssi", $typename, $price_per_hour, $price_per_day, $max_amounts, $description, $image, $room_type_id);

            if ($stmt->execute()) {
                header('Location: admin.php?tab=room_types&message=Chỉnh sửa loại phòng thành công');
                exit();
            } else {
                $error = 'Lỗi khi lưu thay đổi. Vui lòng thử lại.';
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
    <title>Sửa loại phòng</title>
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
                <h2><i class="fas fa-box-open mr-2"></i>Chỉnh sửa loại phòng</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" id="productForm">
                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3 class="form-section-title">Thông tin cơ bản</h3>

                    <div class="form-group mb-3">
                        <label for="typename">Tên loại phòng</label>
                        <input type="text" class="form-control" id="typename" name="typename" value="<?= htmlspecialchars($typename ?? '') ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="price_per_hour">Giá theo giờ (VNĐ)</label>
                            <input type="number" class="form-control" id="price_per_hour" name="price_per_hour" value="<?= htmlspecialchars($price_per_hour ?? '') ?>" required>
                        </div>
                        <div class="form-col">
                            <label for="price_per_day">Giá theo ngày (VNĐ)</label>
                            <input type="number" class="form-control" id="price_per_day" name="price_per_day" value="<?= htmlspecialchars($price_per_day ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="max_amounts">Số người tối đa</label>
                        <input type="number" class="form-control" id="max_amounts" name="max_amounts" value="<?= htmlspecialchars($max_amounts ?? '') ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Ảnh loại phòng</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <div class="action-buttons">
                        <a href="admin.php?tab=room_types" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Thêm loại phòng</button>
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