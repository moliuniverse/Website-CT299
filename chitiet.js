const id = new URLSearchParams(window.location.search).get('id');
const container = document.getElementById('product-detail');

fetch(`get_variants.php?id=${id}`)
  .then(res => res.json())
  .then(variants => {
    if (!variants.length) {
      container.innerHTML = '<p>Không tìm thấy sản phẩm</p>';
      return;
    }

    let selected = variants.find(v => v.id == id) || variants[0];
    const name = selected.name;
    const filteredVariants = variants.filter(v => v.name === name);
    const allColors = [...new Set(filteredVariants.map(v => v.color).filter(Boolean))];
    const allStorages = [...new Set(filteredVariants.map(v => v.storage).filter(Boolean))];
    const hasOptions = allColors.length > 1 || allStorages.length > 1;

    async function render() {
      const availableColors = filteredVariants.filter(v => v.storage === selected.storage).map(v => v.color);
      const details = await fetch(`get_details.php?id=${selected.id}`).then(res => res.json());
      const images = await fetch(`get_images.php?id=${selected.id}`).then(res => res.json());

      let currentImage = images.length ? images[0] : selected.image;

      container.innerHTML = `
        <div class="left">
          <img id="main-image" src="images/${currentImage}" alt="${selected.name}">
          <div class="thumbnails">
            ${images.map((img, i) => `
              <img src="images/${img}" onclick="document.getElementById('main-image').src='images/${img}'">
            `).join('')}
          </div>
        </div>
        <div class="right">
          <h2>${selected.name}</h2>
          <p class="price">
            <del>${Number(selected.price).toLocaleString()}₫</del>
            <strong>${Number(selected.discount_price).toLocaleString()}₫</strong>
          </p>

          ${hasOptions ? `
            ${allStorages.length > 1 ? `
              <div class="option-group">
                <span class="label">Dung lượng:</span>
                <div class="options" id="storage-options">
                  ${allStorages.map(s => `
                    <div class="option-button ${s === selected.storage ? 'active' : ''}" data-storage="${s}">${s}</div>
                  `).join('')}
                </div>
              </div>
            ` : ''}

            ${allColors.length > 1 ? `
              <div class="option-group">
                <span class="label">Màu sắc:</span>
                <div class="options" id="color-options">
                  ${allColors.map(c => {
                    const isAvailable = availableColors.includes(c);
                    const isActive = c === selected.color;
                    return `<div class="option-button ${isActive ? 'active' : ''} ${!isAvailable ? 'disabled' : ''}" data-color="${c}">${c}</div>`;
                  }).join('')}
                </div>
              </div>
            ` : ''}
          ` : ''}

          <div class="actions">
            <button onclick="addToCart()">🛒 Thêm vào giỏ</button>
            <button onclick="buyNow()">💳 Thanh toán ngay</button>
          </div>

          <div class="description">
            <h3>Mô tả sản phẩm</h3>
            <p>${details.description || 'Chưa có mô tả.'}</p>
          </div>

          <div class="specs">
            <h3>Thông số kỹ thuật</h3>
            <table>
              ${details.specs && details.specs.length > 0
                ? details.specs.map(s => `<tr><th>${s.label}</th><td>${s.value}</td></tr>`).join('')
                : '<tr><td colspan="2">Chưa có thông số kỹ thuật.</td></tr>'}
            </table>
          </div>
        </div>
      `;

      // Nút chuyển đổi ảnh bằng bàn phím
      const mainImage = document.getElementById('main-image');
      let currentIndex = 0;
      function updateImage(index) {
        if (images[index]) {
          currentIndex = index;
          mainImage.src = `images/${images[index]}`;
        }
      }
      window.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') updateImage((currentIndex + 1) % images.length);
        if (e.key === 'ArrowLeft') updateImage((currentIndex - 1 + images.length) % images.length);
      });

      if (allStorages.length > 1) {
        document.querySelectorAll('#storage-options .option-button').forEach(btn => {
          btn.onclick = () => {
            const newStorage = btn.dataset.storage;
            const match = filteredVariants.find(v => v.color === selected.color && v.storage === newStorage);
            if (match) {
              selected = match;
              render();
            }
          };
        });
      }

      if (allColors.length > 1) {
        document.querySelectorAll('#color-options .option-button').forEach(btn => {
          if (!btn.classList.contains('disabled')) {
            btn.onclick = () => {
              const newColor = btn.dataset.color;
              const match = filteredVariants.find(v => v.color === newColor && v.storage === selected.storage);
              if (match) {
                selected = match;
                render();
              }
            };
          }
        });
      }
    }

    window.addToCart = function () {
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');
      cart.push(selected);
      localStorage.setItem('cart', JSON.stringify(cart));
      alert(`✅ Đã thêm vào giỏ: ${selected.name} - ${selected.storage || ''} - ${selected.color || ''}`);
    }

    window.buyNow = function () {
      addToCart();
      window.location.href = 'checkout.html';
    }

    render();
  })
  .catch(err => {
    console.error(err);
    container.innerHTML = '<p>Lỗi khi tải sản phẩm.</p>';
  });