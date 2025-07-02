<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Lỗi kết nối']);
  exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
  echo json_encode(['error' => 'Thiếu ID']);
  exit;
}

// Lấy mô tả
$desc = '';
date_default_timezone_set("Asia/Ho_Chi_Minh");
$stmt1 = $conn->prepare("SELECT description FROM product_descriptions WHERE product_id = ?");
$stmt1->bind_param("s", $id);
$stmt1->execute();
$result1 = $stmt1->get_result();
if ($row = $result1->fetch_assoc()) {
  $desc = $row['description'];
}
$stmt1->close();

// Lấy specs
$specs = [];
$stmt2 = $conn->prepare("SELECT label, value FROM product_specs WHERE product_id = ?");
$stmt2->bind_param("s", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
  $specs[] = $row;
}
$stmt2->close();
$conn->close();

echo json_encode([
  'description' => $desc,
  'specs' => $specs
]);
?>
