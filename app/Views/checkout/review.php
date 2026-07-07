<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">Checkout</div>
            <h1>Review your order.</h1>
            <p>Confirm items, shipping address, and payment method before placing your order.</p>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:800px;margin:0 auto">
        <div class="sp-items-panel-head" style="border-bottom:none;padding-bottom:0">
            <?= view('checkout/_progress', ['currentStep' => 2]) ?>
        </div>

        <div style="padding:0 24px 20px">
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <h3>Shipping To</h3>
            <p><?= esc($shippingAddress['contact_name']) ?> &middot; <?= esc($shippingAddress['contact_phone']) ?><br>
                <?= esc($shippingAddress['address_line1']) ?><?= $shippingAddress['address_line2'] ? ', ' . esc($shippingAddress['address_line2']) : '' ?><br>
                <?= esc($shippingAddress['city']) ?>, <?= esc($shippingAddress['state']) ?> <?= esc($shippingAddress['postal_code']) ?></p>
            <p><a href="/checkout/address">Change address</a></p>

            <h3 style="margin-top:20px">Items</h3>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['product']['name']) ?></td>
                            <td><?= esc((string) $item['quantity']) ?></td>
                            <td><?= esc(number_format((float) $item['product']['price'], 2)) ?></td>
                            <td><?= esc(number_format((float) $item['product']['price'] * $item['quantity'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" style="text-align:right">Subtotal</td><td><?= esc(number_format($totals['subtotal'], 2)) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right">Tax</td><td><?= esc(number_format($totals['tax_total'], 2)) ?></td></tr>
                        <tr><td colspan="3" style="text-align:right"><strong>Grand Total</strong></td><td><strong><?= esc($totals['currency']) ?> <?= esc(number_format($totals['grand_total'], 2)) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>

            <h3 style="margin-top:20px">Payment Method</h3>
            <p style="font-size:13px;color:#6b7280">Payment is collected offline — no online payment is processed on this site.</p>
            <form method="post" action="/checkout/place">
                <?= csrf_field() ?>
                <div class="admin-card-grid" style="margin-bottom:16px">
                    <label class="admin-list-card" style="cursor:pointer">
                        <input type="radio" name="payment_method" value="bank_transfer" checked style="margin-right:8px">
                        &#127974; Bank Transfer
                    </label>
                    <label class="admin-list-card" style="cursor:pointer">
                        <input type="radio" name="payment_method" value="cod" style="margin-right:8px">
                        &#128176; Cash on Delivery
                    </label>
                    <label class="admin-list-card" style="cursor:pointer">
                        <input type="radio" name="payment_method" value="invoice" style="margin-right:8px">
                        &#128196; Invoice
                    </label>
                </div>
                <div class="form-group form-full">
                    <label>Order Note (optional)</label>
                    <textarea name="customer_note"></textarea>
                </div>
                <button type="submit" class="btn" style="width:100%;justify-content:center">Place Order</button>
            </form>
        </div>
    </div>
</section>
