<?php
$conn = new mysqli('localhost', 'root', '', 'applestore');
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nhận từ khóa tìm kiếm
$search = $_GET['search'] ?? '';

// Tạo câu SQL có điều kiện tìm kiếm
if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE name LIKE ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM products ORDER BY id ASC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danh sách sản phẩm</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-color: #f9fafb;
      color: #1f2a44;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .table-container {
      max-width: 1200px;
      margin: 0 auto;
    }
    .search-form input {
      transition: all 0.3s ease;
    }
    .search-form input:focus {
      ring: 2px solid #000;
      outline: none;
    }
    .button {
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .button:hover {
      transform: translateY(-1px);
    }
    .delete-button:hover {
      background-color: #991b1b;
    }
    img {
      transition: transform 0.3s ease;
    }
    img:hover {
      transform: scale(1.1);
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50">
  <div class="table-container py-8">
    <h2 class="text-3xl font-bold text-center text-gray-900 mb-6">Danh sách sản phẩm</h2>

    <form class="search-form flex justify-center mb-8" method="GET" action="">
      <div class="relative w-full max-w-md">
        <input 
          type="text" 
          name="search" 
          placeholder="Tìm sản phẩm theo tên..." 
          value="<?= htmlspecialchars($search) ?>"
          class="w-full px-4 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
        >
        <button 
          type="submit" 
          class="absolute right-0 top-0 h-full px-4 bg-gray-900 text-white rounded-r-lg hover:bg-gray-700"
        >
          🔍 Tìm kiếm
        </button>
      </div>
      <?php if (!empty($search)): ?>
        <a href="list_products.php" class="ml-4 text-gray-600 hover:text-gray-900 font-medium">❌ Xóa lọc</a>
      <?php endif; ?>
    </form>

    <div class="flex justify-end mb-4">
      <a 
        href="../form_add_product.html" 
        class="button inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg shadow-sm hover:bg-gray-700"
      >
        ➕ Thêm sản phẩm mới
      </a>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">ID</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tên</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phiên bản</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Giá</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Loại</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ảnh</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['id']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['name']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['version']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  <?= number_format($row['discount_price']) ?>₫<br>
                  <span class="line-through text-gray-500"><?= number_format($row['price']) ?>₫</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['category']) ?></td>
                <td class="px-6 py-4">
                  <?php if (!empty($row['image'])): ?>
                   <img src="../images/<?= htmlspecialchars($row['image']) ?>" alt="Ảnh" class="w-20 h-auto rounded">
                  <?php else: ?>
                    <span class="text-gray-500">Không có ảnh</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 flex space-x-2">
                  <a 
                    href="edit_product.php?id=<?= $row['id'] ?>" 
                    class="button px-3 py-1 bg-gray-900 text-white rounded-lg hover:bg-gray-700"
                  >
                    ✏️ Sửa
                  </a>
                  <a 
                    href="edit_description.php?id=<?= $row['id'] ?>" 
                    class="button px-3 py-1 bg-gray-900 text-white rounded-lg hover:bg-gray-700"
                  >
                    📄 Mô tả
                  </a>
                  <a 
                    href="delete_product.php?id=<?= $row['id'] ?>" 
                    class="button delete-button px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"
                  >
                    🗑 Xóa
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">Không có sản phẩm nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php 
  if (isset($stmt)) $stmt->close();
  $conn->close(); 
  ?>
</body>
</html>
