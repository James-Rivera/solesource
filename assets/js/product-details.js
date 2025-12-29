document.addEventListener('DOMContentLoaded', () => {
  // Thumbnail switching
  const mainImg = document.getElementById('productMainImage');
  const thumbs = document.querySelectorAll('.product-thumb');
  thumbs.forEach(btn => {
    btn.addEventListener('click', () => {
      thumbs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const src = btn.getAttribute('data-img');
      if (src && mainImg) mainImg.src = src;
    });
  });

  // Size selector
  const sizeBtns = document.querySelectorAll('.btn-size');
  const cartSizePreview = document.getElementById('cartSizePreview');
  const hiddenSize = document.getElementById('selectedSize');
  sizeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      sizeBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (cartSizePreview) cartSizePreview.textContent = btn.dataset.size;
      if (hiddenSize) hiddenSize.value = btn.dataset.size;
    });
  });

  // Quantity controls and subtotal update
  const qtyValue = document.getElementById('qtyValue');
  const qtyPlus = document.getElementById('qtyPlus');
  const qtyTrash = document.getElementById('qtyTrash');
  const cartItemRow = document.querySelector('.cart-item-row');
  const cartSubtotal = document.getElementById('cartSubtotal');
  const addToCartBtn = document.getElementById('addToCartBtn');
  const cartDrawerEl = document.getElementById('cartDrawer');
  const cartItems = cartDrawerEl?.querySelector('.cart-items');
  const emptyState = cartDrawerEl?.querySelector('.cart-empty');
  const cartOffcanvas = cartDrawerEl ? bootstrap.Offcanvas.getOrCreateInstance(cartDrawerEl) : null;
  const productNameEl = document.querySelector('.product-title-detail');
  const productPriceEl = document.querySelector('.product-price-detail');
  const productBrandEl = document.querySelector('.text-muted.small.fw-semibold');
  const itemPrice = parseFloat(qtyValue?.dataset.price || productPriceEl?.textContent?.replace(/[^0-9.]/g, '') || '0') || 0;
  let qty = 1;

  function renderQty() {
    if (qtyValue) qtyValue.textContent = qty;
    if (cartSubtotal) {
      const total = itemPrice * qty;
      cartSubtotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    if (cartItemRow) {
      cartItemRow.classList.toggle('d-none', qty === 0);
    }
    if (qty === 0 && cartOffcanvas) {
      cartOffcanvas.hide();
    }
  }

  qtyPlus?.addEventListener('click', () => {
    qty += 1;
    renderQty();
  });

  qtyTrash?.addEventListener('click', () => {
    qty = 0;
    renderQty();
  });

  async function addToServerCart(payload) {
    const res = await fetch('includes/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    return res.json();
  }

  addToCartBtn?.addEventListener('click', async () => {
    qty = 1;
    renderQty();

    const size = document.querySelector('.btn-size.active')?.dataset.size || '';
    const payload = {
      id: addToCartBtn.dataset.productId || Date.now(),
      name: addToCartBtn.dataset.productName || productNameEl?.textContent?.trim() || '',
      brand: addToCartBtn.dataset.productBrand || '',
      size,
      price: parseFloat(addToCartBtn.dataset.productPrice?.replace(/[^0-9.]/g, '') || itemPrice || 0) || 0,
      image: addToCartBtn.dataset.productImage || mainImg?.src || '',
      qty: 1
    };

    await addToServerCart(payload);
    window.cartDrawer?.refreshCart?.();
    window.cartDrawer?.open?.();
  });

  renderQty();

  // Simple carousel nav (cycles thumbs)
  const prevBtn = document.querySelector('.product-gallery-nav.prev');
  const nextBtn = document.querySelector('.product-gallery-nav.next');
  const currentIndex = () => Array.from(thumbs).findIndex(b => b.classList.contains('active'));
  const activateIndex = (idx) => {
    const target = thumbs[idx];
    if (target) target.click();
  };
  prevBtn?.addEventListener('click', () => {
    const idx = currentIndex();
    const next = (idx - 1 + thumbs.length) % thumbs.length;
    activateIndex(next);
  });
  nextBtn?.addEventListener('click', () => {
    const idx = currentIndex();
    const next = (idx + 1) % thumbs.length;
    activateIndex(next);
  });
});
