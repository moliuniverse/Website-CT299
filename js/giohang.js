document.addEventListener('DOMContentLoaded', () => {
    const cartItemsWrapper = document.getElementById('cart-items-wrapper');
    const cartTotalElement = document.getElementById('cart-total');
    const checkoutLink = document.getElementById('checkout-link');

    function renderCart() {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cartItemsWrapper.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            cartItemsWrapper.innerHTML = '<p id="empty-cart-message">Giỏ hàng của bạn đang trống.</p>';
            cartTotalElement.textContent = '0₫';
            checkoutLink.classList.add('disabled'); // Vô hiệu hóa nút thanh toán
            return;
        }

        checkoutLink.classList.remove('disabled');
        cart.forEach((item, index) => {
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';

            // Tạo chuỗi chi tiết phiên bản
            const variantDetails = [item.storage, item.color].filter(Boolean).join(' - ');

            itemElement.innerHTML = `
        <img src="images/${item.image}" alt="${item.name}">
        <div class="item-details">
            <p>${item.name}</p>
            <p class="item-variant-details">${variantDetails}</p> 
            <p class="item-price">${Number(item.discount_price).toLocaleString()}₫</p>
        </div>
        <div class="item-quantity">
            <input type="number" value="${item.quantity}" min="1" data-index="${index}">
        </div>
        <button class="remove-item" data-index="${index}">Xóa</button>`;
            cartItemsWrapper.appendChild(itemElement);
            total += item.discount_price * item.quantity;
        });

        cartTotalElement.textContent = `${total.toLocaleString()}₫`;
        addEventListeners();
    }

    function addEventListeners() {
        document.querySelectorAll('.item-quantity input').forEach(input => {
            input.addEventListener('change', e => updateQuantity(e.target.dataset.index, parseInt(e.target.value)));
        });
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', e => removeItem(e.target.dataset.index));
        });
        checkoutLink.addEventListener('click', e => {
            if (checkoutLink.classList.contains('disabled')) e.preventDefault();
        });
    }

    function updateQuantity(index, quantity) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (quantity > 0) cart[index].quantity = quantity;
        else cart.splice(index, 1); // Xóa nếu số lượng là 0
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }

    function removeItem(index) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }

    renderCart();
});