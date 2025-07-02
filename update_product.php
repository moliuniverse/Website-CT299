<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nhận dữ liệu
$id = $_POST['id'];
$name = $_POST['name'];
$price = $_POST['price'];
$discount_price = $_POST['discount_price'];
$category = $_POST['category'];
$color = $_POST['color'];
$storage = $_POST['storage'] ?? null;
$version = $_POST['version'] ?? null;

// Xử lý ảnh
$image_sql = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $image_name = basename($_FILES['image']['name']);
    $target_path = $upload_dir . $image_name;
    move_uploaded_file($_FILES['image']['tmp_name'], $target_path);
    $image_sql = ", image = '$image_name'";
}

// Cập nhật dữ liệu
$sql = "UPDATE products SET 
        name = ?, version = ?, price = ?, discount_price = ?, category = ?, color = ?, storage = ? $image_sql 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

// Nếu có ảnh thì không dùng bind cho image
if (empty($image_sql)) {
    $stmt->bind_param("sssddsss", $name, $version, $price, $discount_price, $category, $color, $storage, $id);
} else {
    $stmt->bind_param("sssddsss", $name, $version, $price, $discount_price, $category, $color, $storage, $id);
}

if ($stmt->execute()) {
    echo "<script>alert('Cập nhật thành công!'); window.location.href='list_products.php';</script>";
} else {
    echo "Lỗi khi cập nhật: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
