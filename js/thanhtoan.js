document.addEventListener('DOMContentLoaded', () => {
    const summaryItemsDiv = document.getElementById('summary-items');
    const summaryTotalElement = document.getElementById('summary-total');
    const checkoutForm = document.getElementById('checkout-form');

    // --- KIỂM TRA TRẠNG THÁI ĐĂNG NHẬP ---
    fetch('php/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.loggedin) {
                // Nếu đã đăng nhập, tiến hành render trang
                initializeCheckoutPage(data);
            } else {
                // Nếu chưa đăng nhập, thông báo và chuyển hướng
                alert('Vui lòng đăng nhập để tiến hành thanh toán.');
                // Lưu lại trang thanh toán để có thể quay lại sau khi đăng nhập thành công
                localStorage.setItem('redirect_after_login', 'thanhtoan.html');
                window.location.href = 'dangnhap.html';
            }
        })
        .catch(error => {
            console.error('Lỗi khi kiểm tra đăng nhập:', error);
            alert('Có lỗi xảy ra, không thể kiểm tra trạng thái đăng nhập.');
        });


    // --- HÀM KHỞI TẠO TRANG SAU KHI XÁC NHẬN ĐÃ ĐĂNG NHẬP ---
    function initializeCheckoutPage(userData) {
        // Tự động điền thông tin người dùng vào form
        if (userData) {
            document.querySelector('input[name="name"]').value = userData.user_name || '';
            document.querySelector('input[name="phone"]').value = userData.phone || '';
            document.querySelector('input[name="email"]').value = userData.email || '';
        }

        renderSummary(); // Hiển thị tóm tắt đơn hàng

        // Gắn sự kiện cho nút "Hoàn tất đơn hàng"
        checkoutForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const customerName = document.querySelector('input[name="name"]').value;
            if (confirm(`Cảm ơn, ${customerName}! Đơn hàng của bạn đã được đặt thành công. Chúng tôi sẽ sớm liên hệ để xác nhận.`)) {
                // Trong thực tế, bạn sẽ gửi dữ liệu này đến server
                // Ở đây, chúng ta chỉ xóa giỏ hàng và chuyển hướng
                localStorage.removeItem('cart');
                window.location.href = 'trangchu.html';
            }
        });
    }

    // --- HÀM HIỂN THỊ TÓM TẮT ĐƠN HÀNG ---
    function renderSummary() {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        // Nếu giỏ hàng trống, quay về trang giỏ hàng
        if (cart.length === 0) {
            alert('Giỏ hàng của bạn đang trống.');
            window.location.href = 'giohang.html';
            return;
        }

        summaryItemsDiv.innerHTML = '';
        let total = 0;

        cart.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'summary-item';

            // Tạo chuỗi chi tiết phiên bản
            const variantDetails = [item.storage, item.color].filter(Boolean).join(' - ');

            itemElement.innerHTML = `
        <img src="images/${item.image}" alt="${item.name}">
        <div class="summary-item-details">
            <p><strong>${item.name}</strong> (x${item.quantity})</p>
            <p class="summary-variant-details">${variantDetails}</p>
        </div>
        <p><strong>${(item.discount_price * item.quantity).toLocaleString()}₫</strong></p>`;
            summaryItemsDiv.appendChild(itemElement);
            total += item.discount_price * item.quantity;
        });

        summaryTotalElement.textContent = `${total.toLocaleString()}₫`;
    }
});