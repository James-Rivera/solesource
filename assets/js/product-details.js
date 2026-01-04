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
  const hiddenSizeId = document.getElementById('selectedSizeId');
  const hiddenSystem = document.getElementById('selectedSystem');
  const hiddenGender = document.getElementById('selectedGender');
  const systemBtns = document.querySelectorAll('.btn-system');
  const genderBtns = document.querySelectorAll('.btn-gender');
  const normalizeGender = (g) => {
    if (!g) return 'Men';
    const lower = g.toLowerCase();
    if (lower === 'women') return 'Women';
    if (lower === 'both') return 'Both';
    return 'Men';
  };

  function currentGender() {
    const activeToggle = document.querySelector('.btn-gender.active');
    const fromToggle = normalizeGender(activeToggle?.dataset.gender || '');
    const fromHidden = normalizeGender(hiddenGender?.value || '');
    const resolved = fromToggle || fromHidden || 'Men';
    return resolved === 'Both' ? 'Men' : resolved;
  }

  function toEuLabel(usLabel) {
    if (!usLabel) return usLabel;
    const numeric = parseFloat((usLabel.match(/([0-9]+(?:\.[0-9]+)?)/)?.[1]) || '');
    if (Number.isNaN(numeric)) return usLabel;
    const gender = currentGender();
    const offset = gender === 'Women' ? 31.5 : 33;
    const euVal = numeric + offset;
    const formatted = Number.isInteger(euVal) ? euVal.toFixed(0) : euVal.toFixed(1);
    return `EU ${formatted}`;
  }

  function refreshSizeLabels() {
    const sys = hiddenSystem?.value || 'US';
    sizeBtns.forEach(btn => {
      const base = btn.dataset.usLabel || btn.dataset.size || '';
      const label = sys === 'EU' ? toEuLabel(base) : base;
      const labelSpan = btn.querySelector('.size-text');
      if (labelSpan) {
        labelSpan.textContent = label;
      }
    });
  }

  function filterSizes() {
    const sys = hiddenSystem?.value || systemBtns?.[0]?.dataset.system || 'US';
    const gen = currentGender();
    let firstSelectable = null;
    sizeBtns.forEach(btn => {
      const sizeGender = normalizeGender(btn.dataset.sizeGender || '');
      const matchGender = sizeGender === gen || sizeGender === 'Both';
      const matchSystem = sys === 'EU' ? true : (!sys || btn.dataset.sizeSystem === sys);
      const match = matchGender && matchSystem;
      const tile = btn.closest('.size-tile');
      const hide = !match;
      btn.style.display = hide ? 'none' : '';
      btn.classList.toggle('d-none', hide);
      btn.hidden = hide;
      if (tile) {
        tile.style.display = hide ? 'none' : '';
        tile.classList.toggle('d-none', hide);
        tile.hidden = hide;
      }
      if (hide && btn.classList.contains('active')) {
        btn.classList.remove('active');
      }
      const stockVal = parseInt(btn.dataset.stock || '0', 10);
      if (match && !btn.classList.contains('disabled') && stockVal > 0 && !firstSelectable) {
        firstSelectable = btn;
      }
    });
    // ensure one size is active
    const activeVisible = Array.from(sizeBtns).find(b => b.classList.contains('active') && b.style.display !== 'none');
    if (!activeVisible && firstSelectable) {
      sizeBtns.forEach(b => b.classList.remove('active'));
      firstSelectable.classList.add('active');
      hiddenSize.value = firstSelectable.dataset.size || '';
      hiddenSizeId.value = firstSelectable.dataset.sizeId || '';
      hiddenSystem.value = firstSelectable.dataset.sizeSystem || hiddenSystem.value || '';
      hiddenGender.value = firstSelectable.dataset.sizeGender || hiddenGender.value || '';
    }
    updateAddToCartAvailability();
    refreshSizeLabels();
  }
  sizeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.classList.contains('disabled') || btn.disabled) return;
      sizeBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (cartSizePreview) cartSizePreview.textContent = btn.dataset.size;
      if (hiddenSize) hiddenSize.value = btn.dataset.size;
      if (hiddenSizeId) hiddenSizeId.value = btn.dataset.sizeId || '';
      if (hiddenGender && btn.dataset.sizeGender) hiddenGender.value = btn.dataset.sizeGender;
      updateAddToCartAvailability();
      refreshSizeLabels();
    });
  });

  systemBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      systemBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (hiddenSystem) hiddenSystem.value = btn.dataset.system || '';
      filterSizes();
      refreshSizeLabels();
    });
  });

  genderBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      genderBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (hiddenGender) hiddenGender.value = btn.dataset.gender || '';
      filterSizes();
      refreshSizeLabels();
    });
  });

  // Initial label normalization
  refreshSizeLabels();

  // Quantity controls and subtotal update
  const qtyValue = document.getElementById('qtyValue');
  const qtyPlus = document.getElementById('qtyPlus');
  const qtyTrash = document.getElementById('qtyTrash');
  const cartItemRow = document.querySelector('.cart-item-row');
  const cartSubtotal = document.getElementById('cartSubtotal');
  const addToCartBtn = document.getElementById('addToCartBtn');
  const addToCartDefaultText = addToCartBtn?.textContent?.trim() || 'Add to Cart';
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

  function updateAddToCartAvailability() {
    const activeSizeBtn = document.querySelector('.btn-size.active');
    const stockVal = parseInt(activeSizeBtn?.dataset.stock || '0', 10);
    const inStock = !Number.isNaN(stockVal) && stockVal > 0;
    if (!addToCartBtn) return;
    if (inStock) {
      addToCartBtn.disabled = false;
      addToCartBtn.classList.remove('disabled');
      addToCartBtn.textContent = addToCartDefaultText;
    } else {
      addToCartBtn.disabled = true;
      addToCartBtn.classList.add('disabled');
      addToCartBtn.textContent = 'Out of Stock';
    }
  }

  async function addToServerCart(payload) {
    const res = await fetch('includes/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    return res.json();
  }

  addToCartBtn?.addEventListener('click', async () => {
    if (addToCartBtn.disabled) return;
    qty = 1;
    renderQty();

    const activeSizeBtn = document.querySelector('.btn-size.active');
    const size = activeSizeBtn?.textContent || '';
    const sizeId = activeSizeBtn?.dataset.sizeId || '';
    const sizeSystem = hiddenSystem?.value || activeSizeBtn?.dataset.sizeSystem || '';
    const sizeGender = activeSizeBtn?.dataset.sizeGender || '';
    const displaySize = activeSizeBtn?.querySelector('.size-text')?.textContent?.trim() || size;
    const payload = {
      id: addToCartBtn.dataset.productId || Date.now(),
      name: addToCartBtn.dataset.productName || productNameEl?.textContent?.trim() || '',
      brand: addToCartBtn.dataset.productBrand || '',
      size: displaySize,
      size_id: sizeId,
      size_system: sizeSystem,
      size_gender: sizeGender,
      price: parseFloat(addToCartBtn.dataset.productPrice?.replace(/[^0-9.]/g, '') || itemPrice || 0) || 0,
      image: addToCartBtn.dataset.productImage || mainImg?.src || '',
      qty: 1
    };

    const data = await addToServerCart(payload);

    const badge = document.getElementById('header-cart-count');
    if (badge && data?.cart) {
      const totalQty = data.cart.reduce((sum, item) => sum + (item.qty || 0), 0);
      badge.textContent = totalQty;
      if (totalQty > 0) {
        badge.classList.remove('d-none');
      }
    } else if (badge) {
      const current = parseInt(badge.textContent || '0', 10) || 0;
      const next = current + 1;
      badge.textContent = next;
      badge.classList.remove('d-none');
    }

    window.cartDrawer?.refreshCart?.();
    window.cartDrawer?.open?.();
  });

  renderQty();
  filterSizes();
  updateAddToCartAvailability();
  refreshSizeLabels();

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
