document.addEventListener('DOMContentLoaded', () => {
    // Lấy các element cần thiết từ trang HTML
    const heading = document.getElementById('search-heading');
    const grid = document.getElementById('results-grid');
    const resultsCount = document.getElementById('results-count');
    const sortSelect = document.getElementById('sort-select');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const categoryFiltersContainer = document.getElementById('category-filters');
    const colorFiltersContainer = document.getElementById('color-filters');
    const priceFiltersContainer = document.getElementById('price-filters');

    // Các biến để quản lý trạng thái
    let allProducts = []; // Lưu trữ kết quả gốc từ server
    let filteredAndSortedProducts = []; // Lưu kết quả đã lọc và sắp xếp
    let itemsToShow = 16; // Số sản phẩm hiển thị ban đầu (ví dụ: 4 dòng x 4 sản phẩm)
    const itemsPerLoad = 8; // Số sản phẩm tải thêm mỗi lần click

    // Lấy từ khóa tìm kiếm từ URL
    const queryParams = new URLSearchParams(window.location.search);
    const query = queryParams.get('q');

    if (!query) {
        heading.textContent = 'Vui lòng nhập từ khóa để tìm kiếm.';
        return;
    }
    heading.innerHTML = `Kết quả tìm kiếm cho: <span class="query">"${query}"</span>`;

    // --- HÀM 1: HIỂN THỊ SẢN PHẨM RA LƯỚI ---
    const renderProducts = () => {
        grid.innerHTML = '';
        // Chỉ lấy số lượng sản phẩm cần hiển thị từ danh sách đã lọc
        const productsToRender = filteredAndSortedProducts.slice(0, itemsToShow);

        if (productsToRender.length === 0) {
            grid.innerHTML = '<p id="no-results">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>';
        } else {
            productsToRender.forEach(product => {
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
                grid.appendChild(card);
            });
        }
        
        // Cập nhật số lượng kết quả
        resultsCount.textContent = `${filteredAndSortedProducts.length} kết quả`;

        // Ẩn/hiện nút "Xem thêm"
        if (filteredAndSortedProducts.length > itemsToShow) {
            loadMoreBtn.style.display = 'block';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    };

    // --- HÀM 2: TẠO CÁC BỘ LỌC ĐỘNG ---
    const populateFilters = (products) => {
        if (!products || products.length === 0) return;

        // Tạo bộ lọc cho Danh mục
        const categories = [...new Set(products.map(p => p.category).filter(Boolean))];
        if (categoryFiltersContainer) {
            categoryFiltersContainer.innerHTML = categories.map(cat => `
                <label><input type="checkbox" name="category" value="${cat}"> ${cat.charAt(0).toUpperCase() + cat.slice(1)}</label>
            `).join('');
        }

        // Tạo bộ lọc cho Màu sắc
        const colors = [...new Set(products.map(p => p.color).filter(Boolean))];
        if (colorFiltersContainer) {
            colorFiltersContainer.innerHTML = colors.map(col => `
                <label><input type="checkbox" name="color" value="${col}"> ${col}</label>
            `).join('');
        }
        
        // Gắn sự kiện cho tất cả các input trong bộ lọc sau khi chúng được tạo
        document.querySelectorAll('.filter-group input').forEach(input => {
            input.addEventListener('change', applyFiltersAndSort);
        });
    };
    
    // --- HÀM 3: XỬ LÝ LỌC VÀ SẮP XẾP ---
    const applyFiltersAndSort = () => {
        let filtered = [...allProducts];

        // Lọc theo giá
        const selectedPriceInput = document.querySelector('input[name="price"]:checked');
        if (selectedPriceInput) {
            const selectedPriceValue = selectedPriceInput.value;
            if (selectedPriceValue !== 'all') {
                const [min, max] = selectedPriceValue.split('-').map(Number);
                filtered = filtered.filter(p => p.discount_price >= min && p.discount_price <= max);
            }
        }

        // Lọc theo danh mục
        const selectedCategories = [...document.querySelectorAll('input[name="category"]:checked')].map(cb => cb.value);
        if (selectedCategories.length > 0) {
            filtered = filtered.filter(p => selectedCategories.includes(p.category));
        }

        // Lọc theo màu sắc
        const selectedColors = [...document.querySelectorAll('input[name="color"]:checked')].map(cb => cb.value);
        if (selectedColors.length > 0) {
            filtered = filtered.filter(p => selectedColors.includes(p.color));
        }

        // Sắp xếp kết quả đã lọc
        const sortBy = sortSelect.value;
        if (sortBy === 'price-asc') {
            filtered.sort((a, b) => a.discount_price - b.discount_price);
        } else if (sortBy === 'price-desc') {
            filtered.sort((a, b) => b.discount_price - a.discount_price);
        }
        
        filteredAndSortedProducts = filtered; // Cập nhật danh sách cuối cùng
        itemsToShow = 16; // Reset lại số lượng hiển thị mỗi khi lọc
        renderProducts(); // Render lại kết quả
    };

    // --- HÀM 4: TẢI SẢN PHẨM TƯƠNG TỰ ---
    function fetchSimilarProducts() {
        fetch('php/get_products.php')
            .then(response => response.json())
            .then(data => {
                const similarGrid = document.getElementById('similar-grid');
                const similarSection = document.getElementById('similar-products-section');
                
                const searchResultIds = new Set(allProducts.map(p => p.id));
                const similarProducts = data.filter(p => !searchResultIds.has(p.id));
                const randomSimilar = similarProducts.sort(() => 0.5 - Math.random()).slice(0, 4);

                if (randomSimilar.length > 0) {
                    similarGrid.innerHTML = '';
                    randomSimilar.forEach(product => {
                        const card = document.createElement('div');
                        card.className = 'product-card';
                        card.innerHTML = `
                            <img src="images/${product.image}" alt="${product.name}">
                            <p>${product.name}</p>
                            <p class="discount">${Number(product.price).toLocaleString()}₫</p>
                            <p>${Number(product.discount_price).toLocaleString()}₫</p>
                        `;
                        card.addEventListener('click', () => window.location.href = `chitiet.html?id=${product.id}`);
                        similarGrid.appendChild(card);
                    });
                    similarSection.style.display = 'block';
                }
            });
    }

    // --- GẮN CÁC SỰ KIỆN BAN ĐẦU ---
    sortSelect.addEventListener('change', applyFiltersAndSort);
    loadMoreBtn.addEventListener('click', () => {
        itemsToShow += itemsPerLoad;
        renderProducts();
    });

    // --- BẮT ĐẦU TẢI DỮ LIỆU CHÍNH ---
    fetch(`php/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(products => {
            allProducts = products;
            populateFilters(allProducts); // Tạo các checkbox lọc
            applyFiltersAndSort();      // Chạy lọc và hiển thị lần đầu
            if (products.length > 0) {
                fetchSimilarProducts();
            }
        })
        .catch(error => {
            console.error('Lỗi khi tìm kiếm:', error);
            grid.innerHTML = '<p id="no-results">Đã có lỗi xảy ra. Vui lòng thử lại.</p>';
        });
});