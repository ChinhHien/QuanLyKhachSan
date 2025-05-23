<?php
session_start();
include('config/db.php');

// Kiểm tra nếu không có ID phòng
if (!isset($_GET['id'])) {
    header('Location: admin.php'); 
    exit();
}

$room_id = $_GET['id'];

// Kiểm tra xem phòng đã được đặt chưa
$check_stmt = $conn->prepare("SELECT booked FROM rooms WHERE id = ?");
$check_stmt->bind_param("i", $room_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: admin.php?tab=rooms&message=Phòng không tồn tại');
    exit();
}

$room = $check_result->fetch_assoc();

// Nếu đã có lượt đặt thì không được phép xóa
if ($room['booked'] > 0) {
    header('Location: admin.php?tab=rooms&message=Không thể xóa phòng do đã có lượt đặt'); 
    exit();
}

// Nếu booked == 0, tiến hành xóa ảnh phụ trước
$img_stmt = $conn->prepare("SELECT image FROM room_img WHERE room_id = ?");
$img_stmt->bind_param("i", $room_id);
$img_stmt->execute();
$img_result = $img_stmt->get_result();

while ($img = $img_result->fetch_assoc()) {
    if (file_exists($img['image'])) {
        unlink($img['image']);
    }
}

// Xóa bản ghi ảnh phụ
$del_img_stmt = $conn->prepare("DELETE FROM room_img WHERE room_id = ?");
$del_img_stmt->bind_param("i", $room_id);
$del_img_stmt->execute();

// Lấy ảnh chính và xóa file nếu tồn tại
$main_img_stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
$main_img_stmt->bind_param("i", $room_id);
$main_img_stmt->execute();
$main_img_result = $main_img_stmt->get_result();
$main_img = $main_img_result->fetch_assoc();

if (!empty($main_img['image']) && file_exists($main_img['image'])) {
    unlink($main_img['image']);
}

// Xóa phòng
$stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);

if ($stmt->execute()) {
    header('Location: admin.php?tab=rooms&message=Xóa phòng thành công'); 
    exit();
} else {
    echo 'Lỗi khi xóa phòng. Vui lòng thử lại.';
}
?>
