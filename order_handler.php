<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Employee') {
    header('Location: index.php');
    exit();
}

// Xử lý check-in
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkin'])) {
    $contact_id = $_POST['contact_id'];
    $checkin_time = date('Y-m-d H:i:s');
    
    // Kiểm tra xem đã có record check-in chưa
    $check_query = "SELECT id FROM checkin_checkout WHERE contact_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $update_query = "UPDATE checkin_checkout SET check_in = ? WHERE contact_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $checkin_time, $contact_id);
    } else {
        // Insert new record
        $insert_query = "INSERT INTO checkin_checkout (contact_id, check_in) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("is", $contact_id, $checkin_time);
    }
    
    if ($stmt->execute()) {
        header('Location: employee.php?tab=contacts&message=checkin_success');
    } else {
        header('Location: employee.php?tab=contacts&error=checkin_failed');
    }
    exit();
}

// Xử lý check-out
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $contact_id = $_POST['contact_id'];
    $checkout_time = date('Y-m-d H:i:s');
    
    $update_query = "UPDATE checkin_checkout SET check_out = ? WHERE contact_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $checkout_time, $contact_id);
    
    if ($stmt->execute()) {
        header('Location: employee.php?tab=contacts&message=checkout_success');
    } else {
        header('Location: employee.php?tab=contacts&error=checkout_failed');
    }
    exit();
}

// Xử lý thêm dịch vụ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $contact_id = $_POST['contact_id'];
    $service_id = $_POST['service_id'];
    $quantity = $_POST['quantity'];
    
    // Lấy thông tin dịch vụ
    $service_query = "SELECT price FROM service WHERE id = ?";
    $stmt = $conn->prepare($service_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service_result = $stmt->get_result();
    $service_data = $service_result->fetch_assoc();
    
    $total_service_price = $service_data['price'] * $quantity;
    
    // Thêm vào contact_detail
    $insert_service = "INSERT INTO contact_detail (contact_id, service_id, quantity, total_price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_service);
    $stmt->bind_param("iiid", $contact_id, $service_id, $quantity, $total_service_price);
    
    if ($stmt->execute()) {
        // Cập nhật tổng tiền trong contacts
        $update_total = "UPDATE contacts SET extra = extra + ? WHERE id = ?";
        $stmt2 = $conn->prepare($update_total);
        $stmt2->bind_param("di", $total_service_price, $contact_id);
        $stmt2->execute();
        
        header('Location: employee.php?tab=contacts&message=service_added');
    } else {
        header('Location: employee.php?tab=contacts&error=service_failed');
    }
    exit();
}

// Xử lý cập nhật trạng thái đơn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $contact_id = $_POST['contact_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE contacts SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $contact_id);
    
    if ($stmt->execute()) {
        // Nếu hoàn thành hoặc hủy, cập nhật trạng thái phòng
        if ($status == 'Hoàn thành' || $status == 'Đã hủy') {
            $room_query = "SELECT room_id FROM contacts WHERE id = ?";
            $stmt2 = $conn->prepare($room_query);
            $stmt2->bind_param("i", $contact_id);
            $stmt2->execute();
            $room_result = $stmt2->get_result();
            $room_data = $room_result->fetch_assoc();
            
            $update_room = "UPDATE rooms SET status = 'Còn trống' WHERE id = ?";
            $stmt3 = $conn->prepare($update_room);
            $stmt3->bind_param("i", $room_data['room_id']);
            $stmt3->execute();
        }
        
        header('Location: employee.php?tab=contacts&message=status_updated');
    } else {
        header('Location: employee.php?tab=contacts&error=status_failed');
    }
    exit();
}
?>