<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xác nhận đơn hàng
    if (isset($_POST['confirm_contact']) && isset($_POST['confirm_id'])) {
        $confirm_id = $_POST['confirm_id'];
        $stmt = $conn->prepare("UPDATE contacts SET status = 'Đã xác nhận' WHERE id = ?");
        $stmt->bind_param("i", $confirm_id);
        $stmt->execute();
    }

    // Hoàn thành đơn hàng
    if (isset($_POST['complete_order']) && isset($_POST['complete_id'])) {
        $complete_id = $_POST['complete_id'];
        $stmt = $conn->prepare("UPDATE contacts SET status = 'Hoàn thành' WHERE id = ?");
        $stmt->bind_param("i", $complete_id);
        $stmt->execute();
    }

    // Hủy đơn đặt phòng
    if (isset($_POST['cancel_order']) && isset($_POST['cancel_id'])) {
        $cancel_id = $_POST['cancel_id'];
        $stmt = $conn->prepare("UPDATE contacts SET status = 'Đã hủy' WHERE id = ?");
        $stmt->bind_param("i", $cancel_id);
        $stmt->execute();
    }

    // Chuyển hướng lại trang chi tiết đơn hàng nếu có referer
    // $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    
    // if ($referer && strpos($referer, 'order_details.php') !== false) {
    //     $parts = parse_url($referer);
    //     parse_str($parts['query'], $query);
        
    //     if (isset($query['id'])) {
    //         header("Location: order_details.php?id=" . $query['id']);
    //         exit();
    //     }
    // }
    
    header("Location: admin.php?tab=contacts");
    exit();
}
?>
