<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/head-meta.php'; ?>
</head>
<body class="cart-page">
<?php include 'includes/header.php'; ?>
<?php
  $isEmpty = false; // toggle for mock state
  $cartItems = [
    [
      'name' => 'JORDAN 11 RETRO "COLUMBIA / LEGEND BLUE" 2024',
      'brand' => 'JORDAN',
      'size' => 'US MEN SIZE 9.5',
      'price' => 12000,
      'image' => 'assets/img/products/new/jordan-11-legend-blue.png'
    ],
    [
      'name' => 'JORDAN 11 RETRO "COLUMBIA / LEGEND BLUE" 2024',
      'brand' => 'JORDAN',
      'size' => 'US MEN SIZE 9.5',
      'price' => 12000,
      'image' => 'assets/img/products/new/jordan-11-legend-blue.png'
    ],
  ];
  $recommended = [
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
    ['brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => 'P4,995.00', 'image' => 'assets/img/products/new/air-force-1.png'],
  ];
  $subtotal = $isEmpty ? 0 : array_sum(array_map(fn($i) => $i['price'], $cartItems));
?>

<section class="py-5">
  <div class="container-xxl mt-4">
    <div class="row g-5 align-items-start">
      <div class="col-lg-8">
        <h2 class="cart-heading mb-3">Bag</h2>
        <div class="cart-card">
          <?php if ($isEmpty): ?>
            <div class="py-5 text-center">
              <h5 class="fw-bold text-brand-black mb-1">No items yet</h5>
              <p class="text-muted mb-0">Time to start the collection</p>
            </div>
          <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
              <div class="cart-item-row d-flex align-items-start gap-3">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                <div class="flex-grow-1 d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="cart-item-info">
                      <div class="cart-item-name"><?php echo $item['name']; ?></div>
                      <div class="cart-item-brand"><?php echo $item['brand']; ?></div>
                      <div class="cart-item-size"><?php echo $item['size']; ?></div>
                    </div>
                    <div class="cart-item-price-lg">P<?php echo number_format($item['price'], 2); ?></div>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="cart-qty-box">
                      <button class="cart-qty-btn" type="button" aria-label="Remove"><i class="bi bi-trash"></i></button>
                      <span class="fw-bold">1</span>
                      <button class="cart-qty-btn" type="button" aria-label="Add"><i class="bi bi-plus-lg"></i></button>
                    </div>
                    <button class="wishlist-btn" type="button" aria-label="Move to wishlist"><i class="bi bi-heart"></i></button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="shipping-banner d-flex align-items-center gap-2">
          <i class="bi bi-shop"></i>
          <span>Free shipping for SOLESOURCE Members.</span>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="summary-card p-3 summary-sticky text-brand-black">
          <h6 class="summary-title">Summary</h6>
          <div class="summary-row">
            <span>Subtotal</span>
            <span><?php echo $isEmpty ? '-' : 'P' . number_format($subtotal, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Delivery &amp; Handling</span>
            <span><?php echo $isEmpty ? '-' : 'P0.00'; ?></span>
          </div>
          <div class="summary-divider"></div>
          <div class="summary-row summary-total">
            <span>Total</span>
            <span><?php echo $isEmpty ? '-' : 'P' . number_format($subtotal, 2); ?></span>
          </div>
          <button class="btn checkout-btn w-100 mb-3" type="button" <?php echo $isEmpty ? 'disabled' : ''; ?>>Go to checkout</button>
          <div class="small text-muted mb-2">Checkout safely using your preferred payment method</div>
          <div class="d-flex align-items-center gap-2 payment-icons">
            <div class="payment-pill" aria-label="GCash">
              <img src="assets/img/icons/gcash-seeklogo.svg" alt="GCash logo" class="payment-icon-img">
            </div>
            <div class="payment-pill" aria-label="PayPal">
              <img src="assets/img/icons/paypal-seeklogo.svg" alt="PayPal logo" class="payment-icon-img">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="pb-5">
  <div class="container">
    <h6 class="recommend-text text-brand-black mb-4">Recommended for you</h6>
    <div class="position-relative">
      <button class="rec-nav rec-nav-prev" type="button" aria-label="Scroll left"><i class="bi bi-chevron-left"></i></button>
      <button class="rec-nav rec-nav-next" type="button" aria-label="Scroll right"><i class="bi bi-chevron-right"></i></button>
      <div class="recommend-scroller" id="recScroller">
        <?php foreach ($recommended as $rec): ?>
          <div class="recommend-card h-100">
            <img draggable="false" src="<?php echo $rec['image']; ?>" alt="<?php echo htmlspecialchars($rec['name']); ?>" class="recommend-img">
            <div class="p-3">
              <div class="small text-muted text-uppercase mb-1"><?php echo $rec['brand']; ?></div>
              <div class="fw-bold text-brand-black" style="font-size: 0.95rem; text-transform: uppercase;"> <?php echo $rec['name']; ?> </div>
              <div class="small text-brand-black mt-1"><?php echo $rec['price']; ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="recommend-progress">
      <div class="recommend-thumb" id="recThumb"></div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
