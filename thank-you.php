<?php
session_start();
$orderNumber = $_GET['order'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Order Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/head-meta.php'; ?>
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    <main class="py-5">
        <div class="container text-center">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            </div>
            <h1 class="fw-bold mb-3">Order Successful!</h1>
            <?php if ($orderNumber): ?>
                <p class="text-muted mb-4">Your order number is <strong><?php echo htmlspecialchars($orderNumber); ?></strong>.</p>
            <?php else: ?>
                <p class="text-muted mb-4">Thank you for your purchase.</p>
            <?php endif; ?>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a class="btn btn-primary" href="shop.php">Continue Shopping</a>
                <a class="btn btn-outline-dark" href="profile.php">View My Profile</a>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
