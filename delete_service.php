<?php
session_start();
include('config/db.php');

if (!isset($_GET['id'])) {
    header('Location: admin.php?tab=services');
    exit();
}

$service_id = $_GET['id'];

// Kiểm tra dịch vụ có tồn tại không
$check_stmt = $conn->prepare("SELECT id FROM service WHERE id = ?");
$check_stmt->bind_param("i", $service_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: admin.php?tab=services&message=Dịch vụ không tồn tại');
    exit();
}

// Xóa dịch vụ
$stmt = $conn->prepare("DELETE FROM service WHERE id = ?");
$stmt->bind_param("i", $service_id);

if ($stmt->execute()) {
    header('Location: admin.php?tab=services&message=Xóa dịch vụ thành công');
    exit();
} else {
    echo 'Lỗi khi xóa dịch vụ. Vui lòng thử lại.';
}
?>
