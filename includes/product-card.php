<?php
/** @var array $shoe */
$productId = $shoe['id'] ?? null;
$href = $productId ? 'product-details.php?id=' . urlencode($productId) : '#';
$stockTotal = (int)($shoe['stock_total'] ?? $shoe['stock_quantity'] ?? 0);
?>
<div class="col-6 col-md-3">
    <div class="product-card h-100 d-flex flex-column position-relative">
        <?php if ($stockTotal <= 0): ?>
            <span class="badge bg-dark position-absolute top-0 end-0 m-2">Out of Stock</span>
        <?php endif; ?>
        <a href="<?php echo $href; ?>" class="text-decoration-none text-reset d-flex flex-column h-100">
            <div class="ratio ratio-1x1 product-media">
                <img src="<?php echo htmlspecialchars($shoe['image']); ?>" alt="<?php echo htmlspecialchars($shoe['name']); ?>" class="img-fluid product-image">
            </div>
            <div class="product-body flex-grow-1 d-flex flex-column">
                <div class="product-brand mb-2 text-uppercase small"><?php echo htmlspecialchars($shoe['brand']); ?></div>
                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                    <div class="product-title fw-bold text-uppercase mb-0"><?php echo htmlspecialchars($shoe['name']); ?></div>
                    <?php if (!empty($shoe['gender'])): ?>
                        <span class="badge-gender"><?php echo htmlspecialchars($shoe['gender']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mt-auto product-price"><?php echo htmlspecialchars($shoe['price']); ?></div>
            </div>
        </a>
    </div>
</div>
