<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$id = $_GET['id'] ?? '';
if (!$id) die("Thiếu ID sản phẩm");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $desc = $_POST['description'];
  $labels = $_POST['label'] ?? [];
  $values = $_POST['value'] ?? [];

  $conn->query("DELETE FROM product_descriptions WHERE product_id = '$id'");
  $conn->query("DELETE FROM product_specs WHERE product_id = '$id'");
  $stmt1 = $conn->prepare("INSERT INTO product_descriptions (product_id, description) VALUES (?, ?)");
  if (!$stmt1) die("Lỗi prepare 1: " . $conn->error);
  $stmt1->bind_param("ss", $id, $desc);
  $stmt1->execute();

  $stmt2 = $conn->prepare("INSERT INTO product_specs (product_id, label, value) VALUES (?, ?, ?)");
  for ($i = 0; $i < count($labels); $i++) {
    if (trim($labels[$i]) && trim($values[$i])) {
      $stmt2->bind_param("sss", $id, $labels[$i], $values[$i]);
      $stmt2->execute();
    }
  }

  if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = 'images/';
    $stmt3 = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
      $name = basename($_FILES['images']['name'][$index]);
      $targetPath = $uploadDir . $name;
      if (move_uploaded_file($tmpName, $targetPath)) {
        $stmt3->bind_param("ss", $id, $name);
        $stmt3->execute();
      }
    }
  }

  echo "<script>alert('✅ Đã lưu mô tả và ảnh'); location='list_products.php';</script>";
  exit;
}

$desc = '';
$specs = [];
$res = $conn->query("SELECT description FROM product_descriptions WHERE product_id = '$id'");
if ($res && $row = $res->fetch_assoc()) $desc = $row['description'];

$res2 = $conn->query("SELECT label, value FROM product_specs WHERE product_id = '$id'");
while ($res2 && $row = $res2->fetch_assoc()) $specs[] = $row;

$images = [];
$res3 = $conn->query("SELECT image_path FROM product_images WHERE product_id = '$id'");
if ($res3) {
  while ($row = $res3->fetch_assoc()) {
    $images[] = $row['image_path'];
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Mô tả & Thông số</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 30px; }
    form { max-width: 700px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
    textarea, input { width: 100%; padding: 10px; margin: 10px 0; }
    table { width: 100%; margin-top: 20px; border-collapse: collapse; }
    th, td { padding: 8px; }
    button { padding: 10px 20px; background: #0071e3; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #005bb5; }
    img.thumbnail { width: 80px; margin: 5px; border: 1px solid #ccc; border-radius: 4px; }
  </style>
</head>
<body>
  <form method="POST" enctype="multipart/form-data">
    <h2>📝 Mô tả & Thông số cho ID: <?= htmlspecialchars($id) ?></h2>

    <label>Mô tả sản phẩm:</label>
    <textarea name="description" rows="5" required><?= htmlspecialchars($desc) ?></textarea>

    <h3>Thông số kỹ thuật:</h3>
    <table id="spec-table">
      <thead><tr><th>Thông số</th><th>Giá trị</th></tr></thead>
      <tbody>
        <?php foreach ($specs as $s): ?>
        <tr>
          <td><input name="label[]" value="<?= htmlspecialchars($s['label']) ?>"></td>
          <td><input name="value[]" value="<?= htmlspecialchars($s['value']) ?>"></td>
        </tr>
        <?php endforeach; ?>
        <tr><td><input name="label[]"></td><td><input name="value[]"></td></tr>
      </tbody>
    </table>
    <button type="button" onclick="addRow()">➕ Thêm dòng</button>

    <h3>📸 Thêm ảnh phụ:</h3>
    <input type="file" name="images[]" accept="image/*" multiple>

    <?php if ($images): ?>
      <div style="margin-top: 10px;">
        <strong>Ảnh hiện tại:</strong><br>
        <?php foreach ($images as $img): ?>
          <img class="thumbnail" src="images/<?= htmlspecialchars($img) ?>">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <br><br>
    <button type="submit">💾 Lưu thông tin</button>
  </form>

  <script>
    function addRow() {
      const row = document.createElement('tr');
      row.innerHTML = '<td><input name="label[]"></td><td><input name="value[]"></td>';
      document.querySelector('#spec-table tbody').appendChild(row);
    }
  </script>
</body>
</html>