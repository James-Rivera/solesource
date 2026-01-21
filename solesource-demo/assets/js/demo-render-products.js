document.addEventListener('DOMContentLoaded', function(){
  const grid = document.getElementById('productGrid');
  const products = window.demoProducts || [];
  if(!grid) return;

  // New releases should show only 4 cards
  (products.slice(0, 4)).forEach(p => {
    const col = document.createElement('div');
    col.className = 'col-6 col-md-3 product-col';
    col.innerHTML = `
      <div class="product-card h-100 d-flex flex-column position-relative">
        <a href="#" class="text-decoration-none text-reset d-flex flex-column h-100">
          <div class="ratio ratio-1x1 product-media">
            <img src="${p.image}" alt="${p.name}" class="img-fluid product-image">
          </div>
          <div class="product-body flex-grow-1 d-flex flex-column">
            <div class="product-brand mb-2 text-uppercase small">${p.brand}</div>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
              <div class="product-title fw-bold text-uppercase mb-0">${p.name}</div>
              <span class="badge-gender">Men</span>
            </div>
            <div class="mt-auto product-price">₱${p.price.toLocaleString('en-PH')}</div>
          </div>
        </a>
      </div>
    `;
    grid.appendChild(col);
  });

  // after injection, trigger a refresh for any code that wired to product-card anchors
  if(window.cartDrawer && typeof window.cartDrawer.refreshCart === 'function'){
    window.cartDrawer.refreshCart();
  }
});
