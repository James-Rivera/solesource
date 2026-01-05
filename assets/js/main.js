document.addEventListener('DOMContentLoaded', () => {
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-wishlist');
    if (!btn) return;
    e.preventDefault();
    const icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('bi-heart');
      icon.classList.toggle('bi-heart-fill');
    }
    const productId = btn.getAttribute('data-product-id') || '';
    console.log('Wishlist toggled for Product ID: ' + productId);
  });
});
