<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Lỗi: " . $conn->connect_error);

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
  $images[] = $row['image_path'];
}

echo json_encode($images);
