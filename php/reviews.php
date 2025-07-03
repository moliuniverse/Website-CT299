<?php
/**
 * File này xử lý cả hai yêu cầu:
 * 1. Lấy danh sách đánh giá (GET request)
 * 2. Gửi một đánh giá mới (POST request)
 * * BẠN CẦN THỰC HIỆN 2 VIỆC:
 * 1. Thay đổi thông tin kết nối database ở dưới.
 * 2. Tạo bảng `reviews` trong database bằng câu lệnh SQL ở dưới.
 */

/*
-- Dán câu lệnh này vào tab SQL trong phpMyAdmin để tạo bảng `reviews`
CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT 'Khách',
  `user_avatar` varchar(255) DEFAULT 'default.png',
  `rating` int(1) NOT NULL,
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/


// ===== THAY ĐỔI CÁC THÔNG SỐ NÀY CHO PHÙ HỢP VỚI DATABASE CỦA BẠN =====
$host = 'localhost';
$db   = 'ten_database_cua_ban';
$user = 'ten_user_database';
$pass = 'mat_khau_database';
$charset = 'utf8mb4';
// ========================================================================


header('Content-Type: application/json');
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
    exit;
}


// Xử lý yêu cầu dựa trên phương thức (GET hoặc POST)
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // --- LẤY DANH SÁCH ĐÁNH GIÁ ---
    $product_id = isset($_GET['id']) ? $_GET['id'] : '';
    if (empty($product_id)) {
        echo json_encode([]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();
    echo json_encode($reviews);

} elseif ($method === 'POST') {
    // --- LƯU ĐÁNH GIÁ MỚI ---
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['product_id']) || empty($data['rating']) || empty($data['comment'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
        exit;
    }

    $product_id = $data['product_id'];
    $rating = (int)$data['rating'];
    $comment = htmlspecialchars($data['comment']);
    // Trong một ứng dụng thực tế, bạn sẽ lấy thông tin người dùng từ SESSION sau khi họ đăng nhập
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; 
    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Khách vãng lai';
    $user_avatar = isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'default.png';

    $sql = "INSERT INTO reviews (product_id, user_id, user_name, user_avatar, rating, comment) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt= $pdo->prepare($sql);
    
    if ($stmt->execute([$product_id, $user_id, $user_name, $user_avatar, $rating, $comment])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu đánh giá.']);
    }
}
?>