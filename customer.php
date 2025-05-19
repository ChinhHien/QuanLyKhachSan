<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
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
    <title>HBH Store - Tài khoản</title>

    <script src="https://kit.fontawesome.com/d2a571ec6b.js" crossorigin="anonymous"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/Info.css">
</head>

<body>
    <?php
    if (isset($_GET['error'])) {
        echo '<div id="alert-box" class="alert-custom alert-danger-custom">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    ?>
    <?php
    if (isset($_GET['message'])) {
        echo '<div id="alert-box" class="alert-custom alert-success-custom">Lưu thay đổi thành công!</div>';
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
                <img src="<?= htmlspecialchars($user['avatar'] ?: 'assets/img/avatar.png') ?>" alt="User Avatar" class="profile-image">
                <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
                <div class="profile-role">Khách hàng</div>
            </div>

            <div class="list-group">
                <a href="#account-info" class="list-group-item list-group-item-action <?php echo ($active_tab == 'account-info') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-user"></i>
                    <span>Thông tin tài khoản</span>
                </a>
                <a href="#order-history" class="list-group-item list-group-item-action <?php echo ($active_tab == 'order-history') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-history"></i>
                    <span>Lịch sử mua hàng</span>
                </a>
                <a href="#order-status" class="list-group-item list-group-item-action <?php echo ($active_tab == 'order-status') ? 'active' : ''; ?>" data-bs-toggle="list">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Trạng thái đơn hàng</span>
                </a>
            </div>

            <div class="mt-auto d-flex justify-content-center">
                <a href="index.php" class="btn home-btn w-100">
                    <i class="fas fa-home me-2"></i>Trang chủ
                </a>
            </div>
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

                    <!-- Lịch sử mua hàng -->
                    <div class="tab-pane fade <?= $active_tab == 'order-history' ? 'show active' : '' ?>" id="order-history">
                        <div class="page-header">
                            <h3>Lịch sử mua hàng</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="order-history">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="order_id" placeholder="Tìm theo mã đơn hàng..."
                                                value="<?= isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" name="order_date" placeholder="Ngày tạo"
                                                value="<?= isset($_GET['order_date']) ? htmlspecialchars($_GET['order_date']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="customer.php?tab=order-history" class="btn btn-secondary">
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
                                            <tr>
                                                <th>Mã đơn hàng</th>
                                                <th>Tổng tiền</th>
                                                <th>Ngày tạo</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Xây dựng câu truy vấn với bộ lọc
                                            $query = "SELECT orders.id, orders.total_price, orders.order_date 
                                                        FROM orders WHERE user_id = ?";
                                            $params = [$_SESSION['id']];
                                            $types = "i";

                                            // Thêm điều kiện tìm kiếm theo mã đơn hàng
                                            if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                                                $query .= " AND orders.id = ?";
                                                $params[] = $_GET['order_id'];
                                                $types .= "i";
                                            }

                                            // Thêm điều kiện tìm kiếm theo ngày tạo
                                            if (isset($_GET['order_date']) && !empty($_GET['order_date'])) {
                                                $query .= " AND DATE(orders.order_date) = ?";
                                                $params[] = $_GET['order_date'];
                                                $types .= "s";
                                            }

                                            $query .= " ORDER BY order_date DESC";

                                            $stmt = $conn->prepare($query);
                                            if (count($params) > 0) {
                                                $stmt->bind_param($types, ...$params);
                                            }
                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            if ($result->num_rows > 0) {
                                                while ($orders = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>#" . htmlspecialchars($orders['id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars(number_format($orders['total_price'], 0, ',', '.')) . "₫</td>";
                                                    echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($orders['order_date']))) . "</td>";
                                                    echo "<td>";
                                                    echo "<a href='order_details.php?id=" . $orders['id'] . "&source_tab=order-history' class='btn btn-info-dashboard btn-sm me-1'>
                                                            <i class='fas fa-eye me-1'></i>Chi tiết
                                                        </a>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-center'>Bạn chưa có đơn hàng nào.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= $active_tab == 'order-status' ? 'show active' : '' ?>" id="order-status">
                        <div class="page-header">
                            <h3>Trạng thái đơn hàng</h3>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="" method="GET" class="row g-3 align-items-center">
                                    <input type="hidden" name="tab" value="order-status">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" name="order_id" placeholder="Mã đơn hàng..."
                                                value="<?= isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" name="order_date" placeholder="Ngày tạo"
                                                value="<?= isset($_GET['order_date']) ? htmlspecialchars($_GET['order_date']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button type="submit" class="btn btn-primary-dashboard me-2">
                                            <i class="fas fa-search me-1"></i>Tìm kiếm
                                        </button>
                                        <a href="customer.php?tab=order-status" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt me-1"></i>Đặt lại
                                        </a>
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="status[]" value="Chờ xác nhận" id="status-pending"
                                                <?php echo (!isset($_GET['status']) || (isset($_GET['status']) && in_array('Chờ xác nhận', $_GET['status']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status-pending">
                                                <span class="badge badge-warning"><i class="fas fa-clock me-1"></i>Chờ xác nhận</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="status[]" value="Đã xác nhận" id="status-confirmed"
                                                <?php echo (!isset($_GET['status']) || (isset($_GET['status']) && in_array('Đã xác nhận', $_GET['status']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status-confirmed">
                                                <span class="badge badge-info"><i class="fas fa-check me-1"></i>Đã xác nhận</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="status[]" value="Đã hủy" id="status-cancelled"
                                                <?php echo (isset($_GET['status']) && in_array('Đã hủy', $_GET['status'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status-cancelled">
                                                <span class="badge badge-danger"><i class="fas fa-times me-1"></i>Đã hủy</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="status[]" value="Hoàn thành" id="status-completed"
                                                <?php echo (isset($_GET['status']) && in_array('Hoàn thành', $_GET['status'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status-completed">
                                                <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Hoàn thành</span>
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <!-- Hiển thị thông tin đơn hàng -->
                                <div class="table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn hàng</th>
                                                <th>Tổng tiền</th>
                                                <th>Ngày tạo</th>
                                                <th>Trạng thái</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $statusFilter = [];
                                            if (isset($_GET['status']) && is_array($_GET['status'])) {
                                                $statusFilter = $_GET['status'];
                                            } else {
                                                $statusFilter = ['Chờ xác nhận', 'Đã xác nhận'];
                                            }

                                            $query = "SELECT orders.id, orders.total_price, orders.order_date, orders.status 
                                                     FROM orders WHERE user_id = ?";
                                            $params = [$user_id];
                                            $types = "i";

                                            // Thêm điều kiện lọc theo status
                                            if (!empty($statusFilter)) {
                                                $placeholders = str_repeat('?,', count($statusFilter) - 1) . '?';
                                                $query .= " AND status IN ($placeholders)";
                                                foreach ($statusFilter as $status) {
                                                    $params[] = $status;
                                                    $types .= "s";
                                                }
                                            }

                                            // Thêm điều kiện tìm kiếm theo mã đơn hàng
                                            if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                                                $query .= " AND orders.id = ?";
                                                $params[] = $_GET['order_id'];
                                                $types .= "i";
                                            }

                                            // Thêm điều kiện tìm kiếm theo ngày tạo
                                            if (isset($_GET['order_date']) && !empty($_GET['order_date'])) {
                                                $query .= " AND DATE(orders.order_date) = ?";
                                                $params[] = $_GET['order_date'];
                                                $types .= "s";
                                            }

                                            $query .= " ORDER BY order_date DESC";

                                            $stmt = $conn->prepare($query);
                                            if (count($params) > 0) {
                                                $stmt->bind_param($types, ...$params);
                                            }
                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            if ($result->num_rows > 0) {
                                                while ($orders = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>#" . htmlspecialchars($orders['id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($orders['order_date']))) . "</td>";
                                                    echo "<td>" . htmlspecialchars(number_format($orders['total_price'], 0, ',', '.')) . "₫</td>";

                                                    // Status Badge
                                                    $statusBadge = '';
                                                    switch ($orders['status']) {
                                                        case 'Chờ xác nhận':
                                                            $statusBadge = '<span class="badge badge-warning"><i class="fas fa-clock me-1"></i>Chờ xác nhận</span>';
                                                            break;
                                                        case 'Đã xác nhận':
                                                            $statusBadge = '<span class="badge badge-info"><i class="fas fa-check me-1"></i>Đã xác nhận</span>';
                                                            break;
                                                        case 'Đã hủy':
                                                            $statusBadge = '<span class="badge badge-danger"><i class="fas fa-times me-1"></i>Đã hủy</span>';
                                                            break;
                                                        case 'Hoàn thành':
                                                            $statusBadge = '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Hoàn thành</span>';
                                                            break;
                                                    }
                                                    echo "<td>" . $statusBadge . "</td>";

                                                    echo "<td>";
                                                    echo "<a href='order_details.php?id=" . $orders['id'] . "&source_tab=order-status' class='btn btn-info-dashboard btn-sm me-1'>
                                                        <i class='fas fa-eye me-1'></i>Chi tiết
                                                        </a>";

                                                    if ($orders['status'] == 'Chờ xác nhận') {
                                                        echo "<a href='cancel_order.php?id=" . $orders['id'] . "' class='btn btn-danger-dashboard btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn hủy đơn hàng này?\")'>
                                                            <i class='fas fa-times me-1'></i>Hủy đơn
                                                            </a>";
                                                    }

                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>Không tìm thấy đơn hàng phù hợp với bộ lọc.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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