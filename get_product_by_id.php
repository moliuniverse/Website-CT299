<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "applestore";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Kết nối thất bại"]);
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(["error" => "Thiếu ID"]);
    exit;
}

// Lấy name theo ID
$sql = "SELECT name FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "Không tìm thấy sản phẩm"]);
    exit;
}

$productName = $row['name'];

// Lấy tất cả phiên bản có cùng name
$sql2 = "SELECT * FROM products WHERE name = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $productName);
$stmt2->execute();
$result2 = $stmt2->get_result();

$variants = [];
while ($v = $result2->fetch_assoc()) {
    $variants[] = $v;
}

echo json_encode($variants);
$conn->close();
?>
