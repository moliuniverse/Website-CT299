<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi kết nối CSDL']);
    exit;
}

// Lấy ID từ query string
$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode([]);
    exit;
}

// Lấy tên sản phẩm theo ID
$stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode([]);
    exit;
}

$name = $row['name'];

// Lấy tất cả biến thể có cùng tên
$stmt2 = $conn->prepare("SELECT * FROM products WHERE name = ?");
$stmt2->bind_param("s", $name);
$stmt2->execute();
$result2 = $stmt2->get_result();

$variants = [];
while ($row = $result2->fetch_assoc()) {
    $variants[] = $row;
}

echo json_encode($variants);
