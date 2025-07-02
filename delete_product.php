<?php
// 1. Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// 2. Lấy ID từ URL
$id = $_GET['id'] ?? '';
if (!$id) {
    die("Không có ID sản phẩm cần xoá.");
}

// 3. Xoá sản phẩm
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
    echo "<script>alert('Đã xoá sản phẩm $id thành công!'); window.location.href='list_products.php';</script>";
} else {
    echo "Lỗi khi xoá: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
