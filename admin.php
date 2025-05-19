<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {

    header("Location: index.php");
    exit();
}

$admin = $result->fetch_assoc();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'account';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (empty($current_password)) {
        header('Location: admin.php?tab=account&error=' . urlencode('Vui lòng nhập mật khẩu hiện tại để xác thực.'));
        exit();
    } elseif ($current_password !== $admin['password']) {
        header('Location: admin.php?tab=account&error=' . urlencode('Mật khẩu hiện tại không chính xác.'));
        exit();
    }

    $update_query = "UPDATE users SET email = ?";
    $params = array($email);
    $types = "s";

    if (!empty($new_password)) {
        $update_query .= ", password = ?";
        $params[] = $new_password;
        $types .= "s";
    }

    $update_query .= " WHERE id = ?";
    $params[] = $_SESSION['id'];
    $types .= "i";

    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param($types, ...$params);

    if ($update_stmt->execute()) {
        header('Location: admin.php?tab=account&message=' . urlencode('Thông tin tài khoản đã được cập nhật.'));
        exit();
    } else {
        header('Location: admin.php?tab=account&error=' . urlencode('Có lỗi xảy ra: ' . $conn->error));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <script src="https://kit.fontawesome.com/d2a571ec6b.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/Info.css">
    <script src="js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php
    if (isset($_GET['message'])) {
        echo '<div id="alert-box" class="alert-custom alert-success-custom">' . htmlspecialchars(($_GET['message'])) . '</div>';
    }
    ?>

    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary d-md-none mobile-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-store-alt me-2"></i>
                <span>Admin</span>
            </div>

            <div class="list-group">
                <a href="#manage-account" class="list-group-item list-group-item-action <?php echo ($active_tab == 'account') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-box"></i>
                    <span>Tài khoản</span>
                </a>
                <a href="#manage-rooms" class="list-group-item list-group-item-action <?php echo ($active_tab == 'rooms') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-box"></i>
                    <span>Quản lý phòng</span>
                </a>
                <a href="#manage-types" class="list-group-item list-group-item-action <?php echo ($active_tab == 'room_types') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-users"></i>
                    <span>Quản lý loại phòng</span>
                </a>
                <a href="#manage-services" class="list-group-item list-group-item-action <?php echo ($active_tab == 'services') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-users"></i>
                    <span>Quản lý dịch vụ</span>
                </a>
                <a href="#manage-contacts" class="list-group-item list-group-item-action <?php echo ($active_tab == 'contacts') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Quản lý đơn đặt phòng</span>
                </a>
                <a href="#manage-employees" class="list-group-item list-group-item-action <?php echo ($active_tab == 'employees') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Quản lý nhân viên</span>
                </a>
                <a href="#manage-customers" class="list-group-item list-group-item-action <?php echo ($active_tab == 'customers') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Quản lý khách hàng</span>
                </a>
                <a href="#manage-revenue" class="list-group-item list-group-item-action <?php echo ($active_tab == 'revenue') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-chart-line"></i>
                    <span>Quản lý doanh thu</span>
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
                    <!-- Quản lý tài khoản -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'account') ? 'show active' : ''; ?>" id="manage-account">
                        <div class="page-header">
                            <h3>Thông tin tài khoản</h3>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data" class="row">
                                    <div class="col-md-8">
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Tên</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>">
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
                                                <button type="submit" name="update_account" class="btn btn-primary-dashboard">
                                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý phòng -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'rooms') ? 'show active' : ''; ?>" id="manage-rooms">
                        <div class="page-header">
                            <h3>Quản lý phòng</h3>
                            <!-- Tạo trang thêm phòng -->
                            <a href="add_room.php" class="btn btn-success-dashboard">
                                <i class="fas fa-plus me-1"></i>Thêm phòng
                            </a>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="rooms">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" name="room_search" placeholder="Tìm theo tên phòng..." value="<?php echo isset($_GET['room_search']) ? htmlspecialchars($_GET['room_search']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="room_id" placeholder="Tìm theo ID..." value="<?php echo isset($_GET['room_id']) ? htmlspecialchars($_GET['room_id']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=rooms" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <th>ID</th>
                                            <th>Tên phòng</th>
                                            <th>Loại phòng</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT rooms.*, room_types.typename FROM rooms 
                                            LEFT JOIN room_types ON rooms.type_id = room_types.id 
                                            WHERE 1=1";
                                            $params = [];

                                            if (isset($_GET['room_search']) && !empty($_GET['room_search'])) {
                                                $search = '%' . $_GET['room_search'] . '%';
                                                $query .= " AND name LIKE ?";
                                                $params[] = $search;
                                            }

                                            if (isset($_GET['room_id']) && !empty($_GET['room_id'])) {
                                                $query .= " AND id = ?";
                                                $params[] = $_GET['room_id'];
                                            }

                                            if (isset($_GET['type_id']) && !empty($_GET['type_id'])) {
                                                $query .= " AND type_id = ?";
                                                $params[] = $_GET['type_id'];
                                            }

                                            $stmt = $conn->prepare($query);

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($room = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($room['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($room['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($room['typename']) . "</td>";
                                                echo "<td>" . htmlspecialchars($room['status']) . "</td>";
                                                echo "<td>
                                                        <a href='edit_room.php?id=" . $room['id'] . "' class='btn btn-primary-dashboard btn-sm'>
                                                            <i class='fas fa-edit me-1'></i>Sửa
                                                        </a>
                                                        <a href='delete_room.php?id=" . $room['id'] . "' class='btn btn-danger-dashboard btn-sm ms-1' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này không?\")'>
                                                            <i class='fas fa-trash-alt me-1'></i>Xóa
                                                        </a>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý loại phòng -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'room_types') ? 'show active' : ''; ?>" id="manage-types">
                        <div class="page-header">
                            <h3>Quản lý loại phòng</h3>
                            <!-- Tạo trang thêm loại phòng -->
                            <a href="add_room_type.php" class="btn btn-success-dashboard">
                                <i class="fas fa-plus me-1"></i>Thêm loại phòng
                            </a>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="room_types">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" name="type_search" placeholder="Tìm theo tên loại phòng..." value="<?php echo isset($_GET['type_search']) ? htmlspecialchars($_GET['type_search']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="type_id" placeholder="Tìm theo ID..." value="<?php echo isset($_GET['type_id']) ? htmlspecialchars($_GET['type_id']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=room_types" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <th>ID</th>
                                            <th>Tên loại phòng</th>
                                            <th>Giá theo giờ</th>
                                            <th>Giá theo ngày</th>
                                            <th>Số người tối đa</th>
                                            <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM room_types WHERE 1=1";
                                            $params = [];

                                            if (isset($_GET['type_search']) && !empty($_GET['type_search'])) {
                                                $search = '%' . $_GET['type_search'] . '%';
                                                $query .= " AND name LIKE ?";
                                                $params[] = $search;
                                            }

                                            if (isset($_GET['type_id']) && !empty($_GET['type_id'])) {
                                                $query .= " AND id = ?";
                                                $params[] = $_GET['type_id'];
                                            }

                                            $stmt = $conn->prepare($query);

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($type = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($type['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($type['typename']) . "</td>";
                                                echo "<td>" . htmlspecialchars(number_format($type['price_per_hour'], 0, ',', '.')) . "₫</td>";
                                                echo "<td>" . htmlspecialchars(number_format($type['price_per_day'], 0, ',', '.')) . "₫</td>";
                                                echo "<td>" . htmlspecialchars($type['max_amounts']) . "</td>";
                                                echo "<td>
                                                        <a href='edit_type.php?id=" . $type['id'] . "' class='btn btn-primary-dashboard btn-sm'>
                                                            <i class='fas fa-edit me-1'></i>Sửa
                                                        </a>
                                                        <a href='delete_type.php?id=" . $type['id'] . "' class='btn btn-danger-dashboard btn-sm ms-1' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này không?\")'>
                                                            <i class='fas fa-trash-alt me-1'></i>Xóa
                                                        </a>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý dịch vụ -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'services') ? 'show active' : ''; ?>" id="manage-services">
                        <div class="page-header">
                            <h3>Quản lý dịch vụ</h3>
                            <!-- Tạo trang thêm dịch vụ -->
                            <a href="add_service.php" class="btn btn-success-dashboard">
                                <i class="fas fa-plus me-1"></i>Thêm dịch vụ
                            </a>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="services">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" name="service_search" placeholder="Tìm theo tên dịch vụ..." value="<?php echo isset($_GET['service_search']) ? htmlspecialchars($_GET['service_search']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="type_id" placeholder="Tìm theo ID..." value="<?php echo isset($_GET['type_id']) ? htmlspecialchars($_GET['type_id']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=services" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <th>ID</th>
                                            <th>Tên dịch vụ</th>
                                            <th>Giá</th>
                                            <th>Đơn vị tính</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM service WHERE 1=1";
                                            $params = [];

                                            if (isset($_GET['service_search']) && !empty($_GET['service_search'])) {
                                                $search = '%' . $_GET['service_search'] . '%';
                                                $query .= " AND name LIKE ?";
                                                $params[] = $search;
                                            }

                                            if (isset($_GET['service_id']) && !empty($_GET['service_id'])) {
                                                $query .= " AND id = ?";
                                                $params[] = $_GET['service_id'];
                                            }

                                            $stmt = $conn->prepare($query);

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($service = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($service['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($service['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars(number_format($service['price'], 0, ',', '.')) . "₫</td>";
                                                echo "<td>" . htmlspecialchars($service['unit']) . "</td>";
                                                echo "<td>" . htmlspecialchars($service['status']) . "</td>";
                                                echo "<td>
                                                        <a href='edit_type.php?id=" . $service['id'] . "' class='btn btn-primary-dashboard btn-sm'>
                                                            <i class='fas fa-edit me-1'></i>Sửa
                                                        </a>
                                                        <a href='delete_type.php?id=" . $service['id'] . "' class='btn btn-danger-dashboard btn-sm ms-1' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này không?\")'>
                                                            <i class='fas fa-trash-alt me-1'></i>Xóa
                                                        </a>
                                                    </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý đơn đặt phòng -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'contacts') ? 'show active' : ''; ?>" id="manage-contacts">
                        <div class="page-header">
                            <h3>Quản lý đơn đặt phòng</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="contacts">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="order_id" placeholder="Tìm theo mã đơn hàng..." value="<?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="customer_name" placeholder="Tìm theo tên khách hàng..." value="<?php echo isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" name="order_date" value="<?php echo isset($_GET['order_date']) ? htmlspecialchars($_GET['order_date']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=contacts" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap">
                                            <?php
                                            $statuses = ['Chờ xác nhận', 'Đã xác nhận', 'Hoàn thành', 'Đã hủy'];
                                            $statusIcons = ['clock', 'check', 'check-circle', 'times-circle'];
                                            $statusClasses = ['warning', 'info', 'success', 'danger'];
                                            foreach ($statuses as $index => $status) {
                                                $checked = (isset($_GET['status']) && in_array($status, $_GET['status'])) ? 'checked' : '';
                                                echo '<div class="form-check me-4">
                                    <input class="form-check-input" type="checkbox" name="status[]" value="' . $status . '" id="status' . $index . '" ' . $checked . '>
                                    <label class="form-check-label" for="status' . $index . '">
                                        <span class="badge badge-' . $statusClasses[$index] . '"><i class="fas fa-' . $statusIcons[$index] . ' me-1"></i>' . $status . '</span>
                                    </label>
                                  </div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Bảng kết quả -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn hàng</th>
                                                <th>Tên khách hàng</th>
                                                <th>Tổng tiền</th>
                                                <th>Ngày tạo</th>
                                                <th>Trạng thái</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "
                            SELECT contacts.id, users.username, contacts.total_price, contacts.order_date, contacts.status
                            FROM contacts
                            INNER JOIN users ON contacts.customer_id = users.id
                            WHERE 1=1
                        ";
                                            $params = [];

                                            if (!empty($_GET['order_id'])) {
                                                $query .= " AND contacts.id = ?";
                                                $params[] = $_GET['order_id'];
                                            }

                                            if (!empty($_GET['customer_name'])) {
                                                $query .= " AND users.username LIKE ?";
                                                $params[] = '%' . $_GET['customer_name'] . '%';
                                            }

                                            if (!empty($_GET['order_date'])) {
                                                $query .= " AND DATE(contacts.order_date) = ?";
                                                $params[] = $_GET['order_date'];
                                            }

                                            if (!empty($_GET['status'])) {
                                                $placeholders = implode(',', array_fill(0, count($_GET['status']), '?'));
                                                $query .= " AND contacts.status IN ($placeholders)";
                                                $params = array_merge($params, $_GET['status']);
                                            }

                                            $query .= " ORDER BY contacts.order_date DESC";

                                            $stmt = $conn->prepare($query);
                                            if ($stmt === false) {
                                                die("Lỗi truy vấn: " . $conn->error);
                                            }

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($order = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>#" . htmlspecialchars($order['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($order['username']) . "</td>";
                                                echo "<td>" . number_format($order['total_price'], 0, ',', '.') . "₫</td>";
                                                echo "<td>" . date('d/m/Y H:i', strtotime($order['order_date'])) . "</td>";

                                                // Trạng thái
                                                $badge = "";
                                                switch ($order['status']) {
                                                    case 'Chờ xác nhận':
                                                        $badge = '<span class="badge badge-warning"><i class="fas fa-clock me-1"></i>Chờ xác nhận</span>';
                                                        break;
                                                    case 'Đã xác nhận':
                                                        $badge = '<span class="badge badge-info"><i class="fas fa-check me-1"></i>Đã xác nhận</span>';
                                                        break;
                                                    case 'Hoàn thành':
                                                        $badge = '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Hoàn thành</span>';
                                                        break;
                                                    case 'Đã hủy':
                                                        $badge = '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>Đã hủy</span>';
                                                        break;
                                                }
                                                echo "<td>$badge</td>";

                                                // Hành động
                                                echo "<td>";
                                                echo "<a href='order_details.php?id={$order['id']}&source_tab=contacts' class='btn btn-info-dashboard btn-sm me-1'>
                                    <i class='fas fa-eye me-1'></i>Chi tiết
                                  </a>";
                                                switch ($order['status']) {
                                                    case 'Chờ xác nhận':
                                                        echo "<form action='order_actions.php' method='POST' style='display:inline-block;'>
                                            <input type='hidden' name='confirm_id' value='{$order['id']}'>
                                            <button type='submit' name='confirm_order' class='btn btn-success-dashboard btn-sm' onclick='return confirm(\"Xác nhận thao tác?\")'>
                                                <i class='fas fa-check me-1'></i>Xác nhận
                                            </button>
                                          </form>
                                          <form action='order_actions.php' method='POST' style='display:inline-block; margin-left: 5px;'>
                                            <input type='hidden' name='cancel_id' value='{$order['id']}'>
                                            <button type='submit' name='cancel_order' class='btn btn-danger-dashboard btn-sm' onclick='return confirm(\"Xác nhận thao tác?\")'>
                                                <i class='fas fa-times me-1'></i>Hủy
                                            </button>
                                          </form>";
                                                        break;
                                                    case 'Đã xác nhận':
                                                        echo "<form action='order_actions.php' method='POST' style='display:inline-block;'>
                                            <input type='hidden' name='complete_id' value='{$order['id']}'>
                                            <button type='submit' name='complete_order' class='btn btn-primary-dashboard btn-sm' onclick='return confirm(\"Xác nhận thao tác?\")'>
                                                <i class='fas fa-check-circle me-1'></i>Hoàn thành
                                            </button>
                                          </form>";
                                                        break;
                                                    case 'Hoàn thành':
                                                        echo "<button class='btn btn-secondary btn-sm' disabled>
                                            <i class='fas fa-check-double me-1'></i>Đã hoàn thành
                                          </button>";
                                                        break;
                                                    case 'Đã hủy':
                                                        echo "<button class='btn btn-dark btn-sm' disabled>
                                            <i class='fas fa-ban me-1'></i>Đã hủy
                                          </button>";
                                                        break;
                                                }
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý nhân viên -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'employees') ? 'show active' : ''; ?>" id="manage-employees">
                        <div class="page-header">
                            <h3>Quản lý nhân viên</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="employees">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="employee_name" placeholder="Tìm theo tên nhân viên..." value="<?php echo isset($_GET['employee_name']) ? htmlspecialchars($_GET['employee_name']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="text" class="form-control" name="employee_email" placeholder="Tìm theo email..." value="<?php echo isset($_GET['employee_email']) ? htmlspecialchars($_GET['employee_email']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=employees" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Bảng dữ liệu -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>Điện thoại</th>
                                                <th>Giới tính</th>
                                                <th>Ngày sinh</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT id, username, email, phone, gender, birthdate FROM users WHERE role = 'Employee'";
                                            $params = [];

                                            if (!empty($_GET['employee_name'])) {
                                                $query .= " AND username LIKE ?";
                                                $params[] = '%' . $_GET['employee_name'] . '%';
                                            }

                                            if (!empty($_GET['employee_email'])) {
                                                $query .= " AND email LIKE ?";
                                                $params[] = '%' . $_GET['employee_email'] . '%';
                                            }

                                            $query .= " ORDER BY created_at DESC";

                                            $stmt = $conn->prepare($query);
                                            if ($stmt === false) {
                                                die("Lỗi truy vấn: " . $conn->error);
                                            }

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            while ($employee = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($employee['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($employee['username']) . "</td>";
                                                echo "<td>" . htmlspecialchars($employee['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($employee['phone']) . "</td>";
                                                echo "<td>" . htmlspecialchars($employee['gender']) . "</td>";
                                                echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($employee['birthdate']))) . "</td>";
                                                echo "<td>
                                    <a href='employee_details.php?id={$employee['id']}' class='btn btn-info-dashboard btn-sm'>
                                        <i class='fas fa-eye me-1'></i>Chi tiết
                                    </a>
                                  </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý khách hàng -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'customers') ? 'show active' : ''; ?>" id="manage-customers">
                        <div class="page-header">
                            <h3>Quản lý khách hàng</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="customers">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" name="customer_name" placeholder="Tìm theo tên khách hàng..." value="<?php echo isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="text" class="form-control" name="customer_email" placeholder="Tìm theo email..." value="<?php echo isset($_GET['customer_email']) ? htmlspecialchars($_GET['customer_email']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="admin.php?tab=customers" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Bảng danh sách khách hàng -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>SĐT</th>
                                                <th>Giới tính</th>
                                                <th>Ngày sinh</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT id, username, email, phone, gender, birthdate FROM users WHERE role = 'Customer'";
                                            $params = [];

                                            if (!empty($_GET['customer_name'])) {
                                                $query .= " AND username LIKE ?";
                                                $params[] = '%' . $_GET['customer_name'] . '%';
                                            }

                                            if (!empty($_GET['customer_email'])) {
                                                $query .= " AND email LIKE ?";
                                                $params[] = '%' . $_GET['customer_email'] . '%';
                                            }

                                            $query .= " ORDER BY created_at DESC";

                                            $stmt = $conn->prepare($query);
                                            if ($stmt === false) {
                                                die("Lỗi truy vấn: " . $conn->error);
                                            }

                                            if (!empty($params)) {
                                                $types = str_repeat('s', count($params));
                                                $stmt->bind_param($types, ...$params);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            while ($customer = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($customer['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($customer['username']) . "</td>";
                                                echo "<td>" . htmlspecialchars($customer['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($customer['phone']) . "</td>";
                                                echo "<td>" . htmlspecialchars($customer['gender']) . "</td>";
                                                echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($customer['birthdate']))) . "</td>";
                                                echo "<td>
                                    <a href='customer_details.php?id={$customer['id']}' class='btn btn-info-dashboard btn-sm'>
                                        <i class='fas fa-eye me-1'></i>Chi tiết
                                    </a>
                                  </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý doanh thu -->
                    <div class="tab-pane fade <?php echo ($active_tab == 'revenue') ? 'show active' : ''; ?>" id="manage-revenue">
                        <div class="page-header">
                            <h3>Quản lý doanh thu</h3>
                            <div>
                                <button class="btn btn-info-dashboard" id="exportRevenue" onclick="window.location.href='admin.php?tab=revenue&message=Đang trong quá trình phát triển'">
                                    <i class="fas fa-file-export me-1"></i>Xuất báo cáo
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Summary Cards -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Doanh thu (Tháng này)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT SUM(total_price) AS monthly_revenue FROM contacts WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE()) AND status = 'Hoàn thành'");
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();
                                                    echo number_format($row['monthly_revenue'] ?: 0) . " VNĐ";
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Tổng doanh thu</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM contacts WHERE status = 'Hoàn thành'");
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();
                                                    echo number_format($row['total_revenue'] ?: 0) . " VNĐ";
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    Đơn hàng đã hoàn thành</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT COUNT(*) AS completed_orders FROM contacts WHERE status = 'Hoàn thành'");
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();
                                                    echo number_format($row['completed_orders'] ?: 0);
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Đơn hàng chờ xử lý</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT COUNT(*) AS pending_orders FROM contacts WHERE status = 'Chờ xác nhận'");
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();
                                                    echo number_format($row['pending_orders'] ?: 0);
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-comments fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                Báo cáo doanh thu theo ngày
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-filter me-1"></i>Lọc
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#">7 ngày gần đây</a></li>
                                        <li><a class="dropdown-item" href="#">30 ngày gần đây</a></li>
                                        <li><a class="dropdown-item" href="#">Tháng này</a></li>
                                        <li><a class="dropdown-item" href="#">Năm nay</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Ngày</th>
                                                <th>Số đơn hàng</th>
                                                <th>Doanh thu</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $conn->prepare("
                                                SELECT 
                                                    DATE(order_date) AS date, 
                                                    COUNT(*) as order_count,
                                                    SUM(total_price) AS revenue 
                                                FROM contacts 
                                                WHERE status = 'Hoàn thành'
                                                GROUP BY DATE(order_date)
                                                ORDER BY date DESC
                                                LIMIT 30
                                            ");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($revenue = $result->fetch_assoc()) {
                                                // Calculate if revenue is up or down compared to previous day
                                                $date = $revenue['date'];
                                                $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));

                                                $stmtPrev = $conn->prepare("
                                                    SELECT SUM(total_price) AS prev_revenue 
                                                    FROM contacts 
                                                    WHERE DATE(order_date) = ? AND status = 'Hoàn thành'
                                                ");
                                                $stmtPrev->bind_param("s", $prevDate);
                                                $stmtPrev->execute();
                                                $prevResult = $stmtPrev->get_result();
                                                $prevRevenue = $prevResult->fetch_assoc()['prev_revenue'] ?: 0;

                                                $status = '';
                                                if ($prevRevenue > 0) {
                                                    if ($revenue['revenue'] > $prevRevenue) {
                                                        $status = '<span class="text-success"><i class="fas fa-arrow-up"></i> ' . round(($revenue['revenue'] - $prevRevenue) / $prevRevenue * 100, 1) . '%</span>';
                                                    } else if ($revenue['revenue'] < $prevRevenue) {
                                                        $status = '<span class="text-danger"><i class="fas fa-arrow-down"></i> ' . round(($prevRevenue - $revenue['revenue']) / $prevRevenue * 100, 1) . '%</span>';
                                                    } else {
                                                        $status = '<span class="text-secondary"><i class="fas fa-equals"></i> 0%</span>';
                                                    }
                                                }

                                                echo "<tr>";
                                                echo "<td>" . date('d/m/Y', strtotime($revenue['date'])) . "</td>";
                                                echo "<td>" . number_format($revenue['order_count']) . "</td>";
                                                echo "<td>" . number_format($revenue['revenue']) . " VNĐ</td>";
                                                echo "<td>" . $status . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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

                    <!-- User Detail Modal -->
                    <div class="modal fade" id="userDetailModal" tabindex="-1" aria-labelledby="userDetailModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="userDetailModalLabel">Thông tin chi tiết khách hàng</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <img id="modal-avatar" src="" alt="User Avatar" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                        <h5 id="modal-username" class="mb-0"></h5>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 text-muted">Email:</div>
                                        <div class="col-md-8" id="modal-email"></div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 text-muted">Số điện thoại:</div>
                                        <div class="col-md-8" id="modal-phone"></div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 text-muted">Địa chỉ:</div>
                                        <div class="col-md-8" id="modal-address"></div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 text-muted">Giới tính:</div>
                                        <div class="col-md-8" id="modal-gender"></div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 text-muted">Ngày sinh:</div>
                                        <div class="col-md-8" id="modal-birthdate"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
</body>

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

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab) {
            const triggerEl = document.querySelector(`a[href="#manage-${tab}"]`);
            if (triggerEl) {
                const tabContent = new bootstrap.Tab(triggerEl);
                tabContent.show();
            }
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
    });

    window.addEventListener('DOMContentLoaded', () => {
        const alertBox = document.getElementById('alert-box');
        if (alertBox) {
            setTimeout(() => alertBox.style.opacity = 1, 100);
            setTimeout(() => alertBox.style.opacity = 0, 3000);
        }
    });

    document.querySelectorAll('.user-detail-link').forEach(link => {
        link.addEventListener('click', function() {
            document.getElementById('modal-username').textContent = this.dataset.username;
            document.getElementById('modal-email').textContent = this.dataset.email;
            document.getElementById('modal-phone').textContent = this.dataset.phone;
            document.getElementById('modal-address').textContent = this.dataset.address;
            document.getElementById('modal-gender').textContent = this.dataset.gender;
            document.getElementById('modal-birthdate').textContent = this.dataset.birthdate;
            document.getElementById('modal-avatar').src = this.dataset.avatar || 'assets/img/default-avatar.png';
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const listGroupItems = document.querySelectorAll(".list-group-item");

        listGroupItems.forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                const href = this.getAttribute("href");
                const tab = href.replace("#manage-", "");

                listGroupItems.forEach(i => i.classList.remove("active"));
                this.classList.add("active");

                const tabPane = document.querySelector(href);
                const allTabPanes = document.querySelectorAll(".tab-pane");
                allTabPanes.forEach(p => p.classList.remove("show", "active"));
                tabPane.classList.add("show", "active");

                const newUrl = window.location.pathname + '?tab=' + tab;
                window.history.replaceState(null, '', newUrl);
            });
        });
    });
</script>

</html>