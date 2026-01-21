// Demo site JS: simplified header/footer behaviors without PHP
(function () {
  // sample products used for client-side search demo
  const sampleProducts = [
    { id: 1, name: 'Air Force 1', brand: 'Nike', image: 'assets/img/promo/nike/af1-promo.webp', price: '₱6,500.00' },
    { id: 2, name: 'Pharrell', brand: 'Adidas', image: 'assets/img/promo/adidas/adidas-promo.jpg', price: '₱8,200.00' },
    { id: 3, name: 'Legacy', brand: 'Jordan', image: 'assets/img/promo/jordan/jordan-promo.jpg', price: '₱12,500.00' },
    { id: 4, name: 'Innovation', brand: 'Asics', image: 'assets/img/promo/asics/asics-promo.webp', price: '₱5,400.00' }
  ];

  // expose app user id as null for demo
  window.appUserId = null;

  const input = document.getElementById('globalSearchInput');
  const dropdown = document.getElementById('globalSearchResults') || createDropdown();
  const mobileSearchBtn = document.querySelector('.mobile-search-btn');

  function createDropdown() {
    const el = document.createElement('div');
    el.id = 'globalSearchResults';
    el.className = 'global-search-dropdown d-none';
    document.body.appendChild(el);
    return el;
  }

  function renderResults(items) {
    if (!items || items.length === 0) { dropdown.classList.add('d-none'); dropdown.innerHTML = ''; return; }
    dropdown.innerHTML = items.slice(0,5).map(p => `
      <a class="global-search-item" href="#">
        <img class="global-search-thumb" src="${p.image}" alt="${p.name}">
        <div>
          <div class="global-search-name">${p.name}</div>
          <div class="global-search-brand">${p.brand}</div>
        </div>
        <div class="global-search-price">${p.price}</div>
      </a>
    `).join('');
    dropdown.classList.remove('d-none');
  }

  function queryProducts(term) {
    if (!term) { dropdown.classList.add('d-none'); dropdown.innerHTML=''; return; }
    const q = term.toLowerCase();
    const matches = sampleProducts.filter(p => (p.name + ' ' + p.brand).toLowerCase().includes(q));
    renderResults(matches);
  }

  input?.addEventListener('input', (e) => queryProducts(e.target.value.trim()));
  input?.addEventListener('focus', (e) => queryProducts(e.target.value.trim()));
  input?.addEventListener('blur', () => setTimeout(() => { dropdown.classList.add('d-none'); }, 150));

  mobileSearchBtn?.addEventListener('click', () => {
    document.body.classList.toggle('search-open');
    setTimeout(() => input?.focus(), 120);
  });

  // Mega menu mobile toggle
  document.querySelectorAll('.mega-item.has-panel > a, .mega-item > a').forEach(link => {
    link.addEventListener('click', (e) => {
      if (window.innerWidth <= 992) {
        const li = link.parentElement;
        li.classList.toggle('open');
      }
    });
  });

  // Close nav drawer on outside click (best-effort)
  document.addEventListener('click', (e) => {
    const drawer = document.getElementById('navDrawer');
    if (!drawer) return;
    const isOpen = drawer.classList.contains('show');
    if (!isOpen) return;
    const trigger = e.target.closest('[data-bs-target="#navDrawer"]');
    if (trigger) return;
    if (!drawer.contains(e.target)) {
      const off = bootstrap?.Offcanvas.getInstance(drawer);
      off?.hide();
    }
  });

  function updateMegaTop() {
    const anchor = document.querySelector('.mega-nav-wrapper');
    if (!anchor) return;
    const rect = anchor.getBoundingClientRect();
    const top = rect.bottom + window.scrollY;
    document.documentElement.style.setProperty('--mega-top', `${top}px`);
  }

  window.addEventListener('resize', updateMegaTop);
  window.addEventListener('load', updateMegaTop);
  updateMegaTop();

  // Global loader helpers (no PHP)
  (function () {
    const loader = document.getElementById('globalLoader');
    if (!loader) return;
    function showLoader() { loader.classList.add('active'); }
    function hideLoader() { loader.classList.remove('active'); }
    window.showGlobalLoader = showLoader;
    window.hideGlobalLoader = hideLoader;
    // hide on load
    window.addEventListener('load', hideLoader);
  })();

})();
