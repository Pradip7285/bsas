<?php
$steps      = ['pending' => 'Placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
$stepKeys   = array_keys($steps);
$currentIdx = array_search($order['status'], $stepKeys, true);
$isCancelled = $order['status'] === 'cancelled';
?>
<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">My Account</div>
            <h1>Order <?= esc($order['order_number']) ?></h1>
            <p>Placed <?= esc((string) $order['created_at']) ?></p>
        </div>
        <div class="sp-cart-hero-stat">
            <strong><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?></strong>
            <span>Order Total</span>
            <a href="/account/orders" class="btn">All Orders</a>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:960px;margin:0 auto">
        <?= view('account/_nav', ['activeNav' => 'orders']) ?>

        <div style="padding:20px 24px">
            <?php if ($isCancelled): ?>
                <div class="sp-flash-err">This order was cancelled.</div>
            <?php else: ?>
                <!-- ── Order status tracker ── -->
                <div style="display:flex;align-items:center;margin:8px 0 24px">
                    <?php foreach ($steps as $key => $label): ?>
                        <?php $idx = array_search($key, $stepKeys, true); $done = $currentIdx !== false && $idx <= $currentIdx; ?>
                        <div style="flex:1;text-align:center;position:relative">
                            <div style="width:28px;height:28px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;
                                background:<?= $done ? '#f59b23' : '#e5e7eb' ?>;color:<?= $done ? '#fff' : '#9ca3af' ?>">
                                <?= $done ? '&#10003;' : esc((string) ($idx + 1)) ?>
                            </div>
                            <span style="font-size:11px;font-weight:700;color:<?= $done ? '#111' : '#9ca3af' ?>"><?= esc($label) ?></span>
                            <?php if ($idx < count($steps) - 1): ?>
                                <div style="position:absolute;top:14px;left:50%;width:100%;height:2px;background:<?= $idx < $currentIdx ? '#f59b23' : '#e5e7eb' ?>;z-index:-1"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($order['tracking_number'])): ?>
                <div class="sp-flash-ok">
                    Tracking: <?= esc($order['courier_name'] ?: 'Courier') ?> &mdash; <?= esc($order['tracking_number']) ?>
                    <?php if (! empty($order['tracking_url'])): ?>
                        &middot; <a href="<?= esc($order['tracking_url']) ?>" target="_blank" rel="noopener noreferrer">Track Shipment</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="admin-detail-grid" style="margin-top:16px">
                <div>
                    <span class="admin-label">Ship To</span>
                    <p><?= esc($order['shipping_name']) ?> &middot; <?= esc($order['shipping_phone']) ?><br>
                        <?= esc($order['shipping_address_line1']) ?><?= $order['shipping_address_line2'] ? ', ' . esc($order['shipping_address_line2']) : '' ?><br>
                        <?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_state']) ?> <?= esc($order['shipping_postal_code']) ?>
                    </p>
                </div>
                <div>
                    <span class="admin-label">Payment Method</span>
                    <p><?= esc(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?></p>
                </div>
            </div>

            <div class="admin-table-shell" style="margin-top:16px">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['product_name']) ?></td>
                            <td><?= esc((string) $item['quantity']) ?></td>
                            <td><?= esc(number_format((float) $item['unit_price'], 2)) ?></td>
                            <td><?= esc(number_format((float) $item['line_total'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" style="text-align:right">Subtotal</td><td><?= esc(number_format((float) $order['subtotal'], 2)) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right">Tax</td><td><?= esc(number_format((float) $order['tax_total'], 2)) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right"><strong>Grand Total</strong></td><td><strong><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>
