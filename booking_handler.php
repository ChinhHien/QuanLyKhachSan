<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Employee') {
    header('Location: index.php');
    exit();
}

// Xử lý tạo đặt phòng mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_booking'])) {
    $customer_id = $_POST['customer_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $deposit = $_POST['deposit'] ?? 0;
    $note = $_POST['note'] ?? '';
    $discount_code = $_POST['discount_code'] ?? null;
    
    // Tính tổng tiền
    $room_query = "SELECT rt.price_per_hour, rt.price_per_day FROM rooms r 
                   JOIN room_types rt ON r.type_id = rt.id WHERE r.id = ?";
    $stmt = $conn->prepare($room_query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room_result = $stmt->get_result();
    $room_data = $room_result->fetch_assoc();
    
    $checkin_time = new DateTime($check_in);
    $checkout_time = new DateTime($check_out);
    $diff = $checkout_time->diff($checkin_time);
    
    // Tính theo ngày nếu >= 1 ngày, ngược lại tính theo giờ
    if ($diff->days >= 1) {
        $total_price = $diff->days * $room_data['price_per_day'];
    } else {
        $hours = $diff->h + ($diff->days * 24);
        $total_price = $hours * $room_data['price_per_hour'];
    }
    
    // Áp dụng discount nếu có
    if ($discount_code) {
        $discount_query = "SELECT discount_percent FROM discounts WHERE code = ? AND expiry_date >= CURDATE()";
        $stmt = $conn->prepare($discount_query);
        $stmt->bind_param("s", $discount_code);
        $stmt->execute();
        $discount_result = $stmt->get_result();
        if ($discount_result->num_rows > 0) {
            $discount_data = $discount_result->fetch_assoc();
            $total_price = $total_price * (100 - $discount_data['discount_percent']) / 100;
        }
    }
    
    // Tạo đơn đặt phòng
    $insert_query = "INSERT INTO contacts (customer_id, employee_id, room_id, total_price, deposit, note, discount_code, expected_check_in, expected_check_out, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Đã xác nhận')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiidissss", $customer_id, $_SESSION['id'], $room_id, $total_price, $deposit, $note, $discount_code, $check_in, $check_out);
    
    if ($stmt->execute()) {
        // Cập nhật trạng thái phòng
        $update_room = "UPDATE rooms SET status = 'Đã đặt' WHERE id = ?";
        $stmt2 = $conn->prepare($update_room);
        $stmt2->bind_param("i", $room_id);
        $stmt2->execute();
        
        header('Location: employee.php?tab=booking&message=booking_created');
    } else {
        header('Location: employee.php?tab=booking&error=booking_failed');
    }
    exit();
}

// Xử lý tạo khách hàng mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_customer'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $cccd = $_POST['cccd'] ?? '';
    
    // Mật khẩu mặc định
    $default_password = '123456';
    
    $insert_customer = "INSERT INTO users (cccd, username, password, email, phone, address, gender, birthdate, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Customer')";
    $stmt = $conn->prepare($insert_customer);
    $stmt->bind_param("ssssssss", $cccd, $username, $default_password, $email, $phone, $address, $gender, $birthdate);
    
    if ($stmt->execute()) {
        $customer_id = $conn->insert_id;
        echo json_encode(['success' => true, 'customer_id' => $customer_id, 'customer_name' => $username]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể tạo khách hàng']);
    }
    exit();
}

// API lấy phòng khả dụng
if (isset($_GET['get_available_rooms'])) {
    $room_type_id = $_GET['room_type_id'];
    $check_in = $_GET['check_in'];
    $check_out = $_GET['check_out'];
    
    $query = "SELECT r.id, r.name, r.status 
              FROM rooms r 
              WHERE r.type_id = ? 
              AND r.status = 'Còn trống'
              AND r.id NOT IN (
                  SELECT c.room_id FROM contacts c 
                  WHERE c.status IN ('Chờ xác nhận', 'Đã xác nhận') 
                  AND (
                      (c.expected_check_in <= ? AND c.expected_check_out > ?) OR
                      (c.expected_check_in < ? AND c.expected_check_out >= ?) OR
                      (c.expected_check_in >= ? AND c.expected_check_out <= ?)
                  )
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssss", $room_type_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    
    echo json_encode($rooms);
    exit();
}

// API tìm kiếm khách hàng
if (isset($_GET['search_customer'])) {
    $search = '%' . $_GET['search'] . '%';
    
    $query = "SELECT id, username, email, phone FROM users WHERE role = 'Customer' AND (username LIKE ? OR email LIKE ? OR phone LIKE ?) LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    echo json_encode($customers);
    exit();
}
?>