<?php
// Báo cho trình duyệt biết đây là file JSON
header('Content-Type: application/json');

// --- Cấu hình CSDL ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "applestore";

// --- Kết nối CSDL ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu kết nối thất bại, trả về lỗi và dừng lại
    http_response_code(500); // Báo lỗi server
    die(json_encode(["error" => "Lỗi kết nối CSDL: " . $conn->connect_error]));
}

// Lấy từ khóa tìm kiếm từ URL
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Nếu không có từ khóa, trả về một mảng JSON rỗng
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Chuẩn bị câu lệnh SQL để tìm kiếm
$searchTerm = "%" . $query . "%";
$sql = "SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR color LIKE ? OR storage LIKE ?";
$stmt = $conn->prepare($sql);

// Kiểm tra xem câu lệnh SQL có hợp lệ không
if ($stmt === false) {
    http_response_code(500);
    die(json_encode(["error" => "Lỗi cú pháp SQL: " . $conn->error]));
}

// Gắn tham số và thực thi
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Đóng kết nối
$stmt->close();
$conn->close();

// Trả về kết quả dưới dạng JSON
echo json_encode($products);
?>