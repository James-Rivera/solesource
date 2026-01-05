<?php

// Builds the checkout receipt email with fully inline styles for broad client compatibility.
function build_receipt_email(array $data): array
{
    $orderId = (int)($data['orderId'] ?? 0);
    $orderNumber = $data['orderNumber'] ?? '';
    $fullName = $data['fullName'] ?? '';
    $paymentMethod = $data['paymentMethod'] ?? '';
    $shippingAddress = $data['shippingAddress'] ?? '';
    $cartItems = $data['cartItems'] ?? [];
    $totalAmount = (float)($data['totalAmount'] ?? 0);
    $orderDate = $data['orderDate'] ?? '';
    $estimatedArrival = $data['estimatedArrival'] ?? '';

    $envAppUrl = getenv('APP_URL') ?: ($_SERVER['APP_URL'] ?? '');
    $baseUrl = $envAppUrl ? rtrim($envAppUrl, '/') : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $assetBase = rtrim($baseUrl, '/') . '/';
    $orderViewUrl = $baseUrl . '/view_order.php?id=' . urlencode($orderId);
    $accent = '#E9713F';
    $brandBlack = '#121212';
    $brandDarkGray = '#333333';
    $muted = '#6f6f6f';
    $panelBg = '#F8F9FA';

    // Build item rows (table layout is safest across email clients)
    $itemsHtml = '';
    $subtotal = 0;
    $embedded = [];
    $cidCounter = 1;
    foreach ($cartItems as $ci) {
        $lineTotalValue = (float)($ci['line_total'] ?? 0);
        $lineTotal = number_format($lineTotalValue, 2);
        $subtotal += $lineTotalValue;

        $img = $ci['image'] ?? '';
        $img = str_replace('\\', '/', $img);
        $imgSrc = '';
        if ($img) {
            $relativePath = ltrim($img, '/');
            $localPath = realpath(__DIR__ . '/../' . $relativePath);
            if ($localPath && is_readable($localPath)) {
                $cid = 'item_' . $cidCounter++;
                $mime = @mime_content_type($localPath) ?: 'application/octet-stream';
                $embedded[] = ['path' => $localPath, 'cid' => $cid, 'name' => basename($localPath), 'type' => $mime];
                $imgSrc = 'cid:' . $cid;
            }
        }
        if (!$imgSrc) {
            if ($img && stripos($img, 'http') !== 0) {
                $imgSrc = $assetBase . ltrim($img, '/');
            } elseif ($img) {
                $imgSrc = $img;
            } else {
                $imgSrc = 'https://via.placeholder.com/120x120.png?text=SoleSource';
            }
        }

        $metaParts = [];
        if (!empty($ci['style'])) {
            $metaParts[] = 'Style: ' . htmlspecialchars($ci['style']);
        }
        if (!empty($ci['size'])) {
            $metaParts[] = 'Size: ' . htmlspecialchars($ci['size']);
        }
        $metaParts[] = 'Qty: ' . (int)($ci['qty'] ?? 0);
        if (!empty($ci['color'])) {
            $metaParts[] = 'Color: ' . htmlspecialchars($ci['color']);
        }
        $meta = implode(' | ', $metaParts);

        $itemsHtml .= '<tr>
            <td style="padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;"><img src="' . htmlspecialchars($imgSrc) . '" alt="' . htmlspecialchars($ci['name'] ?? '') . '" width="80" style="display:block; border-radius:6px;" referrerpolicy="no-referrer"></td>
            <td style="padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;">'
            . '<div style="font-weight:700; font-size:14px; color:' . $brandBlack . ';">' . htmlspecialchars($ci['name'] ?? '') . '</div>'
            . '<div style="margin-top:4px; color:' . $muted . ';">' . $meta . '</div>'
            . '</td>
            <td style="padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:' . $brandBlack . '; white-space:nowrap;">₱' . $lineTotal . '</td>
        </tr>';
    }

    // Hero image: prefer first product image, fallback to placeholder
    $heroUrl = '';
    if (!empty($cartItems[0]['image'])) {
        $img = str_replace('\\', '/', $cartItems[0]['image']);
        $relativePath = ltrim($img, '/');
        $localPath = realpath(__DIR__ . '/../' . $relativePath);
        if ($localPath && is_readable($localPath)) {
            $cid = 'hero_item';
            $mime = @mime_content_type($localPath) ?: 'application/octet-stream';
            $embedded[] = ['path' => $localPath, 'cid' => $cid, 'name' => basename($localPath), 'type' => $mime];
            $heroUrl = 'cid:' . $cid;
        } else {
            $heroUrl = (stripos($img, 'http') === 0) ? $img : $assetBase . $relativePath;
        }
    }
    if (!$heroUrl) {
        $placeholderPath = realpath(__DIR__ . '/../assets/img/logo-big.png');
        if ($placeholderPath && is_readable($placeholderPath)) {
            $embedded[] = ['path' => $placeholderPath, 'cid' => 'hero_placeholder'];
            $heroUrl = 'cid:hero_placeholder';
        } else {
            $heroUrl = $assetBase . 'assets/img/logo-big.png';
        }
    }

    // Logo for CID embedding if present
    $logoCid = 'logo_receipt';
    $logoPath = __DIR__ . '/../assets/svg/logo-black.svg';
    $logoSrc = (is_readable($logoPath)) ? 'cid:' . $logoCid : $assetBase . 'assets/svg/logo-black.svg';

    $emailSubject = 'Your SoleSource Receipt #' . $orderNumber;
    $emailBody = '<!DOCTYPE html><html><body style="margin:0; padding:0; background:' . $panelBg . ';">
    <div style="max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:' . $brandBlack . '; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);">
        <div style="height:6px; background:' . $accent . ';"></div>
        <div style="padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;">
            <img src="' . $logoSrc . '" alt="SoleSource" height="28" style="display:block;">
            <div style="font-size:12px; color:' . $muted . '; text-align:right;">Order #' . htmlspecialchars($orderNumber) . '</div>
        </div>

        <div style="padding:24px 24px 8px 24px; text-align:center;">
            <div style="font-size:22px; font-weight:800; letter-spacing:0.4px; color:' . $brandBlack . ';">Thank you!</div>
            <div style="margin-top:8px; color:' . $muted . '; font-size:13px;">Your order was placed successfully. Track or view anytime.</div>
        </div>

        <div style="margin:0 24px; background:' . $brandDarkGray . '; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;">
            YOUR ORDER WAS PLACED SUCCESSFULLY.
            <div style="font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;">We also emailed your confirmation.</div>
        </div>

        <div style="padding:18px 24px 8px 24px; font-size:13px; color:' . $muted . '; line-height:1.6;">
            <div style="margin-bottom:4px;">Your Order: <strong style="color:' . $brandBlack . ';">' . htmlspecialchars($orderNumber) . '</strong></div>
            <div style="margin-bottom:10px;">Order Date: <strong style="color:' . $brandBlack . ';">' . htmlspecialchars($orderDate) . '</strong></div>
            <div style="margin-bottom:12px;">We have sent the order confirmation details to ' . htmlspecialchars($fullName ?: 'you') . '.</div>
        </div>

        <div style="display:flex; gap:12px; padding:0 24px 18px 24px;">
            <div style="flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;">
                <div style="font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:' . $brandBlack . ';">SHIPMENT</div>
                <div style="font-size:13px; color:' . $muted . '; line-height:1.6;">' . nl2br(htmlspecialchars($shippingAddress)) . '</div>
            </div>
            <div style="flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;">
                <div style="font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:' . $brandBlack . ';">PAYMENT</div>
                <div style="font-size:13px; color:' . $muted . ';">Method</div>
                <div style="font-weight:700; color:' . $brandBlack . '; margin-bottom:6px;">' . htmlspecialchars($paymentMethod ?: 'N/A') . '</div>
                <div style="font-size:13px; color:' . $muted . ';">Billing</div>
                <div style="font-weight:700; color:' . $brandBlack . ';">' . htmlspecialchars($fullName) . '</div>
            </div>
        </div>

        <div style="padding:0 24px 10px 24px;">
            <div style="font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:' . $brandBlack . ';">' . ($estimatedArrival ? 'ARRIVES ' . htmlspecialchars($estimatedArrival) : 'DELIVERY') . '</div>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; font-size:14px;">
                <tbody>' . $itemsHtml . '</tbody>
            </table>
        </div>

        <div style="padding:14px 24px 8px 24px;">
            <div style="font-size:14px; display:flex; justify-content:space-between; color:' . $brandBlack . ';">
                <span>Subtotal</span><span>₱' . number_format($subtotal, 2) . '</span>
            </div>
            <div style="font-size:14px; display:flex; justify-content:space-between; color:' . $brandBlack . '; margin-top:4px;">
                <span>Estimated Shipping</span><span>-</span>
            </div>
            <div style="font-size:14px; display:flex; justify-content:space-between; color:' . $brandBlack . '; margin-top:4px;">
                <span>Estimated Tax</span><span>-</span>
            </div>
            <div style="margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:' . $brandBlack . '; border-top:1px solid #efefef; padding-top:10px;">
                <span>Total</span><span>₱' . number_format($totalAmount, 2) . '</span>
            </div>
        </div>

        <div style="padding:16px 24px 24px 24px; text-align:right;">
            <a href="' . htmlspecialchars($orderViewUrl) . '" style="display:inline-block; background:' . $accent . '; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;">View / Print</a>
        </div>

        <div style="background:' . $brandBlack . '; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;">
            If you have questions, reply to this email.
        </div>
    </div>
</body></html>';

    $emailAlt = 'Thanks for your order ' . $fullName . "\n" .
        'Order: ' . $orderNumber . "\n" .
        'Total: ₱' . number_format($totalAmount, 2) . "\n" .
        'Payment: ' . $paymentMethod . "\n" .
        'Ship to: ' . $shippingAddress . "\n" .
        'View: ' . $orderViewUrl;

    if (is_readable($logoPath)) {
        $embedded[] = ['path' => $logoPath, 'cid' => $logoCid, 'name' => basename($logoPath), 'type' => (@mime_content_type($logoPath) ?: 'image/svg+xml')];
    }

    return [
        'subject' => $emailSubject,
        'html' => $emailBody,
        'alt' => $emailAlt,
        'embedded' => $embedded,
    ];
}

