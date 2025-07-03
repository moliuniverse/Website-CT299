<?php
session_start();

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "applestore";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Kết nối thất bại"]));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'register') {
    $username_reg = $_POST['username'];
    $email_reg = $_POST['email'];
    $phone_reg = $_POST['phone']; // Lấy SĐT
    $password_reg = $_POST['password'];

    // Thêm SĐT vào kiểm tra
    if (empty($username_reg) || empty($email_reg) || empty($phone_reg) || empty($password_reg)) {
        echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin."]);
        exit();
    }

    // Kiểm tra xem email hoặc SĐT đã tồn tại chưa
    $stmt = $conn->prepare("SELECT user_id FROM customers WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email_reg, $phone_reg);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email hoặc Số điện thoại này đã được sử dụng."]);
    } else {
        $password_hash = password_hash($password_reg, PASSWORD_BCRYPT);
        
        // Cập nhật câu lệnh INSERT
        $stmt_insert = $conn->prepare("INSERT INTO customers (user_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $username_reg, $email_reg, $phone_reg, $password_hash);

        if ($stmt_insert->execute()) {
            echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Đã có lỗi xảy ra."]);
        }
        $stmt_insert->close();
    }
    $stmt->close();

} elseif ($action === 'login') {
    // Lấy thông tin đăng nhập (có thể là email hoặc SĐT)
    $email_or_phone = $_POST['email_or_phone'];
    $password_login = $_POST['password'];

    if (empty($email_or_phone) || empty($password_login)) {
        echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin."]);
        exit();
    }

    // Cập nhật câu lệnh SELECT để tìm bằng email hoặc SĐT
    $stmt = $conn->prepare("SELECT user_id, user_name, password_hash FROM customers WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email_or_phone, $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password_login, $user['password_hash'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            
            echo json_encode(["success" => true, "message" => "Đăng nhập thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Mật khẩu không chính xác."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Email hoặc Số điện thoại không tồn tại."]);
    }
    $stmt->close();
}

$conn->close();
?>