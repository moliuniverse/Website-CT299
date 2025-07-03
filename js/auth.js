document.addEventListener('DOMContentLoaded', () => {

    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');

    // Xử lý form Đăng ký
    if (registerForm) {
        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Ngăn form gửi theo cách truyền thống

            const formData = new FormData(registerForm);
            formData.append('action', 'register'); // Thêm hành động 'register'

            try {
                // ===== ĐÃ CẬP NHẬT ĐƯỜNG DẪN Ở ĐÂY =====
                const response = await fetch('php/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    // Chuyển hướng đến trang đăng nhập sau khi đăng ký thành công
                    window.location.href = 'dangnhap.html';
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                console.error('Đã có lỗi xảy ra:', error);
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }

    // Xử lý form Đăng nhập
    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(loginForm);
            formData.append('action', 'login'); // Thêm hành động 'login'

            try {
                // ===== ĐÃ CẬP NHẬT ĐƯỜNG DẪN Ở ĐÂY =====
                const response = await fetch('php/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Chuyển hướng đến trang chủ sau khi đăng nhập thành công
                    window.location.href = 'trangchu.html';
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                console.error('Đã có lỗi xảy ra:', error);
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
});