<?php
session_start();
include('config/db.php');

if (!isset($_GET['id'])) {
    header('Location: admin.php?tab=room_types');
    exit();
}

$room_type_id = $_GET['id'];

// Kiểm tra xem loại phòng có tồn tại và có đang được sử dụng không
$check_stmt = $conn->prepare("SELECT COUNT(*) as in_use FROM rooms WHERE type_id = ?");
$check_stmt->bind_param("i", $room_type_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: admin.php?tab=room_types&message=Loại phòng không tồn tại');
    exit();
}

$usage = $check_result->fetch_assoc();

if ($usage['in_use'] > 0) {
    header('Location: admin.php?tab=room_types&message=Không thể xóa loại phòng vì đang được sử dụng');
    exit();
}

// Lấy thông tin ảnh để xóa file vật lý nếu có
$get_image_stmt = $conn->prepare("SELECT image FROM room_types WHERE id = ?");
$get_image_stmt->bind_param("i", $room_type_id);
$get_image_stmt->execute();
$image_result = $get_image_stmt->get_result();

if ($image_data = $image_result->fetch_assoc()) {
    if ($image_data['image'] && file_exists($image_data['image'])) {
        unlink($image_data['image']);
    }
}

// Tiến hành xóa loại phòng
$delete_stmt = $conn->prepare("DELETE FROM room_types WHERE id = ?");
$delete_stmt->bind_param("i", $room_type_id);

if ($delete_stmt->execute()) {
    header('Location: admin.php?tab=room_types&message=Xóa loại phòng thành công');
    exit();
} else {
    echo 'Lỗi khi xóa loại phòng. Vui lòng thử lại.';
}
?>
