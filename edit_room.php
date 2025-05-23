<?php
session_start();
include('config/db.php');

// Kiểm tra xem có ID phòng không
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$room_id = $_GET['id'];

// Lấy thông tin phòng hiện tại
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_result = $stmt->get_result();
$room = $room_result->fetch_assoc();

if (!$room) {
    header('Location: admin.php?tab=rooms');
    exit();
}

$room_types = [];
$result = $conn->query("SELECT id, typename FROM room_types");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $room_types[] = $row;
    }
}

// Lấy các ảnh phụ của phòng
$images_stmt = $conn->prepare("SELECT * FROM room_imgs WHERE room_id = ?");
$images_stmt->bind_param("i", $room_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$additional_images = [];
while ($img = $images_result->fetch_assoc()) {
    $additional_images[] = $img;
}

$error = '';
// Gán giá trị mặc định ban đầu để giữ lại khi có lỗi
$name = $room['name'];
$description = $room['description'];
$image = $room['image'];
$type_id = $room['type_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type_id = $_POST['type_id'] ?? '';
    $image = $room['image'];

    if (empty($name) || empty($description) || empty($type_id)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        // Xử lý ảnh chính nếu có cập nhật
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
            $image_name = $_FILES['main_image']['name'];
            $image_tmp = $_FILES['main_image']['tmp_name'];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($image_ext), $allowed_extensions)) {
                $image_new_name = "room_" . $room_id . "_main." . $image_ext;
                $image_upload_path = "assets/img/room_img/" . $image_new_name;

                // Xoá ảnh cũ nếu có
                if ($room['image'] && file_exists($room['image']) && $room['image'] != $image_upload_path) {
                    unlink($room['image']);
                }

                if (move_uploaded_file($image_tmp, $image_upload_path)) {
                    $image = $image_upload_path;
                } else {
                    $error = 'Lỗi khi tải ảnh phòng lên. Vui lòng thử lại.';
                }
            } else {
                $error = 'Chỉ chấp nhận các định dạng ảnh JPG, JPEG, PNG, GIF.';
            }
        }

        // Nếu không có lỗi, cập nhật thông tin phòng
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE rooms SET name = ?, description = ?, image = ?, type_id = ? WHERE id = ?");
            $stmt->bind_param("sssii", $name, $description, $image, $type_id, $room_id);

            if ($stmt->execute()) {
                // Xử lý ảnh phụ mới nếu có
                if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                    $additional_image_count = count($_FILES['additional_images']['name']);

                    for ($i = 0; $i < $additional_image_count; $i++) {
                        if ($_FILES['additional_images']['error'][$i] == 0) {
                            $add_image_name = $_FILES['additional_images']['name'][$i];
                            $add_image_tmp = $_FILES['additional_images']['tmp_name'][$i];
                            $add_image_ext = pathinfo($add_image_name, PATHINFO_EXTENSION);
                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                            if (in_array(strtolower($add_image_ext), $allowed_extensions)) {
                                $add_image_new_name = "room_" . $room_id . "_" . uniqid() . "." . $add_image_ext;
                                $add_image_upload_path = "assets/img/room_img/" . $add_image_new_name;

                                if (move_uploaded_file($add_image_tmp, $add_image_upload_path)) {
                                    // Thêm ảnh phụ vào bảng room_img
                                    $img_stmt = $conn->prepare("INSERT INTO room_img (room_id, image) VALUES (?, ?)");
                                    $img_stmt->bind_param("is", $room_id, $add_image_upload_path);
                                    $img_stmt->execute();
                                }
                            }
                        }
                    }
                }

                // Xử lý xóa ảnh phụ nếu được chọn
                if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $img_id) {
                        // Lấy thông tin ảnh trước khi xóa
                        $get_img_stmt = $conn->prepare("SELECT image FROM room_img WHERE id = ? AND room_id = ?");
                        $get_img_stmt->bind_param("ii", $img_id, $room_id);
                        $get_img_stmt->execute();
                        $img_result = $get_img_stmt->get_result();

                        if ($img_data = $img_result->fetch_assoc()) {
                            // Xóa file ảnh
                            if (file_exists($img_data['image'])) {
                                unlink($img_data['image']);
                            }

                            // Xóa record từ database
                            $del_img_stmt = $conn->prepare("DELETE FROM room_img WHERE id = ? AND room_id = ?");
                            $del_img_stmt->bind_param("ii", $img_id, $room_id);
                            $del_img_stmt->execute();
                        }
                    }
                }

                header('Location: admin.php?tab=rooms&message=Chỉnh sửa phòng thành công');
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
    <title>Sửa phòng</title>
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
                <h2><i class="fas fa-box-open mr-2"></i>Chỉnh phòng</h2>
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