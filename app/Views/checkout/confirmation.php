<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">Checkout</div>
            <h1>Order Confirmed.</h1>
            <p>Thank you for your order &mdash; here's a summary and what happens next.</p>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:700px;margin:0 auto;text-align:center">
        <div style="padding:12px 24px 0">
            <?= view('checkout/_progress', ['currentStep' => 3]) ?>
        </div>

        <div style="padding:0 24px 40px">
            <div style="width:64px;height:64px;border-radius:50%;background:#d1fae5;color:#065f46;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px">&#10003;</div>
            <h2>Order Placed!</h2>
            <p>Your order <strong style="color:#f59b23"><?= esc($order['order_number']) ?></strong> has been received.
                A confirmation email has been sent to your registered address.</p>

            <div class="admin-table-shell" style="text-align:left;margin-top:24px">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Line Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['product_name']) ?></td>
                            <td><?= esc((string) $item['quantity']) ?></td>
                            <td><?= esc(number_format((float) $item['line_total'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="2" style="text-align:right"><strong>Grand Total</strong></td><td><strong><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="sp-dark-card" style="margin-top:24px;text-align:left">
                <h4>What happens next?</h4>
                <p>Our team will confirm your order and get it ready for dispatch. You'll be able to track its status &mdash; and any courier tracking details &mdash; from your order history at any time.</p>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px;justify-content:center">
                <a href="/account/orders/<?= esc($order['order_number']) ?>" class="btn">View Order</a>
                <a href="/e-shop" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>
