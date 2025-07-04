document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.account-nav .nav-link');
    const contentSections = document.querySelectorAll('.account-content .content-section');
    
    // Logic chuyển tab
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (link.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                document.querySelector('.account-nav .nav-link.active').classList.remove('active');
                link.classList.add('active');
                const targetId = link.getAttribute('href');
                document.querySelector('.content-section.active').classList.remove('active');
                document.querySelector(targetId).classList.add('active');
            }
        });
    });

    // Hàm gọi API chung
    async function apiCall(action, options = {}) {
        try {
            const response = await fetch(`php/customer_account.php?action=${action}`, options);
            return await response.json();
        } catch (error) {
            console.error('API call error:', error);
            return { success: false, message: 'Lỗi kết nối máy chủ.' };
        }
    }

    // Tải thông tin người dùng
    async function loadUserInfo() {
        const result = await apiCall('get_info');
        if (result.success) {
            const user = result.data;
            document.getElementById('username').value = user.user_name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone;
            document.getElementById('avatar-img-preview').src = `images/avatars/${user.avatar || 'default.png'}`;
            document.getElementById('user-name-display').textContent = user.user_name;
            
            // Cập nhật badge hạng thành viên
            const tierBadge = document.getElementById('membership-tier-badge');
            tierBadge.textContent = user.membership_tier;
            tierBadge.className = 'tier-badge'; // Reset class
            switch(user.membership_tier.toLowerCase()) {
                case 'bạc': tierBadge.classList.add('tier-silver'); break;
                case 'vàng': tierBadge.classList.add('tier-gold'); break;
                case 'kim cương': tierBadge.classList.add('tier-diamond'); break;
                default: tierBadge.classList.add('tier-default');
            }
        } else {
            alert(result.message || "Vui lòng đăng nhập để xem trang này.");
            window.location.href = 'dangnhap.html';
        }
    }
    
    // Gắn sự kiện cho các form
    document.getElementById('info-form').addEventListener('submit', async e => {
        e.preventDefault();
        const result = await apiCall('update_info', { method: 'POST', body: new FormData(e.target) });
        alert(result.message);
        if (result.success) loadUserInfo();
    });
    document.getElementById('avatar-form').addEventListener('submit', async e => {
        e.preventDefault();
        const result = await apiCall('update_avatar', { method: 'POST', body: new FormData(e.target) });
        alert(result.message);
        if (result.success) { e.target.reset(); loadUserInfo(); }
    });
    document.getElementById('password-form').addEventListener('submit', async e => {
        e.preventDefault();
        if (e.target.new_password.value !== e.target.confirm_password.value) {
            alert('Mật khẩu xác nhận không khớp!'); return;
        }
        const result = await apiCall('update_password', { method: 'POST', body: new FormData(e.target) });
        alert(result.message);
        if (result.success) e.target.reset();
    });
    document.getElementById('delete-form').addEventListener('submit', async e => {
        e.preventDefault();
        if (confirm("BẠN CÓ CHẮC CHẮN MUỐN XÓA TÀI KHOẢN KHÔNG? HÀNH ĐỘNG NÀY KHÔNG THỂ HOÀN TÁC.")) {
            const result = await apiCall('delete_account', { method: 'POST', body: new FormData(e.target) });
            alert(result.message);
            if (result.success) window.location.href = 'php/logout.php';
        }
    });

    loadUserInfo(); // Chạy hàm tải thông tin khi vào trang
});