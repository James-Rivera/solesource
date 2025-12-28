<?php
/** @var array $shoe */
$productId = $shoe['id'] ?? null;
$href = $productId ? 'product-details.php?id=' . urlencode($productId) : '#';
?>
<div class="col-6 col-md-3">
    <div class="product-card h-100 d-flex flex-column">
        <a href="<?php echo $href; ?>" class="text-decoration-none text-reset d-flex flex-column h-100">
            <div class="ratio ratio-1x1 product-media">
                <img src="<?php echo htmlspecialchars($shoe['image']); ?>" alt="<?php echo htmlspecialchars($shoe['name']); ?>" class="img-fluid product-image">
            </div>
            <div class="product-body flex-grow-1 d-flex flex-column">
                <div class="product-brand mb-2 text-uppercase small"><?php echo htmlspecialchars($shoe['brand']); ?></div>
                <div class="product-title fw-bold mb-2 text-uppercase"><?php echo htmlspecialchars($shoe['name']); ?></div>
                <div class="mt-auto product-price"><?php echo htmlspecialchars($shoe['price']); ?></div>
            </div>
        </a>
    </div>
</div>
