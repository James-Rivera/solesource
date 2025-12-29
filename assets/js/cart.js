document.addEventListener('DOMContentLoaded', () => {
  const cartDrawerEl = document.getElementById('cartDrawer');
  if (!cartDrawerEl) return;

  const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(cartDrawerEl);
  const emptyState = cartDrawerEl.querySelector('.cart-empty');
  const cartItems = cartDrawerEl.querySelector('.cart-items');
  const subtotalEl = document.getElementById('cartSubtotal');

  const api = {
    get: () => fetch('includes/cart-get.php').then(r => r.json()),
    add: (p) => fetch('includes/cart-add.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(p) }).then(r => r.json()),
    update: (p) => fetch('includes/cart-update.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(p) }).then(r => r.json()),
    remove: (p) => fetch('includes/cart-remove.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(p) }).then(r => r.json()),
  };

  function toggleEmpty(hasItems) {
    if (emptyState) emptyState.classList.toggle('d-none', hasItems);
  }

  function renderCart(data) {
    if (!cartItems) return;
    cartItems.innerHTML = '';
    const cart = data?.cart || [];
    const subtotal = data?.subtotal || 0;
    toggleEmpty(cart.length > 0);

    cart.forEach(item => {
      const row = document.createElement('div');
      row.className = 'cart-item-row d-flex align-items-start gap-3';
      row.innerHTML = `
        <img class="cart-item-img" src="${item.image || ''}" alt="${item.name || ''}">
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between cart-item-header">
            <div class="cart-item-title">${item.name || ''}</div>
            <div class="cart-item-price fw-bold">₱${(item.price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
          </div>
          <div class="cart-item-meta mb-2">Size ${item.size || ''}${item.brand ? ' · ' + item.brand : ''}</div>
          <div class="d-flex align-items-center border rounded cart-qty-box" style="width: fit-content; gap: 15px; padding: 5px 10px; border-color: #dee2e6;">
            <button class="btn btn-sm p-0 border-0 bg-transparent text-dark cart-dec-btn" type="button" aria-label="Decrease">
              <i class="bi ${item.qty > 1 ? 'bi-dash-lg' : 'bi-trash'}"></i>
            </button>
            <span class="mx-2 fw-bold cart-qty-value">${item.qty || 1}</span>
            <button class="btn btn-sm p-0 border-0 bg-transparent text-dark cart-plus-btn" type="button" aria-label="Add one"><i class="bi bi-plus-lg"></i></button>
          </div>
        </div>
      `;

      const decBtn = row.querySelector('.cart-dec-btn');
      const plusBtn = row.querySelector('.cart-plus-btn');
      const removeBtn = row.querySelector('.remove-cart-line');

      decBtn?.addEventListener('click', async () => {
        const nextQty = (item.qty || 1) - 1;
        const data = await api.update({ id: item.id, size: item.size, qty: nextQty });
        renderCart(data);
      });

      plusBtn?.addEventListener('click', async () => {
        const data = await api.add({ id: item.id, size: item.size, name: item.name, brand: item.brand, price: item.price, image: item.image, qty: 1 });
        renderCart(data);
      });

      removeBtn?.addEventListener('click', async () => {
        const data = await api.remove({ id: item.id, size: item.size });
        renderCart(data);
      });

      cartItems.appendChild(row);
    });

    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  async function loadCart() {
    const data = await api.get();
    renderCart(data);
  }

  cartDrawerEl.addEventListener('show.bs.offcanvas', loadCart);
  loadCart();

  window.cartDrawer = {
    open: () => offcanvas.show(),
    close: () => offcanvas.hide(),
    refreshCart: loadCart
  };
});
