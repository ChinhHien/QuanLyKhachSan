<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Employee') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['id'];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'account-info';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>

    <script src="https://kit.fontawesome.com/d2a571ec6b.js" crossorigin="anonymous"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/Info.css">
    <link rel="stylesheet" href="assets/css/employee_booking.css">
</head>

<body>
    <?php
    if (isset($_GET['error'])) {
        echo '<div id="alert-box" class="fade-alert alert-custom alert-danger-custom">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    ?>
    <?php
    if (isset($_GET['message'])) {
        echo '<div id="alert-box" class="fade-alert alert-custom alert-success-custom">Lưu thay đổi thành công!</div>';
    }
    ?>

    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary d-md-none mobile-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column" id="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-user-circle me-2"></i>
                <span>Tài khoản</span>
            </div>

            <div class="profile-section">
                <?php include('customer_info.php'); ?>
                <img src="<?= htmlspecialchars($user['avatar'] ?: 'assets/img/avatar.png') ?>" class="profile-image">
                <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
                <div class="profile-role">Nhân viên</div>
            </div>

            <div class="list-group">
                <a href="#account-info" class="list-group-item list-group-item-action <?php echo ($active_tab == 'account-info') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-user"></i>
                    <span>Thông tin tài khoản</span>
                </a>
                <a href="#booking" class="list-group-item list-group-item-action <?php echo ($active_tab == 'booking') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-ticket"></i>
                    <span>Đặt phòng</span>
                </a>
                <a href="#contacts" class="list-group-item list-group-item-action <?php echo ($active_tab == 'contacts') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-history"></i>
                    <span>Đơn đặt phòng</span>
                </a>
            </div>

            <div class="mt-auto d-flex justify-content-center"><a href='#' class="btn logout-btn w-100" data-bs-toggle='modal' data-bs-target='#confirmlogout'>
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a></div>
        </div>

        <!-- Content Area -->
        <div class="content-wrapper">
            <div class="container-fluid mx-auto" style="max-width: 1200px;">
                <div class="tab-content">
                    <!-- Thông tin tài khoản -->
                    <div class="tab-pane fade <?= $active_tab == 'account-info' ? 'show active' : '' ?>" id="account-info">
                        <div class="page-header">
                            <h3>Thông tin tài khoản</h3>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" class="row">
                                    <div class="col-md-8">
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Tên</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Số điện thoại</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Địa chỉ</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Giới tính</label>
                                            <div class="col-sm-9 d-flex align-items-center">
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" name="gender" id="male" value="Male" class="form-check-input" <?= ($user['gender'] == 'Male') ? 'checked' : '' ?>>
                                                    <label for="male" class="form-check-label">Nam</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" name="gender" id="female" value="Female" class="form-check-input" <?= ($user['gender'] == 'Female') ? 'checked' : '' ?>>
                                                    <label for="female" class="form-check-label">Nữ</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" name="gender" id="other" value="Other" class="form-check-input" <?= ($user['gender'] == 'Other') ? 'checked' : '' ?>>
                                                    <label for="other" class="form-check-label">Khác</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Ngày sinh</label>
                                            <div class="col-sm-9">
                                                <input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($user['birthdate']) ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Mật khẩu hiện tại<span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="password" name="current_password" class="form-control" placeholder="Nhập mật khẩu hiện tại để xác thực" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Mật khẩu mới</label>
                                            <div class="col-sm-9">
                                                <input type="password" name="new_password" class="form-control" placeholder="Để trống nếu không muốn đổi mật khẩu">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-9 offset-sm-3">
                                                <button type="submit" class="btn btn-primary-dashboard">
                                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 text-center">
                                        <div class="profile-picture-container mb-4">
                                            <img src="<?= htmlspecialchars($user['avatar'] ?: 'assets/img/avatar.png') ?>" alt="Profile Picture" class="rounded-circle profile-image mb-3">
                                            <div class="position-relative">
                                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Đặt phòng -->
                    <div class="tab-pane fade <?= $active_tab == 'booking' ? 'show active' : '' ?>" id="booking">
                        <div class="page-header">
                            <h3>Đặt phòng</h3>
                        </div>

                        <div class="booking-form">
                            <form id="createBookingForm" method="POST" action="booking_handler.php">
                                <!-- Phần chọn loại phòng -->
                                <div class="form-section">
                                    <h4 class="section-title"><i class="fas fa-bed"></i> Chọn loại phòng</h4>
                                    <select class="form-control" id="roomTypeSelect" name="room_type_id" required>
                                        <option value="">-- Chọn loại phòng --</option>
                                        <?php
                                        $room_types = $conn->query("SELECT * FROM room_types");
                                        while ($type = $room_types->fetch_assoc()) {
                                            echo '<option value="' . $type['id'] . '">' . $type['typename'] . ' - ' . number_format($type['price_per_day']) . '₫/đêm</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Phần chọn thời gian -->
                                <div class="form-section">
                                    <h4 class="section-title"><i class="fas fa-calendar-alt"></i> Chọn thời gian</h4>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Ngày nhận phòng</label>
                                            <input type="datetime-local" class="form-control" id="checkInDate" name="check_in" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Ngày trả phòng</label>
                                            <input type="datetime-local" class="form-control" id="checkOutDate" name="check_out" required>
                                        </div>
                                    </div>
                                    <button type="button" id="checkAvailabilityBtn" class="btn btn-primary-dashboard">
                                        <i class="fas fa-search"></i> Kiểm tra phòng trống
                                    </button>
                                </div>

                                <!-- Hiển thị phòng khả dụng -->
                                <div class="form-section" id="availableRoomsSection" style="display: none;">
                                    <h4 class="section-title"><i class="fas fa-door-open"></i> Phòng khả dụng</h4>
                                    <div class="room-grid" id="availableRoomsGrid">
                                        <!-- Phòng sẽ được load bằng AJAX -->
                                    </div>
                                </div>

                                <!-- Phần thông tin khách hàng -->
                                <div class="form-section" id="customerInfoSection" style="display: none;">
                                    <h4 class="section-title"><i class="fas fa-user"></i> Thông tin khách hàng</h4>

                                    <div class="customer-search mb-3">
                                        <label>Tìm kiếm khách hàng</label>
                                        <input type="text" class="form-control" id="customerSearch" placeholder="Nhập tên, email hoặc số điện thoại">
                                        <div class="customer-results" id="customerResults"></div>
                                    </div>

                                    <div id="selectedCustomerInfo" style="display: none;">
                                        <div class="alert alert-success">
                                            <strong>Khách hàng đã chọn:</strong>
                                            <span id="customerNameDisplay"></span>
                                            <input type="hidden" id="customerId" name="customer_id">
                                        </div>
                                    </div>

                                    <!-- Form tạo khách hàng mới (ẩn ban đầu) -->
                                    <div id="newCustomerForm" style="display: none;">
                                        <h5 class="mt-4">Thông tin khách hàng mới</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Họ và tên</label>
                                                <input type="text" class="form-control" id="newCustomerName" name="username" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Số CCCD</label>
                                                <input type="text" class="form-control" id="newCustomerCCCD" name="cccd">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="newCustomerEmail" name="email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Số điện thoại</label>
                                                <input type="tel" class="form-control" id="newCustomerPhone" name="phone" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label>Giới tính</label>
                                                <select class="form-control" id="newCustomerGender" name="gender" required>
                                                    <option value="Male">Nam</option>
                                                    <option value="Female">Nữ</option>
                                                    <option value="Other">Khác</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Ngày sinh</label>
                                                <input type="date" class="form-control" id="newCustomerBirthdate" name="birthdate" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Địa chỉ</label>
                                            <input type="text" class="form-control" id="newCustomerAddress" name="address">
                                        </div>
                                        <button type="button" id="createCustomerBtn" class="btn btn-primary-dashboard">
                                            <i class="fas fa-user-plus"></i> Tạo khách hàng mới
                                        </button>
                                    </div>
                                </div>

                                <!-- Phần thông tin thanh toán -->
                                <div class="form-section" id="paymentInfoSection" style="display: none;">
                                    <h4 class="section-title"><i class="fas fa-money-bill-wave"></i> Thông tin thanh toán</h4>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Tổng tiền</label>
                                            <input type="text" class="form-control" id="totalPriceDisplay" readonly>
                                            <input type="hidden" id="totalPrice" name="total_price">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Tiền đặt cọc</label>
                                            <input type="number" class="form-control" name="deposit" min="0" value="0">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label>Mã giảm giá (nếu có)</label>
                                        <input type="text" class="form-control" name="discount_code">
                                    </div>

                                    <div class="mb-3">
                                        <label>Ghi chú</label>
                                        <textarea class="form-control" name="note" rows="3"></textarea>
                                    </div>

                                    <input type="hidden" id="selectedRoomId" name="room_id">
                                    <input type="hidden" name="create_booking" value="1">

                                    <button type="submit" class="btn btn-success-dashboard">
                                        <i class="fas fa-check"></i> Xác nhận đặt phòng
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Đơn đặt phòng -->
                    <div class="tab-pane fade <?= $active_tab == 'contacts' ? 'show active' : '' ?>" id="contacts">
                        <div class="page-header">
                            <h3>Đơn đặt phòng</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="contacts">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="order_id" placeholder="Mã đơn hàng"
                                                value="<?= isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="customer_name" placeholder="Tên khách hàng"
                                                value="<?= isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-control" name="status">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="Chờ xác nhận" <?= (isset($_GET['status']) && $_GET['status'] == 'Chờ xác nhận') ? 'selected' : '' ?>>Chờ xác nhận</option>
                                            <option value="Đã xác nhận" <?= (isset($_GET['status']) && $_GET['status'] == 'Đã xác nhận') ? 'selected' : '' ?>>Đã xác nhận</option>
                                            <option value="Hoàn thành" <?= (isset($_GET['status']) && $_GET['status'] == 'Hoàn thành') ? 'selected' : '' ?>>Hoàn thành</option>
                                            <option value="Đã hủy" <?= (isset($_GET['status']) && $_GET['status'] == 'Đã hủy') ? 'selected' : '' ?>>Đã hủy</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary-dashboard w-100">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <?php
                                // Xây dựng câu truy vấn với bộ lọc
                                $query = "SELECT c.*, u.username as customer_name, r.name as room_name 
                      FROM contacts c
                      JOIN users u ON c.customer_id = u.id
                      JOIN rooms r ON c.room_id = r.id
                      WHERE 1=1";

                                $params = [];
                                $types = "";

                                // Thêm điều kiện tìm kiếm
                                if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                                    $query .= " AND c.id = ?";
                                    $params[] = $_GET['order_id'];
                                    $types .= "i";
                                }

                                if (isset($_GET['customer_name']) && !empty($_GET['customer_name'])) {
                                    $query .= " AND u.username LIKE ?";
                                    $params[] = '%' . $_GET['customer_name'] . '%';
                                    $types .= "s";
                                }

                                if (isset($_GET['status']) && !empty($_GET['status'])) {
                                    $query .= " AND c.status = ?";
                                    $params[] = $_GET['status'];
                                    $types .= "s";
                                }

                                $query .= " ORDER BY c.order_date DESC";

                                $stmt = $conn->prepare($query);
                                if (!empty($params)) {
                                    $stmt->bind_param($types, ...$params);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($contact = $result->fetch_assoc()) {
                                        $status_class = '';
                                        switch ($contact['status']) {
                                            case 'Chờ xác nhận':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'Đã xác nhận':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'Hoàn thành':
                                                $status_class = 'status-completed';
                                                break;
                                            case 'Đã hủy':
                                                $status_class = 'status-cancelled';
                                                break;
                                        }
                                ?>
                                        <div class="order-card mb-4">
                                            <div class="order-header">
                                                <div class="order-id">Đơn #<?= $contact['id'] ?></div>
                                                <div class="order-status <?= $status_class ?>"><?= $contact['status'] ?></div>
                                            </div>
                                            <div class="order-body">
                                                <div class="order-info">
                                                    <div class="info-group">
                                                        <div class="info-label">Khách hàng</div>
                                                        <div class="info-value"><?= htmlspecialchars($contact['customer_name']) ?></div>
                                                    </div>
                                                    <div class="info-group">
                                                        <div class="info-label">Phòng</div>
                                                        <div class="info-value"><?= htmlspecialchars($contact['room_name']) ?></div>
                                                    </div>
                                                    <div class="info-group">
                                                        <div class="info-label">Thời gian</div>
                                                        <div class="info-value">
                                                            <?= date('d/m/Y H:i', strtotime($contact['expected_check_in'])) ?> -
                                                            <?= date('d/m/Y H:i', strtotime($contact['expected_check_out'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="info-group">
                                                        <div class="info-label">Tổng tiền</div>
                                                        <div class="info-value"><?= number_format($contact['total_price'], 0, ',', '.') ?>₫</div>
                                                    </div>
                                                </div>

                                                <!-- Chi tiết dịch vụ -->
                                                <div class="service-section">
                                                    <h5>Dịch vụ đã sử dụng</h5>
                                                    <div class="service-list">
                                                        <?php
                                                        $service_query = "SELECT s.name, cd.quantity, cd.total_price 
                                                     FROM contact_detail cd
                                                     JOIN service s ON cd.service_id = s.id
                                                     WHERE cd.contact_id = ?";
                                                        $stmt2 = $conn->prepare($service_query);
                                                        $stmt2->bind_param("i", $contact['id']);
                                                        $stmt2->execute();
                                                        $service_result = $stmt2->get_result();

                                                        if ($service_result->num_rows > 0) {
                                                            while ($service = $service_result->fetch_assoc()) {
                                                                echo '<div class="service-item">';
                                                                echo '<div>' . htmlspecialchars($service['name']) . ' x' . $service['quantity'] . '</div>';
                                                                echo '<div>' . number_format($service['total_price'], 0, ',', '.') . '₫</div>';
                                                                echo '</div>';
                                                            }
                                                        } else {
                                                            echo '<p>Không có dịch vụ nào</p>';
                                                        }
                                                        ?>
                                                    </div>

                                                    <!-- Form thêm dịch vụ (chỉ hiển thị khi đơn chưa hoàn thành hoặc hủy) -->
                                                    <?php if ($contact['status'] == 'Đã xác nhận'): ?>
                                                        <form method="POST" action="order_handler.php" class="service-add-form">
                                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                            <div class="row">
                                                                <div class="col-md-5 mb-2">
                                                                    <select class="form-control" name="service_id" required>
                                                                        <option value="">Chọn dịch vụ</option>
                                                                        <?php
                                                                        $services = $conn->query("SELECT * FROM service WHERE status = 'Khả dụng'");
                                                                        while ($service = $services->fetch_assoc()) {
                                                                            echo '<option value="' . $service['id'] . '">' . $service['name'] . ' - ' . number_format($service['price'], 0, ',', '.') . '₫/' . $service['unit'] . '</option>';
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <input type="number" class="form-control" name="quantity" min="1" value="1" required>
                                                                </div>
                                                                <div class="col-md-4 mb-2">
                                                                    <button type="submit" name="add_service" class="btn btn-primary-dashboard w-100">
                                                                        <i class="fas fa-plus"></i> Thêm dịch vụ
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Nút thao tác -->
                                                <div class="action-buttons">
                                                    <?php if ($contact['status'] == 'Chờ xác nhận'): ?>
                                                        <form method="POST" action="order_handler.php" style="display: inline;">
                                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                            <input type="hidden" name="status" value="Đã xác nhận">
                                                            <button type="submit" name="update_status" class="btn-action btn-confirm">
                                                                <i class="fas fa-check"></i> Xác nhận
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="order_handler.php" style="display: inline;">
                                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                            <input type="hidden" name="status" value="Đã hủy">
                                                            <button type="submit" name="update_status" class="btn-action btn-cancel">
                                                                <i class="fas fa-times"></i> Hủy đơn
                                                            </button>
                                                        </form>
                                                    <?php elseif ($contact['status'] == 'Đã xác nhận'): ?>
                                                        <form method="POST" action="order_handler.php" style="display: inline;">
                                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                            <button type="submit" name="checkin" class="btn-action btn-checkin">
                                                                <i class="fas fa-sign-in-alt"></i> Check-in
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="order_handler.php" style="display: inline;">
                                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                            <input type="hidden" name="status" value="Hoàn thành">
                                                            <button type="submit" name="update_status" class="btn-action btn-complete">
                                                                <i class="fas fa-check-circle"></i> Hoàn thành
                                                            </button>
                                                        </form>
                                                    <?php elseif ($contact['status'] == 'Hoàn thành'): ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Đơn đã hoàn thành</span>
                                                    <?php elseif ($contact['status'] == 'Đã hủy'): ?>
                                                        <span class="text-danger"><i class="fas fa-ban"></i> Đơn đã hủy</span>
                                                    <?php endif; ?>

                                                    <!-- Nút xem chi tiết -->
                                                    <a href="contact_details.php?id=<?= $contact['id'] ?>" class="btn-action btn-info">
                                                        <i class="fas fa-info-circle"></i> Chi tiết
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo '<div class="alert alert-info">Không tìm thấy đơn đặt phòng nào.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Logout Modal -->
                    <div class="modal fade" id="confirmlogout" tabindex="-1" aria-labelledby="confirmLogoutLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmLogoutLabel">Xác nhận đăng xuất</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Bạn có chắc chắn muốn đăng xuất không?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="button" class="btn btn-danger" onclick="logoutUser()">Đăng xuất</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logoutUser() {
            fetch('logout.php', {
                    method: 'POST'
                })
                .then(response => response.text())
                .then(data => {
                    window.location.href = 'index.php';
                })
                .catch(error => console.error('Lỗi:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle alert fadeout
            const alertBox = document.getElementById('alert-box');
            if (alertBox) {
                setTimeout(() => alertBox.style.opacity = 1, 100);
                setTimeout(() => alertBox.style.opacity = 0, 3000);
            }

            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (sidebar.style.width === '0px' || sidebar.style.width === '') {
                        sidebar.style.width = '250px';
                    } else {
                        sidebar.style.width = '0px';
                    }
                });
            }

            // Active tab management
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');

            if (tab) {
                const triggerEl = document.querySelector(`a[href="#${tab}"]`);
                if (triggerEl) {
                    const tabContent = new bootstrap.Tab(triggerEl);
                    tabContent.show();
                }
            }

            // Handle tab navigation with URL updates
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    const href = this.getAttribute('href').substring(1);
                    const newUrl = window.location.pathname + '?tab=' + href;
                    window.history.replaceState(null, '', newUrl);
                });
            });

            // Auto-submit form when any checkbox changes
            const statusCheckboxes = document.querySelectorAll('input[name="status[]"]');
            statusCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });
        });
    </script>
</body>

</html>