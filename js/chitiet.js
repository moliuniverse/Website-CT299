document.addEventListener('DOMContentLoaded', () => {
    // Lấy ID sản phẩm từ URL
    const id = new URLSearchParams(window.location.search).get('id');
    const container = document.getElementById('product-detail');
    let autoSlideInterval; // Biến để lưu trữ bộ đếm thời gian tự động trượt ảnh

    // Nếu không có ID trong URL, dừng lại và báo lỗi
    if (!id) {
        container.innerHTML = '<p style="text-align: center; font-size: 18px;">Không tìm thấy ID sản phẩm. Vui lòng kiểm tra lại đường dẫn.</p>';
        return;
    }

    // Hàm tiện ích để cuộn danh sách ảnh phụ
    window.scrollThumbnails = function(direction) {
      const thumbsContainer = document.querySelector('.thumbnails');
      if (!thumbsContainer) return;
      const scrollAmount = thumbsContainer.offsetWidth * 0.7;
      if (direction === 'left') {
        thumbsContainer.scrollLeft -= scrollAmount;
      } else {
        thumbsContainer.scrollLeft += scrollAmount;
      }
    }

    // Bắt đầu quá trình lấy dữ liệu sản phẩm
    fetch(`php/get_variants.php?id=${id}`)
      .then(res => {
          if (!res.ok) throw new Error('Lỗi mạng hoặc không tìm thấy file get_variants.php');
          return res.json();
      })
      .then(variants => {
        if (!variants.length) {
          container.innerHTML = '<p>Không tìm thấy sản phẩm với ID này.</p>';
          return;
        }

        let selected = variants.find(v => v.id == id) || variants[0];
        const name = selected.name;
        const filteredVariants = variants.filter(v => v.name === name);
        const allColors = [...new Set(filteredVariants.map(v => v.color).filter(Boolean))];
        const allStorages = [...new Set(filteredVariants.map(v => v.storage).filter(Boolean))];
        const hasOptions = allColors.length > 1 || allStorages.length > 1;

        // Hàm chính để render lại toàn bộ giao diện
        async function render() {
          clearInterval(autoSlideInterval);

          const [details, images] = await Promise.all([
              fetch(`php/get_details.php?id=${selected.id}`).then(res => res.json()),
              fetch(`php/get_images.php?id=${selected.id}`).then(res => res.json())
          ]);
          
          let currentImage = images.length ? images[0] : selected.image;

          container.innerHTML = `
            <div class="left">
              <button class="main-image-nav prev" onclick="navigateMainImage(-1)">&#10094;</button>
              <img id="main-image" src="images/${currentImage}" alt="${selected.name}">
              <button class="main-image-nav next" onclick="navigateMainImage(1)">&#10095;</button>
              <div class="thumbnails-container">
                <button class="thumbnail-scroll-btn prev-thumb" onclick="scrollThumbnails('left')">←</button>
                <div class="thumbnails">${images.map(img => `<img src="images/${img}" alt="Thumbnail ${selected.name}">`).join('')}</div>
                <button class="thumbnail-scroll-btn next-thumb" onclick="scrollThumbnails('right')">→</button>
              </div>
            </div>
            <div class="right">
              <h2>${selected.name}</h2>
              <p class="price"><del>${Number(selected.price).toLocaleString()}₫</del> <strong>${Number(selected.discount_price).toLocaleString()}₫</strong></p>
              ${hasOptions ? `
                ${allStorages.length > 1 ? `<div class="option-group"><span class="label">Dung lượng:</span><div class="options" id="storage-options">${allStorages.map(s => `<div class="option-button ${s === selected.storage ? 'active' : ''}" data-storage="${s}">${s}</div>`).join('')}</div></div>` : ''}
                ${allColors.length > 1 ? `<div class="option-group"><span class="label">Màu sắc:</span><div class="options" id="color-options">${filteredVariants.filter(v => v.storage === selected.storage).map(v => v.color).map(c => `<div class="option-button ${c === selected.color ? 'active' : ''}" data-color="${c}">${c}</div>`).join('')}</div></div>` : ''}
              ` : ''}
              <div class="actions"><button onclick="addToCart()">🛒 Thêm vào giỏ</button><button onclick="buyNow()">💳 Thanh toán ngay</button></div>
            </div>
            <div class="description"><h3>Mô tả sản phẩm</h3><p>${details.description || 'Chưa có mô tả.'}</p></div>
            <div class="specs"><h3>Thông số kỹ thuật</h3><table>${details.specs && details.specs.length > 0 ? details.specs.map(s => `<tr><th>${s.label}</th><td>${s.value}</td></tr>`).join('') : '<tr><td colspan="2">Chưa có thông số kỹ thuật.</td></tr>'}</table></div>
            <div class="reviews-section">
              <h3>Đánh giá & Nhận xét</h3>
              <div class="review-form-container"><form id="review-form"><h4>Viết đánh giá của bạn</h4><div class="star-rating"><input type="radio" id="5-stars" name="rating" value="5" /><label for="5-stars">☆</label><input type="radio" id="4-stars" name="rating" value="4" /><label for="4-stars">☆</label><input type="radio" id="3-stars" name="rating" value="3" /><label for="3-stars">☆</label><input type="radio" id="2-stars" name="rating" value="2" /><label for="2-stars">☆</label><input type="radio" id="1-star" name="rating" value="1" /><label for="1-star">☆</label></div><textarea name="comment" placeholder="Nhận xét của bạn..." required></textarea><button type="submit">Gửi đánh giá</button></form></div>
              <div id="reviews-list"></div>
            </div>
          `;

          // --- LOGIC CHO THƯ VIỆN ẢNH (GALLERY) ---
          const mainImage = document.getElementById('main-image');
          let currentIndex = images.findIndex(img => img === currentImage);
          if (currentIndex === -1) currentIndex = 0;

          function updateGallery(index) {
            if (images.length === 0 || index === currentIndex) return;
            const newIndex = (index + images.length) % images.length;
            mainImage.style.opacity = 0;
            setTimeout(() => {
              currentIndex = newIndex;
              mainImage.src = `images/${images[currentIndex]}`;
              mainImage.style.opacity = 1;
              document.querySelectorAll('.thumbnails img').forEach((thumb, idx) => thumb.classList.toggle('active-thumb', idx === currentIndex));
            }, 400);
          }
          
          function startAutoSlide() { clearInterval(autoSlideInterval); autoSlideInterval = setInterval(() => navigateMainImage(1), 10000); }
          function resetAutoSlide() { startAutoSlide(); }
          
          document.querySelectorAll('.thumbnails img').forEach((thumb, index) => thumb.addEventListener('click', () => { updateGallery(index); resetAutoSlide(); }));
          window.navigateMainImage = (direction) => { updateGallery(currentIndex + direction); resetAutoSlide(); };
          
          updateGallery(currentIndex);
          startAutoSlide();

          // --- LOGIC CHỌN PHIÊN BẢN ---
          if (allStorages.length > 1) document.querySelectorAll('#storage-options .option-button').forEach(btn => btn.onclick = () => { const s = btn.dataset.storage; const m = filteredVariants.find(v => v.color === selected.color && v.storage === s) || filteredVariants.find(v => v.storage === s); if (m) { selected = m; render(); } });
          if (allColors.length > 1) document.querySelectorAll('#color-options .option-button:not(.disabled)').forEach(btn => btn.onclick = () => { const c = btn.dataset.color; const m = filteredVariants.find(v => v.color === c && v.storage === selected.storage); if (m) { selected = m; render(); } });
          
          // --- GẮN SỰ KIỆN VÀ TẢI ĐÁNH GIÁ ---
          document.getElementById('review-form').addEventListener('submit', handleReviewSubmit);
          fetchAndRenderReviews(selected.id);
        }
        
        render();
      })
      .catch(err => {
        console.error("Lỗi nghiêm trọng khi tải dữ liệu ban đầu:", err);
        container.innerHTML = '<p>Đã có lỗi xảy ra khi tải dữ liệu sản phẩm. Vui lòng kiểm tra lại ID sản phẩm và kết nối đến server.</p>';
      });

    // --- CÁC HÀM TOÀN CỤC ---
    window.addToCart = () => { /* Logic thêm vào giỏ hàng */ };
    window.buyNow = () => { /* Logic mua ngay */ };

    async function fetchAndRenderReviews(productId) {
      const reviewsList = document.getElementById('reviews-list');
      try {
        const response = await fetch(`php/reviews.php?id=${productId}`);
        const reviews = await response.json();
        if (reviews.length === 0) { reviewsList.innerHTML = '<p>Chưa có đánh giá nào.</p>'; return; }
        reviewsList.innerHTML = reviews.map(review => `
          <div class="review-item">
            <div class="review-author"><img src="images/avatars/${review.user_avatar || 'default.png'}" alt="Avatar"><div><strong>${review.user_name || 'Khách'}</strong><div class="review-stars-display" data-rating="${review.rating}"></div></div></div>
            <p class="review-comment">${review.comment}</p><span class="review-date">${new Date(review.created_at).toLocaleDateString('vi-VN')}</span>
          </div>`).join('');
        document.querySelectorAll('.review-stars-display').forEach(starDiv => { const r = starDiv.dataset.rating; let s = ''; for (let i = 1; i <= 5; i++) s += `<span class="star ${i <= r ? 'filled' : ''}">★</span>`; starDiv.innerHTML = s; });
      } catch (error) { console.error('Lỗi tải đánh giá:', error); reviewsList.innerHTML = '<p>Lỗi tải danh sách đánh giá.</p>'; }
    }

    function handleReviewSubmit(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      const data = { product_id: new URLSearchParams(window.location.search).get('id'), rating: formData.get('rating'), comment: formData.get('comment') };
      if (!data.rating) { alert('Vui lòng chọn số sao!'); return; }
      fetch('php/reviews.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
      .then(res => res.json())
      .then(result => {
        if (result.success) { alert('Cảm ơn bạn đã đánh giá!'); form.reset(); fetchAndRenderReviews(data.product_id); }
        else { alert('Lỗi: ' + result.message); }
      })
      .catch(error => console.error('Lỗi gửi đánh giá:', error));
    }
});