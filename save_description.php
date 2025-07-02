<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$product_id = $_POST['product_id'] ?? '';
$description = $_POST['description'] ?? '';
$labels = $_POST['label'] ?? [];
$values = $_POST['value'] ?? [];

if (!$product_id) die("Thiếu ID sản phẩm.");

// Lưu mô tả: update nếu tồn tại, insert nếu chưa có
$stmt = $conn->prepare("REPLACE INTO product_descriptions (product_id, description) VALUES (?, ?)");
$stmt->bind_param("ss", $product_id, $description);
$stmt->execute();
$stmt->close();

// Xoá thông số kỹ thuật cũ
$del = $conn->prepare("DELETE FROM product_specs WHERE product_id = ?");
$del->bind_param("s", $product_id);
$del->execute();
$del->close();

// Thêm thông số mới
$insert = $conn->prepare("INSERT INTO product_specs (product_id, label, value) VALUES (?, ?, ?)");
for ($i = 0; $i < count($labels); $i++) {
  $label = trim($labels[$i]);
  $value = trim($values[$i]);
  if ($label && $value) {
    $insert->bind_param("sss", $product_id, $label, $value);
    $insert->execute();
  }
}
$insert->close();
$conn->close();

echo "<script>alert('Đã lưu mô tả và thông số thành công!'); window.location.href='edit_description.php?id=$product_id';</script>";
?>
