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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mô tả & Thông số</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-color: #f9fafb;
      color: #1f2a44;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .form-container {
      max-width: 800px;
      margin: 0 auto;
    }
    input, textarea {
      transition: all 0.3s ease;
    }
    input:focus, textarea:focus {
      outline: none;
      ring: 2px solid #000;
    }
    .button {
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .button:hover {
      transform: translateY(-1px);
    }
    .thumbnail {
      transition: transform 0.3s ease;
    }
    .thumbnail:hover {
      transform: scale(1.05);
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50">
  <div class="form-container py-8">
    <form method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">📝 Mô tả & Thông số cho ID: <?= htmlspecialchars($id) ?></h2>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả sản phẩm:</label>
        <textarea 
          name="description" 
          rows="5" 
          required
          class="w-full px-4 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
        ><?= htmlspecialchars($desc) ?></textarea>
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Thông số kỹ thuật:</h3>
        <table id="spec-table" class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">Thông số</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">Giá trị</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($specs as $s): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2">
                  <input 
                    name="label[]" 
                    value="<?= htmlspecialchars($s['label']) ?>"
                    class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                  >
                </td>
                <td class="px-4 py-2">
                  <input 
                    name="value[]" 
                    value="<?= htmlspecialchars($s['value']) ?>"
                    class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                  >
                </td>
              </tr>
            <?php endforeach; ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2">
                <input 
                  name="label[]" 
                  class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                >
              </td>
              <td class="px-4 py-2">
                <input 
                  name="value[]" 
                  class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                >
              </td>
            </tr>
          </tbody>
        </table>
        <button 
          type="button" 
          onclick="addRow()" 
          class="button mt-4 inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg shadow-sm hover:bg-gray-700"
        >
          ➕ Thêm dòng
        </button>
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">📸 Thêm ảnh phụ:</h3>
        <input 
          type="file" 
          name="images[]" 
          accept="image/*" 
          multiple
          class="w-full px-3 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg shadow-sm file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:rounded-lg"
        >
      </div>

      <?php if ($images): ?>
        <div class="mb-6">
          <strong class="block text-sm font-medium text-gray-700 mb-2">Ảnh hiện tại:</strong>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($images as $img): ?>
              <img 
                src="images/<?= htmlspecialchars($img) ?>" 
                class="thumbnail w-20 h-auto rounded border border-gray-300"
              >
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="flex justify-end">
        <button 
          type="submit" 
          class="button inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg shadow-sm hover:bg-gray-700"
        >
          💾 Lưu thông tin
        </button>
      </div>
    </form>
  </div>

  <script>
    function addRow() {
      const row = document.createElement('tr');
      row.className = 'hover:bg-gray-50';
      row.innerHTML = `
        <td class="px-4 py-2">
          <input 
            name="label[]" 
            class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
          >
        </td>
        <td class="px-4 py-2">
          <input 
            name="value[]" 
            class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
          >
        </td>
      `;
      document.querySelector('#spec-table tbody').appendChild(row);
    }
  </script>
</body>
</html>
