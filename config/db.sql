CREATE DATABASE db;
USE db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cccd VARCHAR(12) UNIQUE,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    gender ENUM('Male', 'Female', 'Other'),
    birthdate DATE NOT NULL,
    avatar VARCHAR(255),
    role ENUM ('Customer', 'Employee', 'Admin', 'Sponsor'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pending_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    gender ENUM('Male', 'Female', 'Other'),
    birthdate DATE,
    phone VARCHAR(15),
    token VARCHAR(255),
    expires INT
);

CREATE TABLE user_rank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    rank VARCHAR(50),
    rank_point INT,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent DECIMAL(5, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    employee_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (employee_id) REFERENCES users(id)
);

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT,
    sender_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    typename VARCHAR(50),
    description VARCHAR(200),
    price_per_hour INT,
	price_per_day INT,
    max_amounts INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
	image VARCHAR(255)
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type_id INT,
    description TEXT,
    status ENUM('Còn trống', 'Đã đặt', 'Bảo trì') DEFAULT 'Còn trống',
    image VARCHAR(255),
    FOREIGN KEY (type_id) REFERENCES room_types(id)
);

CREATE TABLE room_imgs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT,
    image VARCHAR(255),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE discounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent DECIMAL(5, 2) NOT NULL,
    text TEXT,
    expiry_date DATE NOT NULL
);

CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE feedback_img (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_id INT,
    image VARCHAR(255),
    FOREIGN KEY (feedback_id) REFERENCES feedbacks(id) ON DELETE CASCADE
);

CREATE TABLE service (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    price INT,
    unit VARCHAR(50),
    status ENUM('Khả dụng', 'Không khả dụng'),
    image VARCHAR(255)
);

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    employee_id INT,
    room_id INT,
    total_price DECIMAL(10, 2) NOT NULL,
    deposit INT, 
    extra INT, 
    note TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    discount_code VARCHAR(50),
    status ENUM('Chờ xác nhận', 'Đã xác nhận', 'Hoàn thành', 'Đã hủy') DEFAULT 'Chờ xác nhận',
    expected_check_in DATETIME NOT NULL,
    expected_check_out DATETIME NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (discount_code) REFERENCES discounts(code),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (employee_id) REFERENCES users(id)
);

CREATE TABLE contact_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT,
    service_id INT,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (service_id) REFERENCES service(id),
    FOREIGN KEY (contact_id) REFERENCES contacts(id)
);

CREATE TABLE checkin_checkout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT,
    check_in DATETIME,
    check_out DATETIME,
    FOREIGN KEY (contact_id) REFERENCES contacts(id)
);

CREATE TABLE advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    image VARCHAR(255),
    text TEXT,
    FOREIGN KEY (id) REFERENCES users(id)
);

CREATE TABLE password_resets (
    email VARCHAR(255) UNIQUE,
    code VARCHAR(255),
    expires INT
);

-- DELIMITER $$

-- CREATE TRIGGER before_insert_room_image
-- BEFORE INSERT ON rooms
-- FOR EACH ROW
-- BEGIN
--     IF NEW.image IS NOT NULL AND NEW.image NOT LIKE 'assets/img/room_img/%' THEN
--         SET NEW.image = CONCAT('assets/img/room_img/', NEW.image);
--     END IF;
-- END $$

-- DELIMITER ;

-- Mặc định admin
INSERT INTO users (username, password, email, role) VALUES 
('admin', '123456', 'admin@gmail.com', 'Admin');

-- Mặc định user
INSERT INTO users (username, password, email, role) VALUES 
('Hien', '123456', 'Hien@gmail.com', 'Customer');

INSERT INTO room_types (typename, description, price_per_hour, price_per_day, max_amounts, rating, image) VALUES
('Phòng đơn', 'Phòng dành cho 1 người, đầy đủ tiện nghi cơ bản.', 50000, 300000, 1, 4, 'single_room.jpg'),
('Phòng đôi', 'Phòng dành cho 2 người, giường đôi, tiện nghi đầy đủ.', 80000, 500000, 2, 4.5,  'double_room.jpg'),
('Phòng gia đình', 'Phòng rộng rãi phù hợp cho gia đình, có thể ở 4 người.', 120000, 800000, 4, 5, 'family_room.jpg');

INSERT INTO rooms (name, type_id, description, status, image) VALUES
('Phòng 101', 1, 'Phòng đơn tầng 1, gần sảnh lễ tân.', 'Còn trống', 'room101.jpg'),
('Phòng 102', 2, 'Phòng đôi tầng 1, có cửa sổ hướng vườn.', 'Đã đặt', 'room102.jpg'),
('Phòng 201', 3, 'Phòng gia đình tầng 2, có ban công.', 'Còn trống', 'room201.jpg'),
('Phòng 202', 2, 'Phòng đôi tầng 2, gần thang máy.', 'Bảo trì', 'room202.jpg');

INSERT INTO service (name, description, price, unit, status, image) VALUES
('Giặt ủi', 'Dịch vụ giặt ủi quần áo trong ngày.', 20000, '1 kg', 'Khả dụng', 'laundry.jpg'),
('Bữa sáng', 'Bữa sáng tự chọn tại nhà hàng tầng trệt.', 50000, 'suất', 'Khả dụng', 'breakfast.jpg'),
('Thuê xe máy', 'Dịch vụ cho thuê xe máy trong ngày.', 120000, 'ngày', 'Khả dụng', 'motorbike.jpg'),
('Spa', 'Liệu trình spa thư giãn 60 phút.', 300000, 'suất', 'Không khả dụng', 'spa.jpg');


-- Dữ liệu mẫu cho bảng contacts
INSERT INTO contacts (
    customer_id, employee_id, room_id, total_price, deposit, extra, note, discount_code,
    expected_check_in, expected_check_out, status
)
VALUES 
(2, 1, 101, 600000, 100000, 50000, 'Khách yêu cầu phòng gần cửa sổ.', NULL, '2025-06-01 14:00:00', '2025-06-02 12:00:00', 'Chờ xác nhận'),

(2, 1, 102, 800000, 200000, 0, 'Đặt phòng cho dịp lễ.', NULL, '2025-06-03 15:00:00', '2025-06-04 11:00:00', 'Đã xác nhận'),

(2, 1, 201, 1500000, 300000, 100000, 'Khách cần thêm gối và mền.', NULL, '2025-06-05 13:00:00', '2025-06-06 10:00:00', 'Hoàn thành'),

(2, 1, 202, 500000, 100000, 0, 'Khách hủy sát giờ.', NULL, '2025-06-07 18:00:00', '2025-06-08 09:00:00', 'Đã hủy');
