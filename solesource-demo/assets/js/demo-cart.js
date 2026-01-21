document.addEventListener('DOMContentLoaded', () => {
  const cartDrawerEl = document.getElementById('cartDrawer');

  const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(cartDrawerEl);
  const emptyState = cartDrawerEl.querySelector('.cart-empty');
  const cartItems = cartDrawerEl.querySelector('.cart-items');
  const subtotalEl = document.getElementById('cartSubtotal');

  // demo storage key
  const KEY = 'solesource_demo_cart_v1';

  const storage = {
    get: () => {
      try {
        const raw = localStorage.getItem(KEY);
        return raw ? JSON.parse(raw) : { cart: [], subtotal: 0 };
      } catch (e) {
        return { cart: [], subtotal: 0 };
      }
    },
    set: (data) => localStorage.setItem(KEY, JSON.stringify(data))
  };

  function recalc(cart) {
    let subtotal = 0;
    cart.forEach(i => { subtotal += (i.price || 0) * (i.qty || 1); });
    return { cart, subtotal };
  }

  const api = {
    get: async () => {
      return storage.get();
    },
    add: async (p) => {
      const state = storage.get();
      const existing = state.cart.find(x => x.id === p.id && x.size === p.size);
      if (existing) existing.qty = (existing.qty || 1) + (p.qty || 1);
      else state.cart.push({ id: p.id, name: p.name, brand: p.brand, price: p.price || 0, image: p.image || '', size: p.size || '', size_id: p.size_id || null, qty: p.qty || 1 });
      const next = recalc(state.cart);
      storage.set(next);
      return next;
    },
    update: async (p) => {
      const state = storage.get();
      const idx = state.cart.findIndex(x => x.id === p.id && x.size === p.size);
      if (idx === -1) return state;
      if (p.qty <= 0) state.cart.splice(idx, 1);
      else state.cart[idx].qty = p.qty;
      const next = recalc(state.cart);
      storage.set(next);
      return next;
    },
    remove: async (p) => {
      const state = storage.get();
      const idx = state.cart.findIndex(x => x.id === p.id && x.size === p.size);
      if (idx > -1) state.cart.splice(idx, 1);
      const next = recalc(state.cart);
      storage.set(next);
      return next;
    }
  };

  // expose add helper for demo renderers to call when wiring add-to-cart
  window.apiAddFallback = api.add;

  function toggleEmpty(hasItems) {
    if (emptyState) emptyState.classList.toggle('d-none', hasItems);
  }

  function renderCart(data) {
    if (!cartItems) return;
    cartItems.innerHTML = '';
    const cart = data?.cart || [];
    const subtotal = data?.subtotal || 0;
    toggleEmpty(cart.length > 0);

    // update header cart count badge
    const headerCount = document.getElementById('header-cart-count');
    const totalQty = cart.reduce((s, it) => s + (it.qty || 0), 0);
    if (headerCount) {
      headerCount.textContent = totalQty;
      headerCount.classList.toggle('d-none', totalQty === 0);
    }

    cart.forEach(item => {
      const row = document.createElement('div');
      row.className = 'cart-item-row d-flex align-items-start gap-3';
      row.innerHTML = `
        <img class="cart-item-img" src="${item.image || ''}" alt="${item.name || ''}" style="width:64px;height:64px;object-fit:contain">
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

      decBtn?.addEventListener('click', async () => {
        const nextQty = (item.qty || 1) - 1;
        const data = await api.update({ id: item.id, size: item.size, size_id: item.size_id, qty: nextQty });
        renderCart(data);
      });

      plusBtn?.addEventListener('click', async () => {
        const data = await api.add({ id: item.id, size: item.size, size_id: item.size_id, name: item.name, brand: item.brand, price: item.price, image: item.image, qty: 1 });
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

  cartDrawerEl?.addEventListener('show.bs.offcanvas', loadCart);
  loadCart();

  window.cartDrawer = {
    open: () => offcanvas.show(),
    close: () => offcanvas.hide(),
    refreshCart: loadCart
  };

  // Hide the chat toggle while the cart drawer is visible to avoid overlap
  cartDrawerEl?.addEventListener('show.bs.offcanvas', () => {
    window.dispatchEvent(new CustomEvent('ai-chat:close'));
    const chatToggle = document.querySelector('.ai-chat-toggle');
    const chatPanel = document.querySelector('.ai-chat-panel');
    if (chatToggle) chatToggle.style.display = 'none';
    if (chatPanel) chatPanel.classList.remove('open');
  });

  cartDrawerEl?.addEventListener('hidden.bs.offcanvas', () => {
    const chatToggle = document.querySelector('.ai-chat-toggle');
    if (chatToggle) chatToggle.style.display = '';
  });

  // wire demo "Add to cart" buttons on product cards
  document.querySelectorAll('.product-card a').forEach(a => {
    a.addEventListener('click', (e) => {
      // for demo, prevent navigation and add sample product
      e.preventDefault();
      const img = a.querySelector('.product-image');
      const name = a.querySelector('.product-title')?.textContent?.trim() || 'Product';
      const brand = a.querySelector('.product-brand')?.textContent?.trim() || '';
      const priceText = a.querySelector('.product-price')?.textContent?.replace(/[^0-9\.]/g, '') || '0';
      const price = parseFloat(priceText.replace(/,/g, '')) || 0;
      const id = Math.floor(Math.random() * 1000000);
      api.add({ id, name, brand, price, image: img?.src || '', size: '' }).then(() => {
        window.cartDrawer.open();
        loadCart();
      });
    });
  });

  // open cart when header cart button is clicked
  document.querySelectorAll('.cart-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
      window.cartDrawer?.open();
    });
  });

});
