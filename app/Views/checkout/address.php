<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">Checkout</div>
            <h1>Where should we ship this?</h1>
            <p>Choose a saved address or add a new one to continue.</p>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:800px;margin:0 auto">
        <div class="sp-items-panel-head" style="border-bottom:none;padding-bottom:0">
            <?= view('checkout/_progress', ['currentStep' => 1]) ?>
        </div>

        <div style="padding:0 24px 20px">
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <?php if ($addresses !== []): ?>
                <h3>Choose a saved address</h3>
                <form method="post" action="/checkout/address">
                    <?= csrf_field() ?>
                    <div class="admin-card-grid">
                        <?php foreach ($addresses as $address): ?>
                            <label class="admin-list-card" style="cursor:pointer;display:block">
                                <input type="radio" name="address_id" value="<?= esc((string) $address['id']) ?>" style="margin-right:8px">
                                <strong><?= esc($address['label'] ?: 'Address') ?></strong>
                                <p><?= esc($address['contact_name']) ?> &middot; <?= esc($address['contact_phone']) ?></p>
                                <p><?= esc($address['address_line1']) ?><?= $address['address_line2'] ? ', ' . esc($address['address_line2']) : '' ?><br>
                                    <?= esc($address['city']) ?>, <?= esc($address['state']) ?> <?= esc($address['postal_code']) ?></p>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn" style="margin-top:16px">Use Selected Address</button>
                </form>
                <hr style="margin:28px 0;border-color:#e5e7eb">
            <?php endif; ?>

            <h3>Add a new address</h3>
            <form method="post" action="/checkout/address" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="label" placeholder="e.g. Head Office">
                </div>
                <div class="form-group">
                    <label>Contact Name *</label>
                    <input type="text" name="contact_name" required>
                </div>
                <div class="form-group">
                    <label>Contact Phone *</label>
                    <input type="tel" name="contact_phone" required>
                </div>
                <div class="form-group form-full">
                    <label>Address Line 1 *</label>
                    <input type="text" name="address_line1" required>
                </div>
                <div class="form-group form-full">
                    <label>Address Line 2</label>
                    <input type="text" name="address_line2">
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" required>
                </div>
                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" required>
                </div>
                <div class="form-group">
                    <label>Postal Code *</label>
                    <input type="text" name="postal_code" required>
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Save &amp; Continue</button>
                </div>
            </form>
        </div>
    </div>
</section>
