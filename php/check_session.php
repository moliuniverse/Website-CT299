<?php
session_start();
header('Content-Type: application/json');

// Mặc định là chưa đăng nhập
$response = ['loggedin' => false];

// Nếu có session và đã đăng nhập
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Kết nối CSDL
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "applestore";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        // Nếu lỗi kết nối, trả về thông tin cơ bản từ session
        $response = [
            'loggedin' => true,
            'user_name' => $_SESSION['user_name'] ?? 'Khách',
            // Trả về null nếu không lấy được từ CSDL
            'email' => null,
            'phone' => null,
            'avatar' => 'default.png'
        ];
    } else {
        $user_id = $_SESSION['user_id'];
        // Cập nhật câu lệnh SQL để lấy thêm email và phone
        $stmt = $conn->prepare("SELECT user_name, email, phone, avatar FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Cập nhật response với đầy đủ thông tin
        $response = [
            'loggedin' => true,
            'user_name' => $result['user_name'] ?? $_SESSION['user_name'],
            'email' => $result['email'] ?? null,
            'phone' => $result['phone'] ?? null,
            'avatar' => $result['avatar'] ?? 'default.png'
        ];
        $conn->close();
    }
}

echo json_encode($response);
?>