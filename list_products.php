<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$sql = "SELECT * FROM products ORDER BY id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách sản phẩm</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
    }
    h2 {
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ccc;
      text-align: center;
    }
    img {
      width: 80px;
      height: auto;
    }
    a.button {
      padding: 6px 12px;
      background: #0071e3;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 14px;
      display: inline-block;
      margin: 2px;
    }
    a.button:hover {
      background: #005bb5;
    }
    a.delete {
      background: #e63946;
    }
    a.delete:hover {
      background: #b1001f;
    }
  </style>
</head>
<body>
  <h2>📦 Danh sách sản phẩm</h2>
  <a class="button" href="form_add_product.html">➕ Thêm sản phẩm mới</a>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Phiên bản</th>
        <th>Giá</th>
        <th>Loại</th>
        <th>Ảnh</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['version']) ?></td>
            <td>
              <?= number_format($row['discount_price']) ?>₫<br>
              <s><?= number_format($row['price']) ?>₫</s>
            </td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td>
              <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?= $row['image'] ?>" alt="Ảnh">
              <?php else: ?>
                Không có ảnh
              <?php endif; ?>
            </td>
            <td>
              <a class="button" href="edit_product.php?id=<?= $row['id'] ?>">✏️ Sửa</a>
              <a class="button" href="edit_description.php?id=<?= $row['id'] ?>">📄 Mô tả</a>
              <a class="button delete" href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xoá sản phẩm này?')">🗑 Xoá</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7">Không có sản phẩm nào.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
<?php $conn->close(); ?>
