<!-- ── Cart Hero ── -->
<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">Quote Basket</div>
            <h1>Review &amp; Request Quote</h1>
            <p>Confirm quantities, then submit the entire shortlist as a single coordinated RFQ — one form, one BSAS sales response.</p>
            <div class="sp-hero-pills">
                <span class="sp-hero-pill">One enquiry</span>
                <span class="sp-hero-pill">Multiple products</span>
                <span class="sp-hero-pill">Sales follow-up</span>
            </div>
        </div>
        <div class="sp-cart-hero-stat">
            <strong><?= esc((string) $cartCount) ?></strong>
            <span>Basket Items</span>
            <a href="/e-shop" class="btn">Continue Shopping</a>
        </div>
    </div>
</div>

<!-- ── Cart Layout ── -->
<section class="sp-cart-section">
    <div class="sp-cart-layout">

        <!-- Left: Cart Items -->
        <div class="sp-items-panel">
            <div class="sp-items-panel-head">
                <h2>Selected Products</h2>
                <span class="sp-item-count"><?= esc((string) $cartCount) ?> item<?= $cartCount !== 1 ? 's' : '' ?></span>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div style="padding:16px 24px 0"><div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div style="padding:16px 24px 0"><div class="sp-flash-err"><?= esc(session()->getFlashdata('error')) ?></div></div>
            <?php endif; ?>

            <?php if ($cartItems === []): ?>
                <div class="sp-cart-empty">
                    <div class="sp-cart-empty-icon">&#128722;</div>
                    <h3>Your quote basket is empty.</h3>
                    <p>Add products from the catalogue to prepare a quote request.</p>
                    <a href="/e-shop" class="btn">Browse Catalogue</a>
                </div>
            <?php else: ?>
                <form method="post" action="/cart/update" id="cart-update-form">
                    <?= csrf_field() ?>
                    <div class="sp-cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="sp-cart-item">
                                <div class="sp-cart-item-img"
                                     style="background-image:url('<?= esc($item['product']['image_url'] ?? '/assets/images/sparePart.webp') ?>')">
                                </div>
                                <div class="sp-cart-item-info">
                                    <span class="sp-cart-item-name"><?= esc($item['product']['name']) ?></span>
                                    <span class="sp-cart-item-meta">
                                        <?= esc($item['product']['category']) ?>
                                        <?php if (! empty($item['product']['sku'])): ?>
                                            &nbsp;&middot;&nbsp; SKU <?= esc($item['product']['sku']) ?>
                                        <?php endif; ?>
                                    </span>
                                    <?php if ((float) ($item['product']['price'] ?? 0) > 0): ?>
                                        <span class="sp-cart-item-meta" style="display:block;margin-top:4px;font-weight:700;color:#111">
                                            &#8377; <?= esc(number_format((float) $item['product']['price'], 2)) ?> &times; <?= esc((string) $item['quantity']) ?>
                                            = &#8377; <?= esc(number_format((float) $item['product']['price'] * $item['quantity'], 2)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="sp-cart-item-actions">
                                    <div class="sp-cart-qty">
                                        <button type="button" class="sp-cart-qty-btn"
                                                onclick="spCartAdj(this,-1)">&#8722;</button>
                                        <input type="number" min="1"
                                               name="quantities[<?= esc((string) $item['product']['id']) ?>]"
                                               value="<?= esc((string) $item['quantity']) ?>"
                                               class="sp-cart-qty-input">
                                        <button type="button" class="sp-cart-qty-btn"
                                                onclick="spCartAdj(this,1)">&#43;</button>
                                    </div>
                                </div>
                                <!-- type="button" — does NOT submit the parent update form -->
                                <button type="button"
                                        class="sp-cart-remove"
                                        data-pid="<?= esc((string) $item['product']['id']) ?>"
                                        title="Remove item">&#10005;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sp-items-panel-footer">
                        <button type="submit" class="btn btn-outline">Update Quantities</button>
                    </div>
                </form>

                <!-- Shared remove form — OUTSIDE the update form, submitted by JS below -->
                <form method="post" action="/cart/remove" id="sp-remove-form" style="display:none">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" id="sp-remove-pid" value="">
                </form>
            <?php endif; ?>
        </div><!-- /sp-items-panel -->

        <!-- Right: Quote Form (sticky) -->
        <div class="sp-quote-panel">
            <?php
            $orderableItems = array_filter($cartItems, static fn(array $item): bool => (float) ($item['product']['price'] ?? 0) > 0);
            $orderSubtotal  = 0.0;
            $orderTax       = 0.0;
            foreach ($orderableItems as $orderItem) {
                $lineTotal      = (float) $orderItem['product']['price'] * $orderItem['quantity'];
                $orderSubtotal += $lineTotal;
                $orderTax      += $lineTotal * ((float) ($orderItem['product']['tax_rate'] ?? 0) / 100);
            }
            $orderGrandTotal = $orderSubtotal + $orderTax;
            ?>
            <?php if ($orderableItems !== []): ?>
                <div class="sp-quote-card" style="margin-bottom:16px">
                    <h3>Ready to buy now?</h3>
                    <p><?= count($orderableItems) ?> item<?= count($orderableItems) !== 1 ? 's' : '' ?> in your basket have a fixed price and can be purchased directly.</p>
                    <dl class="sp-spec-table" style="margin:12px 0">
                        <div class="sp-spec-row"><dt>Subtotal</dt><dd>&#8377; <?= esc(number_format($orderSubtotal, 2)) ?></dd></div>
                        <div class="sp-spec-row"><dt>Tax</dt><dd>&#8377; <?= esc(number_format($orderTax, 2)) ?></dd></div>
                        <div class="sp-spec-row"><dt><strong>Total</strong></dt><dd><strong>&#8377; <?= esc(number_format($orderGrandTotal, 2)) ?></strong></dd></div>
                    </dl>
                    <a href="/checkout/address" class="btn" style="width:100%;justify-content:center">Proceed to Checkout</a>
                </div>
            <?php endif; ?>

            <div class="sp-quote-card">
                <h3>Request Cart Quote</h3>
                <p>Submit once &mdash; the BSAS team will respond with pricing, availability, and dispatch details for all items.</p>

                <?php if (! empty($errors)): ?>
                    <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
                <?php endif; ?>

                <form method="post" action="/cart/request-quote">
                    <?= csrf_field() ?>
                    <div class="sp-quote-form">
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Name *</label>
                            <input type="text" name="name" value="<?= esc(old('name')) ?>" required>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Company</label>
                            <input type="text" name="company" value="<?= esc(old('company')) ?>">
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Email</label>
                            <input type="email" name="email" value="<?= esc(old('email')) ?>">
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Phone *</label>
                            <input type="tel" name="phone" value="<?= esc(old('phone')) ?>" required>
                        </div>
                        <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Designation</label>
                            <input type="text" name="designation" value="<?= esc(old('designation')) ?>">
                        </div>
                        <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                            <label class="sp-quote-form-label">Notes &amp; Requirements</label>
                            <textarea name="message"><?= esc(old('message')) ?></textarea>
                        </div>
                        <div class="sp-quote-form-full">
                            <button type="submit" class="btn" style="width:100%;justify-content:center"
                                    <?= $cartItems === [] ? 'disabled' : '' ?>>
                                &#128231; Submit Cart Quote
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="sp-dark-card">
                <h4>Need validation first?</h4>
                <p>If you need interchange confirmation, application matching, or lead-time checks before submitting, route your requirement through support first.</p>
                <div class="sp-dark-card-actions">
                    <a href="/support" class="btn btn-dark">Open Support</a>
                    <a href="/e-shop" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.35)">Browse More</a>
                </div>
            </div>
        </div><!-- /sp-quote-panel -->

    </div><!-- /sp-cart-layout -->
</section>

<script>
// Quantity stepper
function spCartAdj(btn, delta) {
    var row   = btn.closest('.sp-cart-qty');
    var input = row ? row.querySelector('.sp-cart-qty-input') : null;
    if (!input) return;
    var v = parseInt(input.value, 10) || 1;
    v = Math.max(1, v + delta);
    input.value = v;
}

// Remove item — fills the shared hidden form and submits it
document.querySelectorAll('.sp-cart-remove[data-pid]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var pid  = this.dataset.pid;
        var form = document.getElementById('sp-remove-form');
        document.getElementById('sp-remove-pid').value = pid;
        form.submit();
    });
});
</script>
