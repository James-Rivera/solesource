<?php
session_start();
require_once 'includes/connect.php';

$convert_size_label = static function ($row, $desiredSystem = 'US', $fallback = '') {
  if (!$row) { return $fallback; }
  $usLabel = $row['size_label'] ?? $fallback;
  $gender = strtolower($row['gender'] ?? 'men');
  if (strtolower($desiredSystem) !== 'eu') { return $usLabel; }
  $numeric = (float) (preg_replace('/[^0-9.]/', '', $usLabel) ?: 0);
  if ($numeric <= 0) { return $usLabel; }
  $offset = ($gender === 'women') ? 31.5 : 33.0;
  $eu = $numeric + $offset;
  $formatted = floor($eu) == $eu ? number_format($eu, 0) : number_format($eu, 1);
  return 'EU ' . $formatted;
};

// Handle quantity updates and removals submitted from the page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
  $size = isset($_POST['size']) ? trim($_POST['size']) : '';
  $sizeId = isset($_POST['size_id']) && $_POST['size_id'] !== '' ? (int) $_POST['size_id'] : null;
  $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : null;

  if ($id && $size && isset($_SESSION['cart'])) {
    $key = $id . ':' . ($sizeId !== null ? $sizeId : $size);
    $legacyKey = $id . ':' . $size;
    $targetKey = isset($_SESSION['cart'][$key]) ? $key : (isset($_SESSION['cart'][$legacyKey]) ? $legacyKey : null);

    if ($action === 'remove' && $targetKey !== null && isset($_SESSION['cart'][$targetKey])) {
      unset($_SESSION['cart'][$targetKey]);
    } elseif ($action === 'update' && $qty !== null && $targetKey !== null && isset($_SESSION['cart'][$targetKey])) {
      if ($qty <= 0) {
        unset($_SESSION['cart'][$targetKey]);
      } else {
        $_SESSION['cart'][$targetKey]['qty'] = $qty;
        if ($sizeId !== null) {
          $_SESSION['cart'][$targetKey]['size_id'] = $sizeId;
        }
      }
    }
  }

  header('Location: cart.php');
  exit;
}

// Build cart dataset from session and DB
$sessionCart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartItems = [];
$grandTotal = 0;
$cartProductIds = [];
$userWishlistIds = [];
$sizeRows = [];

// Fetch wishlist ids for current user (for heart state)
if (isset($_SESSION['user_id'])) {
  $uid = (int) $_SESSION['user_id'];
  $wsStmt = $conn->prepare("SELECT product_id FROM user_wishlist WHERE user_id = ?");
  $wsStmt->bind_param('i', $uid);
  $wsStmt->execute();
  $wsRes = $wsStmt->get_result();
  while ($row = $wsRes->fetch_assoc()) {
    $userWishlistIds[] = (int) $row['product_id'];
  }
  $wsStmt->close();
}

if (!empty($sessionCart)) {
  $productIds = array_unique(array_map(fn($item) => (int) ($item['id'] ?? 0), $sessionCart));
  $productIds = array_filter($productIds, fn($id) => $id > 0);
  $productIds = array_values($productIds); // reindex for binding refs

  $sizeIds = array_unique(array_filter(array_map(fn($item) => isset($item['size_id']) ? (int) $item['size_id'] : 0, $sessionCart)));

  if ($sizeIds) {
    $ph = implode(',', array_fill(0, count($sizeIds), '?'));
    $typesSz = str_repeat('i', count($sizeIds));
    $stmtSz = $conn->prepare("SELECT id, product_id, size_label, size_system, gender, stock_quantity FROM product_sizes WHERE id IN ($ph) AND is_active = 1");
    $bindSz = [$typesSz];
    foreach ($sizeIds as $idx => $sid) { $bindSz[] = &$sizeIds[$idx]; }
    call_user_func_array([$stmtSz, 'bind_param'], $bindSz);
    $stmtSz->execute();
    $resSz = $stmtSz->get_result();
    while ($row = $resSz->fetch_assoc()) {
      $sizeRows[(int) $row['id']] = $row;
    }
    $stmtSz->close();
  }

  if ($productIds) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");

    $bindParams = [$types];
    foreach ($productIds as $idx => $pid) {
      $bindParams[] = &$productIds[$idx];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
      $products[$row['id']] = $row;
    }
    $stmt->close();

    foreach ($sessionCart as $item) {
      $id = (int) ($item['id'] ?? 0);
      $size = $item['size'] ?? '';
      $sizeId = isset($item['size_id']) ? (int) $item['size_id'] : null;
      $sizeSystem = $item['size_system'] ?? 'US';
      if (!$id || !isset($products[$id])) {
        continue;
      }
      $cartProductIds[] = $id;
      $product = $products[$id];
      $qty = max(1, (int) ($item['qty'] ?? 1));
      $unitPrice = isset($product['price']) ? (float) $product['price'] : (float) ($item['price'] ?? 0);
      $lineTotal = $unitPrice * $qty;
      $grandTotal += $lineTotal;

      $sizeRow = $sizeId && isset($sizeRows[$sizeId]) ? $sizeRows[$sizeId] : null;
      $displaySize = $sizeRow ? $convert_size_label($sizeRow, $sizeSystem, $size) : $size;

      $cartItems[] = [
        'id' => $id,
        'name' => $product['name'] ?? ($item['name'] ?? ''),
        'brand' => $product['brand'] ?? ($item['brand'] ?? ''),
        'size' => $displaySize,
        'size_id' => $sizeId,
        'price' => $unitPrice,
        'qty' => $qty,
        'image' => $product['image'] ?? ($item['image'] ?? ''),
        'line_total' => $lineTotal,
        'in_wishlist' => in_array($id, $userWishlistIds, true),
      ];
    }
  }
}

$isEmpty = empty($cartItems);
$recommended = [];
$excludeIds = array_unique($cartProductIds);
$excludeIds = array_values($excludeIds);

$whereParts = ["status = 'active'"];
$typesRec = '';
$paramsRec = [];
if (!empty($excludeIds)) {
  $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
  $whereParts[] = "id NOT IN ($placeholders)";
  $typesRec .= str_repeat('i', count($excludeIds));
  $paramsRec = $excludeIds;
}

$whereSql = implode(' AND ', $whereParts);
$recSql = "SELECT * FROM products WHERE $whereSql ORDER BY RAND() LIMIT 10";
$stmtRec = $conn->prepare($recSql);
if ($typesRec) {
  $bindRec = [$typesRec];
  foreach ($paramsRec as $idx => $pid) {
    $bindRec[] = &$paramsRec[$idx];
  }
  call_user_func_array([$stmtRec, 'bind_param'], $bindRec);
}

$stmtRec->execute();
$recRes = $stmtRec->get_result();
while ($row = $recRes->fetch_assoc()) {
  $recommended[] = $row;
}
$stmtRec->close();
?>
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

<section class="py-5">
  <div class="container-xxl mt-4">
    <div class="row g-5 align-items-start">
      <div class="col-lg-8">
        <h2 class="cart-heading mb-3">Bag</h2>
        <div class="cart-card">
          <?php if ($isEmpty): ?>
            <div class="py-5 text-center">
              <h5 class="fw-bold text-brand-black mb-1">Your cart is empty</h5>
              <p class="text-muted mb-0">Time to start the collection</p>
            </div>
          <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
              <div class="cart-item-row d-flex align-items-start gap-3">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                <div class="flex-grow-1 d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="cart-item-info">
                      <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                      <div class="cart-item-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                      <div class="cart-item-size">Size <?php echo htmlspecialchars($item['size']); ?></div>
                    </div>
                    <div class="cart-item-price-lg">₱<?php echo number_format($item['line_total'], 2); ?></div>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="cart-qty-box">
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                        <input type="hidden" name="size_id" value="<?php echo htmlspecialchars((string) ($item['size_id'] ?? '')); ?>">
                        <input type="hidden" name="qty" value="<?php echo max(0, $item['qty'] - 1); ?>">
                        <button class="cart-qty-btn" type="submit" aria-label="Decrease quantity">
                          <i class="bi <?php echo $item['qty'] > 1 ? 'bi-dash-lg' : 'bi-trash'; ?>"></i>
                        </button>
                      </form>
                      <span class="fw-bold"><?php echo (int) $item['qty']; ?></span>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                        <input type="hidden" name="size_id" value="<?php echo htmlspecialchars((string) ($item['size_id'] ?? '')); ?>">
                        <input type="hidden" name="qty" value="<?php echo $item['qty'] + 1; ?>">
                        <button class="cart-qty-btn" type="submit" aria-label="Increase quantity"><i class="bi bi-plus-lg"></i></button>
                      </form>
                    </div>
                    <button class="wishlist-btn btn-wishlist" type="button" aria-label="Toggle wishlist" data-product-id="<?php echo (int) $item['id']; ?>" data-in-wishlist="<?php echo $item['in_wishlist'] ? '1' : '0'; ?>">
                      <i class="bi <?php echo $item['in_wishlist'] ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                    </button>
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
            <span><?php echo $isEmpty ? '-' : '₱' . number_format($grandTotal, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Delivery &amp; Handling</span>
            <span><?php echo $isEmpty ? '-' : 'P0.00'; ?></span>
          </div>
          <div class="summary-divider"></div>
          <div class="summary-row summary-total">
            <span>Total</span>
            <span><?php echo $isEmpty ? '-' : '₱' . number_format($grandTotal, 2); ?></span>
          </div>
          <a class="btn checkout-btn w-100 mb-3 <?php echo $isEmpty ? 'disabled' : ''; ?>" href="<?php echo $isEmpty ? '#' : 'checkout.php'; ?>" <?php echo $isEmpty ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>Go to checkout</a>
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
            <a href="product-details.php?id=<?php echo urlencode($rec['id']); ?>" class="text-decoration-none text-reset d-flex flex-column h-100">
              <div class="ratio ratio-1x1 product-media">
                <img src="<?php echo htmlspecialchars($rec['image']); ?>" alt="<?php echo htmlspecialchars($rec['name']); ?>" class="img-fluid product-image">
              </div>
              <div class="product-body flex-grow-1 d-flex flex-column p-3">
                <div class="product-brand mb-2 text-uppercase small"><?php echo htmlspecialchars($rec['brand']); ?></div>
                <div class="product-title fw-bold mb-2 text-uppercase"><?php echo htmlspecialchars($rec['name']); ?></div>
                <div class="mt-auto product-price">₱<?php echo number_format((float) $rec['price'], 2); ?></div>
              </div>
            </a>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.btn-wishlist');
  buttons.forEach(btn => {
    btn.addEventListener('click', async () => {
      const productId = btn.getAttribute('data-product-id');
      try {
        const res = await fetch('includes/wishlist-toggle.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: Number(productId) })
        });
        if (res.status === 401) {
          window.location.href = 'login.php?redirect=cart';
          return;
        }
        const data = await res.json();
        if (data?.ok) {
          const icon = btn.querySelector('i');
          const added = data.action === 'added';
          btn.setAttribute('data-in-wishlist', added ? '1' : '0');
          if (icon) {
            icon.className = added ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
          }
        }
      } catch (err) {
        console.error('Wishlist toggle failed', err);
      }
    });
  });
});
</script>
</body>
</html>
