document.addEventListener('DOMContentLoaded', () => {
    
    // --- PHẦN 1: TẢI VÀ HIỂN THỊ SẢN PHẨM ---
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
                        <p class="online-deal">Online giá rẻ hơn</p>
                    `;
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


    // --- PHẦN 2: LOGIC THANH TÌM KIẾM ---
    const searchBar = document.querySelector('.search-bar');
    const searchInput = document.querySelector('.search-input');
    const searchToggle = document.querySelector('.search-toggle');

    if (searchBar && searchInput && searchToggle) {
        // Sự kiện khi click vào icon kính lúp
        searchToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Ngăn sự kiện click lan ra ngoài
            searchBar.classList.add('active');
            searchInput.focus();
        });

        // Sự kiện khi nhấn Enter trong ô tìm kiếm
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    window.location.href = `timkiem.html?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });

        // Sự kiện khi click ra ngoài để đóng thanh tìm kiếm
        document.addEventListener('click', (e) => {
            if (!searchBar.contains(e.target)) {
                searchBar.classList.remove('active');
            }
        });
    }


    // --- PHẦN 3: HÀM KHỞI TẠO CAROUSEL ---
    function initCarousel(productsSection) {
        const productList = productsSection.querySelector('.product-list');
        const prevBtn = productsSection.querySelector('.prev-btn');
        const nextBtn = productsSection.querySelector('.next-btn');

        if (!productList || !prevBtn || !nextBtn) return;
        
        const scrollAmount = 270 * 3; // Cuộn một khoảng 3 sản phẩm

        prevBtn.addEventListener('click', () => {
            productList.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            productList.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }
});