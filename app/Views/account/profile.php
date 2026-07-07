<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">My Account</div>
            <h1>Profile &amp; Security</h1>
            <p>Update your contact details and password.</p>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:700px;margin:0 auto">
        <?= view('account/_nav', ['activeNav' => 'profile']) ?>

        <div style="padding:20px 24px">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <h3>Profile</h3>
            <form method="post" action="/account/profile" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group form-full">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= esc(old('name', $customer['name'] ?? '')) ?>" required>
                </div>
                <div class="form-group form-full">
                    <label>Email</label>
                    <input type="email" value="<?= esc($customer['email'] ?? '') ?>" disabled>
                </div>
                <div class="form-group form-full">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= esc(old('phone', $customer['phone'] ?? '')) ?>">
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Save Profile</button>
                </div>
            </form>

            <h3 style="margin-top:28px">Change Password</h3>
            <form method="post" action="/account/password" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Current Password *</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password *</label>
                    <input type="password" name="new_password" minlength="8" required>
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</section>
