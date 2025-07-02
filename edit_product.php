<?php
// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy ID từ URL
$id = $_GET['id'] ?? '';
if (!$id) {
    die("Không có ID sản phẩm.");
}

// Lấy dữ liệu sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    die("Không tìm thấy sản phẩm.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa sản phẩm</title>
  <style>
    body { font-family: Arial; background: #f2f2f2; }
    form {
      max-width: 500px; margin: 50px auto; background: #fff;
      padding: 30px; border-radius: 10px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    input, select {
      width: 100%; margin-bottom: 15px; padding: 10px;
      font-size: 16px;
    }
    button {
      padding: 10px 20px; background: #0071e3; color: white;
      font-size: 16px; border: none; border-radius: 6px; cursor: pointer;
    }
    button:hover { background: #005bb5; }
    .hidden { display: none; }
  </style>
</head>
<body>

<form action="update_product.php" method="POST" enctype="multipart/form-data">
  <h2>✏️ Sửa sản phẩm</h2>
  <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">

  <input type="text" name="name" placeholder="Tên sản phẩm" value="<?= htmlspecialchars($product['name']) ?>" required>

  <input type="number" name="price" placeholder="Giá gốc" value="<?= $product['price'] ?>" required>
  <input type="number" name="discount_price" placeholder="Giá khuyến mãi" value="<?= $product['discount_price'] ?>" required>

  <select name="category" id="category" required>
    <option value="">-- Chọn danh mục --</option>
    <?php
    $types = ['iphone','mac','ipad','watch','airpods','phukien'];
    foreach ($types as $t) {
        $sel = ($product['category'] == $t) ? "selected" : "";
        echo "<option value=\"$t\" $sel>" . ucfirst($t) . "</option>";
    }
    ?>
  </select>

  <input type="text" name="color" placeholder="Màu sắc" value="<?= htmlspecialchars($product['color']) ?>" required>

  <div id="storage-field">
    <input type="text" name="storage" placeholder="Dung lượng" value="<?= htmlspecialchars($product['storage']) ?>">
  </div>

  <div id="version-field" class="hidden">
    <input type="text" name="version" placeholder="Phiên bản" value="<?= htmlspecialchars($product['version']) ?>">
  </div>

  <label>Thay ảnh mới (nếu có):</label>
  <input type="file" name="image" accept="image/*">

  <button type="submit">💾 Lưu thay đổi</button>
</form>

<script>
const categorySelect = document.getElementById('category');
const versionField = document.getElementById('version-field');
const storageField = document.getElementById('storage-field');

function toggleFields() {
  const value = categorySelect.value;
  if (value === 'iphone' || value === 'mac' || value === 'ipad') {
    versionField.classList.add('hidden');
    storageField.classList.remove('hidden');
  } else if (value === 'watch') {
    versionField.classList.remove('hidden');
    storageField.classList.add('hidden');
  } else if (value === 'airpods' || value === 'phukien') {
    versionField.classList.add('hidden');
    storageField.classList.add('hidden');
  }
}
toggleFields();
categorySelect.addEventListener('change', toggleFields);
</script>

</body>
</html>
<?php $stmt->close(); $conn->close(); ?>
