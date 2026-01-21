document.addEventListener('DOMContentLoaded', function(){
  const products = window.demoProducts || [];

  function renderGrid(list, containerId, limit){
    const el = document.getElementById(containerId);
    if(!el) return;
    el.innerHTML = '';
    (list.slice(0, limit)).forEach(p => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-3 product-col';
      col.innerHTML = `
        <div class="product-card h-100 d-flex flex-column position-relative">
          <a href="#" class="text-decoration-none text-reset d-flex flex-column h-100">
            <div class="ratio ratio-1x1 product-media"><img src="${p.image}" alt="${p.name}" class="img-fluid product-image"></div>
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
      el.appendChild(col);
    });
  }

  // Best selling: show 4 products (demo)
  renderGrid(products, 'bestSellingGrid', 4);

  // Featured: show 8 products (demo selection)
  const featured = products.slice().sort(() => 0.5 - Math.random()).slice(0,8);
  renderGrid(featured, 'featuredGrid', 8);

  // Retro carousel: add a couple of promo slides using hero posters
  const retroContainer = document.getElementById('retroSlides');
  if(retroContainer){
    const slides = [
      {title: 'RETRO ARCHIVE', subtitle: 'Timeless silhouettes. Verified authentic.', image: 'assets/img/promo/jordan/jordan-promo.jpg'},
      {title: 'THE TERRACE CLASSIC', subtitle: 'The Terrace Revival', image: 'assets/img/promo/adidas/adidas-promo.jpg'},
      {title: 'ICONIC BY DESIGN', subtitle: 'Archive Series', image: 'assets/img/promo/nike/af1-promo.webp'}
    ];
    // clear existing except first
    retroContainer.innerHTML = '';
    slides.forEach((s, i) => {
      const item = document.createElement('div');
      item.className = 'carousel-item' + (i===0? ' active':'');
      item.innerHTML = `
        <div class="retro-slide d-flex align-items-center justify-content-center text-center" style="background-image: url('${s.image}'); background-size: cover; background-position: center; min-height: 260px;">
          <div class="retro-overlay position-absolute top-0 start-0 w-100 h-100"></div>
          <div class="position-relative text-white">
            <h2 class="retro-title mb-2">${s.title}</h2>
            <p class="retro-subtitle mb-0">${s.subtitle}</p>
          </div>
        </div>
      `;
      retroContainer.appendChild(item);
    });
  }

  // re-bind cart add handlers since we injected new product-card anchors
  if(window.cartDrawer && typeof window.cartDrawer.refreshCart === 'function'){
    // wait a tick then refresh bindings by reloading cart script behavior
    setTimeout(() => {
      // trigger demo-cart click wiring by re-invoking its anchor listeners
      document.querySelectorAll('.product-card a').forEach(a => {
        if(!a.dataset.demoWired){
          a.addEventListener('click', (e) => {
            e.preventDefault();
            const img = a.querySelector('.product-image');
            const name = a.querySelector('.product-title')?.textContent?.trim() || 'Product';
            const brand = a.querySelector('.product-brand')?.textContent?.trim() || '';
            const priceText = a.querySelector('.product-price')?.textContent?.replace(/[^0-9\.]/g, '') || '0';
            const price = parseFloat(priceText.replace(/,/g, '')) || 0;
            const id = Math.floor(Math.random() * 1000000);
            // use demo cart API if present
            if(window.apiAddFallback){
              window.apiAddFallback({ id, name, brand, price, image: img?.src || '', size: '' }).then(() => {
                window.cartDrawer.open();
                window.cartDrawer.refreshCart();
              });
            } else if(window.cartDrawer){
              // fallback: open cart
              window.cartDrawer.open();
            }
          });
          a.dataset.demoWired = '1';
        }
      });
    }, 50);
  }
});
