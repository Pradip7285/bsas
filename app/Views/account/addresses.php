<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">My Account</div>
            <h1>Shipping Addresses</h1>
            <p>Save addresses once and pick them at checkout in a click.</p>
        </div>
        <div class="sp-cart-hero-stat">
            <strong><?= esc((string) count($addresses)) ?></strong>
            <span>Saved Addresses</span>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:960px;margin:0 auto">
        <?= view('account/_nav', ['activeNav' => 'addresses']) ?>

        <div style="padding:20px 24px">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <h3>My Addresses</h3>
            <?php if ($addresses === []): ?>
                <p>You have no saved addresses yet.</p>
            <?php else: ?>
                <div class="admin-card-grid">
                    <?php foreach ($addresses as $address): ?>
                        <article class="admin-list-card">
                            <strong><?= esc($address['label'] ?: 'Address') ?></strong>
                            <p><?= esc($address['contact_name']) ?> &middot; <?= esc($address['contact_phone']) ?></p>
                            <p><?= esc($address['address_line1']) ?><?= $address['address_line2'] ? ', ' . esc($address['address_line2']) : '' ?><br>
                                <?= esc($address['city']) ?>, <?= esc($address['state']) ?> <?= esc($address['postal_code']) ?></p>
                            <form method="post" action="/account/addresses/<?= esc((string) $address['id']) ?>/delete" onsubmit="return confirm('Delete this address?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:12px;color:#dc2626;border-color:#fca5a5">Delete</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3 style="margin-top:28px">Add New Address</h3>
            <form method="post" action="/account/addresses" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="label" placeholder="e.g. Head Office" value="<?= esc(old('label')) ?>">
                </div>
                <div class="form-group">
                    <label>Contact Name *</label>
                    <input type="text" name="contact_name" value="<?= esc(old('contact_name')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Contact Phone *</label>
                    <input type="tel" name="contact_phone" value="<?= esc(old('contact_phone')) ?>" required>
                </div>
                <div class="form-group form-full">
                    <label>Address Line 1 *</label>
                    <input type="text" name="address_line1" value="<?= esc(old('address_line1')) ?>" required>
                </div>
                <div class="form-group form-full">
                    <label>Address Line 2</label>
                    <input type="text" name="address_line2" value="<?= esc(old('address_line2')) ?>">
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" value="<?= esc(old('city')) ?>" required>
                </div>
                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" value="<?= esc(old('state')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Postal Code *</label>
                    <input type="text" name="postal_code" value="<?= esc(old('postal_code')) ?>" required>
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</section>
