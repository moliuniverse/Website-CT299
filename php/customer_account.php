<?php
session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); // Unauthorized
    die(json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập.']));
}

// Kết nối CSDL
$servername = "localhost"; $username = "root"; $password = ""; $dbname = "applestore";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Lỗi kết nối CSDL."]));
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_info':
        $stmt = $conn->prepare("SELECT user_name, email, phone, avatar, membership_tier FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $user]);
        break;

    case 'update_info':
        $user_name = $_POST['username']; $email = $_POST['email']; $phone = $_POST['phone'];
        $stmt = $conn->prepare("UPDATE customers SET user_name = ?, email = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $user_name, $email, $phone, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $user_name;
            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại. Email hoặc SĐT có thể đã được sử dụng.']);
        }
        break;

    case 'update_password':
        $current_password = $_POST['current_password']; $new_password = $_POST['new_password'];
        $stmt = $conn->prepare("SELECT password_hash FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && password_verify($current_password, $result['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $new_hash, $user_id);
            if ($update_stmt->execute()) echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
            else echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
        }
        break;

    case 'update_avatar':
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $upload_dir = '../images/avatars/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_filename = $user_id . '_' . time() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
                $stmt = $conn->prepare("UPDATE customers SET avatar = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_filename, $user_id);
                if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công!']);
                else echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật CSDL.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi tải lên ảnh.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không có file nào được tải lên.']);
        }
        break;

    case 'delete_account':
        $password = $_POST['password'];
        $stmt = $conn->prepare("SELECT password_hash FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && password_verify($password, $result['password_hash'])) {
            $delete_stmt = $conn->prepare("DELETE FROM customers WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_id);
            if ($delete_stmt->execute()) {
                session_destroy();
                echo json_encode(['success' => true, 'message' => 'Tài khoản đã được xóa vĩnh viễn.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa tài khoản.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không đúng.']);
        }
        break;
}
$conn->close();
?>