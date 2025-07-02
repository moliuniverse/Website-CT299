// === trangchu.js ===
document.addEventListener('DOMContentLoaded', () => {
    const categories = ['iphone', 'mac', 'ipad', 'watch', 'airpods', 'phukien'];

    fetch('get_products.php')
        .then(response => response.json())
        .then(data => {
            categories.forEach(category => {
                const container = document.querySelector(`#${category} .product-list`);
                if (!container) return;

                const seenNames = new Set();
                const filtered = data.filter(product => {
                    const isSameCategory = product.category === category;
                    const isUnique = !seenNames.has(product.name);
                    if (isSameCategory && isUnique) {
                        seenNames.add(product.name);
                        return true;
                    }
                    return false;
                });

                container.innerHTML = '';

                filtered.forEach(product => {
                    const card = document.createElement('div');
                    card.className = 'product-card';
                    card.dataset.product = product.name;

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
                initCarousel(productsSection);
            });
        })
        .catch(error => console.error('Lỗi tải sản phẩm:', error));

    // === Tìm kiếm sản phẩm ===
    const searchInput = document.querySelector('.search-bar input');
    let timeout;

    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.placeholder = "Tìm kiếm sản phẩm...";
        });

        searchInput.addEventListener('blur', () => {
            if (!searchInput.value) {
                searchInput.placeholder = "🔍";
            }
        });

        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.product-card').forEach(card => {
                    const name = (card.dataset.product || '').toLowerCase();
                    card.style.display = name.includes(searchTerm) ? 'block' : 'none';
                });
            }, 300);
        });
    }

    // === Toggle tìm kiếm khi bấm vào ảnh ===
    const toggle = document.querySelector('.search-toggle');
const input = document.querySelector('.search-input');

if (toggle && input) {
    toggle.addEventListener('click', () => {
        input.classList.remove('hidden');
        input.classList.add('show');
        toggle.style.display = 'none';
        input.focus();
    });

    input.addEventListener('blur', () => {
        input.classList.remove('show');
        input.classList.add('hidden');
        toggle.style.display = 'inline';
    });
}

});

// === Khởi tạo carousel (tương thích mọi trình duyệt) ===
function initCarousel(productsSection) {
    if (!productsSection) return;

    const productList = productsSection.querySelector('.product-list');
    const prevBtn = productsSection.querySelector('.prev-btn');
    const nextBtn = productsSection.querySelector('.next-btn');

    if (!productList || !prevBtn || !nextBtn) return;

    const cardWidth = 256;

    prevBtn.addEventListener('click', () => {
        productList.scrollLeft -= cardWidth * 2;
    });

    nextBtn.addEventListener('click', () => {
        productList.scrollLeft += cardWidth * 2;
    });
}