<?php 
// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nhận dữ liệu POST
$id = $_POST['id'];
$name = $_POST['name'];
$price = $_POST['price'];
$discount_price = $_POST['discount_price'];
$category = strtolower($_POST['category']);
$color = $_POST['color'];
$storage = $_POST['storage'] ?? null;
$version = $_POST['version'] ?? null;

// Biến ảnh
$image_name = null;

// Xử lý ảnh upload nếu có
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = '../images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $image_name = basename($_FILES['image']['name']);
    $target_path = $upload_dir . $image_name;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        die("Lỗi khi upload ảnh.");
    }
}

// Nếu có ảnh mới
if ($image_name) {
    $sql = "UPDATE products SET 
        name = ?, 
        version = ?, 
        price = ?, 
        discount_price = ?, 
        category = ?, 
        color = ?, 
        storage = ?, 
        image = ?
        WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Lỗi prepare: " . $conn->error);
    $stmt->bind_param(
        "sssssssss",
        $name,
        $version,
        $price,
        $discount_price,
        $category,
        $color,
        $storage,
        $image_name,
        $id
    );
} else {
    // Không có ảnh mới
    $sql = "UPDATE products SET 
        name = ?, 
        version = ?, 
        price = ?, 
        discount_price = ?, 
        category = ?, 
        color = ?, 
        storage = ?
        WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Lỗi prepare: " . $conn->error);
    $stmt->bind_param(
        "ssssssss",
        $name,
        $version,
        $price,
        $discount_price,
        $category,
        $color,
        $storage,
        $id
    );
}

// Thực thi
if ($stmt->execute()) {
    echo "<script>
        alert('Cập nhật thành công!');
        window.location.href = 'list_products.php';
    </script>";
} else {
    echo "Lỗi khi cập nhật: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
