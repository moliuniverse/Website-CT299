document.addEventListener('DOMContentLoaded', () => {

    // --- PHẦN 1: LOGIC HEADER (DROPDOWN & TÌM KIẾM) ---
    function setupHeader() {
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (!profileDropdown) return;

        const profileAvatar = document.getElementById('profile-avatar');
        const dropdownMenu = profileDropdown.querySelector('.dropdown-menu');
        if (!profileAvatar || !dropdownMenu) return;

        // Lấy trạng thái đăng nhập từ server
        fetch('php/check_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.loggedin) {
                    profileAvatar.src = `images/avatars/${data.avatar}`;
                    dropdownMenu.innerHTML = `
                        <div class="dropdown-user-info"><strong>${data.user_name}</strong></div>
                        <a href="thongtinkhachhang.html">Hồ sơ của tôi</a>
                        <a href="#">Lịch sử đơn hàng</a><a href="#">Hỗ trợ</a>
                        <div class="dropdown-divider"></div>
                        <a href="php/logout.php">Đăng xuất</a>`;
                } else {
                    profileAvatar.src = 'images/avatars/user.png';
                    dropdownMenu.innerHTML = `
                        <a href="dangnhap.html">Đăng nhập</a>
                        <a href="dangky.html">Đăng ký</a>
                        <div class="dropdown-divider"></div>
                        <a href="#">Trợ giúp</a>`;
                }
            }).catch(error => console.error("Lỗi khi kiểm tra session:", error));

        // Logic click được cải tiến để hoạt động chính xác
        profileDropdown.addEventListener('click', (e) => {
            e.stopPropagation(); // Ngăn sự kiện lan ra ngoài
            dropdownMenu.classList.toggle('show');
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', (e) => {
            // Chỉ đóng nếu click ra ngoài khu vực dropdown và menu đang mở
            if (!profileDropdown.contains(e.target) && dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });

        // --- Logic cho thanh tìm kiếm ---
        const searchBar = document.querySelector('.search-bar');
        const searchInput = document.querySelector('.search-input');
        const searchToggle = document.querySelector('.search-toggle');
        if (searchBar && searchInput && searchToggle) {
            searchToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                searchBar.classList.add('active');
                searchInput.focus();
            });
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm) window.location.href = `timkiem.html?q=${encodeURIComponent(searchTerm)}`;
                }
            });
        }
    }

    setupHeader();

    // --- PHẦN 2: TẢI VÀ HIỂN THỊ SẢN PHẨM (CHỈ CHẠY TRÊN TRANG CHỦ) ---
    const pagePath = window.location.pathname.split("/").pop();
    if (pagePath === 'trangchu.html' || pagePath === '') {
        fetchProductsAndSetupCarousel();
    }

    function fetchProductsAndSetupCarousel() {
        const categories = ['iphone', 'mac', 'ipad', 'watch', 'airpods', 'phukien'];
        fetch('php/get_products.php')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.error) throw new Error(data.error);
                categories.forEach(category => {
                    const container = document.querySelector(`#${category} .product-list`);
                    if (!container) return;
                    const seenNames = new Set();
                    const filteredProducts = data.filter(product => {
                        if (product.category === category && !seenNames.has(product.name)) {
                            seenNames.add(product.name);
                            return true;
                        }
                        return false;
                    });
                    container.innerHTML = '';
                    filteredProducts.forEach(product => {
                        const card = document.createElement('div');
                        card.className = 'product-card';
                        card.innerHTML = `
                            <img src="images/${product.image}" alt="${product.name}">
                            <p>${product.name}</p>
                            <p class="discount">${Number(product.price).toLocaleString()}₫</p>
                            <p>${Number(product.discount_price).toLocaleString()}₫</p>
                            <p class="online-deal">Online giá rẻ hơn</p>`;
                        card.addEventListener('click', () => {
                            window.location.href = `chitiet.html?id=${product.id}`;
                        });
                        container.appendChild(card);
                    });
                    const productsSection = container.closest('.products');
                    if (productsSection) initCarousel(productsSection);
                });
            })
            .catch(error => console.error('Lỗi khi tải sản phẩm:', error));
    }

    // --- PHẦN 3: HÀM KHỞI TẠO CAROUSEL ---
    function initCarousel(productsSection) {
        const productList = productsSection.querySelector('.product-list');
        const prevBtn = productsSection.querySelector('.prev-btn');
        const nextBtn = productsSection.querySelector('.next-btn');
        if (!productList || !prevBtn || !nextBtn) return;
        const scrollAmount = 270 * 3;
        prevBtn.addEventListener('click', () => productList.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
        nextBtn.addEventListener('click', () => productList.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
    }
});