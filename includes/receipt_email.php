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

    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $orderViewUrl = $baseUrl . '/view_order.php?id=' . urlencode($orderId);
    $accent = '#E9713F';
    $brandBlack = '#121212';
    $muted = '#6f6f6f';

    // Build item rows (table layout is safest across email clients)
    $itemsHtml = '';
    $subtotal = 0;
    foreach ($cartItems as $ci) {
        $lineTotalValue = (float)($ci['line_total'] ?? 0);
        $lineTotal = number_format($lineTotalValue, 2);
        $subtotal += $lineTotalValue;

        $img = $ci['image'] ?? '';
        if ($img && stripos($img, 'http') !== 0) {
            $img = $baseUrl . '/' . ltrim($img, '/');
        }
        if (!$img) {
            $img = 'https://via.placeholder.com/120x120.png?text=SoleSource';
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
            <td style="padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;"><img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($ci['name'] ?? '') . '" width="80" style="display:block; border-radius:6px;"></td>
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
        $img = $cartItems[0]['image'];
        $heroUrl = (stripos($img, 'http') === 0) ? $img : $baseUrl . '/' . ltrim($img, '/');
    }
    if (!$heroUrl) {
        $heroUrl = 'https://via.placeholder.com/700x260.png?text=SoleSource+Hero';
    }

    // Logo for CID embedding if present
    $logoCid = 'logo_receipt';
    $logoPath = __DIR__ . '/../assets/svg/logo-black.svg';
    $logoSrc = (is_readable($logoPath)) ? 'cid:' . $logoCid : 'https://via.placeholder.com/120x32.png?text=SoleSource';

    $emailSubject = 'Your SoleSource Receipt #' . $orderNumber;
    $emailBody = '<!DOCTYPE html><html><body style="margin:0; padding:0; background:#f5f5f5;">
    <div style="max-width:720px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:' . $brandBlack . '; border:1px solid #e6e6e6; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
        <div style="padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;">
            <img src="' . $logoSrc . '" alt="SoleSource" height="28" style="display:block;">
            <div style="font-size:12px; color:' . $muted . '; text-align:right;">Order #' . htmlspecialchars($orderNumber) . '</div>
        </div>

        <div style="padding:28px 24px 12px 24px; text-align:center;">
            <div style="font-size:20px; font-weight:800; letter-spacing:0.4px;">THANK YOU!</div>
        </div>

        <div style="margin:0 24px; background:' . $brandBlack . '; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px;">YOUR ORDER WAS PLACED SUCCESSFULLY.<div style="font-weight:400; margin-top:6px; color:#dcdcdc; font-size:12px;">Check your email for your order confirmation.</div></div>

        <div style="padding:18px 24px 8px 24px; font-size:13px; color:' . $muted . '; line-height:1.6;">
            <div style="margin-bottom:4px;">Your Order: <strong style="color:' . $brandBlack . ';">' . htmlspecialchars($orderNumber) . '</strong></div>
            <div style="margin-bottom:10px;">Order Date: <strong style="color:' . $brandBlack . ';">' . htmlspecialchars($orderDate) . '</strong></div>
            <div style="margin-bottom:12px;">We have sent the order confirmation details to ' . htmlspecialchars($fullName ?: 'you') . '.</div>
        </div>

        <div style="padding:6px 24px 18px 24px; border-bottom:1px solid #efefef;">
            <div style="font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px;">SHIPMENT</div>
            <div style="font-size:13px; color:' . $muted . '; line-height:1.6;">' . nl2br(htmlspecialchars($shippingAddress)) . '</div>
        </div>

        <div style="padding:16px 24px 10px 24px; border-bottom:1px solid #efefef;">
            <div style="font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:10px;">PAYMENT</div>
            <div style="font-size:13px; color:' . $muted . ';">Payment Method</div>
            <div style="font-weight:700; color:' . $brandBlack . '; margin-bottom:6px;">' . htmlspecialchars($paymentMethod ?: 'N/A') . '</div>
            <div style="font-size:13px; color:' . $muted . ';">Billing Details</div>
            <div style="font-weight:700; color:' . $brandBlack . ';">' . htmlspecialchars($fullName) . '</div>
        </div>

        <div style="padding:16px 24px 10px 24px; border-bottom:1px solid #efefef;">
            <div style="font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px;">' . ($estimatedArrival ? 'ARRIVES ' . htmlspecialchars($estimatedArrival) : 'DELIVERY') . '</div>
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

        <div style="background:#0f0f0f; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;">
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

    $embedded = [];
    if (is_readable($logoPath)) {
        $embedded[] = ['path' => $logoPath, 'cid' => $logoCid];
    }

    return [
        'subject' => $emailSubject,
        'html' => $emailBody,
        'alt' => $emailAlt,
        'embedded' => $embedded,
    ];
}

