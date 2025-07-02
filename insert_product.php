<?php
// 1. Kết nối CSDL
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'applestore'; // 👉 sửa lại nếu bạn dùng tên khác

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// 2. Nhận dữ liệu từ form
$name = $_POST['name'];
$price = $_POST['price'];
$discount_price = $_POST['discount_price'];
$category = $_POST['category'];
$color = $_POST['color'];
$storage = $_POST['storage'] ?? null;
$version = $_POST['version'] ?? null;
$quantity = 10;

// 3. Xử lý ảnh
$image_name = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $image_name = basename($_FILES['image']['name']);
    $target_path = $upload_dir . $image_name;
    move_uploaded_file($_FILES['image']['tmp_name'], $target_path);
}

// 4. Sinh mã ID sản phẩm (vd: IPH001)
$prefix = strtoupper(substr($category, 0, 3));
$prefix = str_pad($prefix, 3, 'X');

$sql_max = "SELECT MAX(CAST(SUBSTRING(id, 4) AS UNSIGNED)) AS max_num FROM products WHERE id LIKE '$prefix%'";
$result = $conn->query($sql_max);

$next_num = 1;
if ($result && $row = $result->fetch_assoc()) {
    $next_num = (int)$row['max_num'] + 1;
}
$id = $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);

// 5. Chèn vào bảng products
$stmt = $conn->prepare("INSERT INTO products 
    (id, name, version, price, discount_price, quantity, category, image, color, storage) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssddissss", 
    $id, $name, $version, $price, $discount_price, $quantity, $category, $image_name, $color, $storage
);

if ($stmt->execute()) {
    echo "<script>alert('Thêm sản phẩm thành công! Mã sản phẩm: $id'); window.location.href='form_add_product.html';</script>";
} else {
    echo "Lỗi khi thêm sản phẩm: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
