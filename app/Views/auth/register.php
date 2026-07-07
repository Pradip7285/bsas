<?= view('auth/_hero', [
    'eyebrow'      => 'Create Account',
    'heroTitle'    => 'Join BSAS.',
    'heroSubtitle' => 'Register to place orders, track deliveries, and manage your shipping addresses from one account.',
]) ?>

<section class="sp-cart-section">
    <div class="sp-quote-panel" style="max-width:520px;margin:0 auto">
        <div class="sp-quote-card">
            <h3>Create Your BSAS Account</h3>
            <p>Takes less than a minute &mdash; you can check out as soon as you're registered.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="/register">
                <?= csrf_field() ?>
                <div class="sp-quote-form">
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Full Name *</label>
                        <input type="text" name="name" value="<?= esc(old('name')) ?>" required>
                    </div>
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Email *</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Phone</label>
                        <input type="tel" name="phone" value="<?= esc(old('phone')) ?>">
                    </div>
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Password *</label>
                        <input type="password" name="password" minlength="8" required>
                        <span style="font-size:11px;color:#9ca3af">Minimum 8 characters.</span>
                    </div>
                    <div class="sp-quote-form-full">
                        <button type="submit" class="btn" style="width:100%;justify-content:center">Create Account &rarr;</button>
                    </div>
                </div>
            </form>

            <div style="margin-top:16px;text-align:center;font-size:13px">
                Already have an account? <a href="/login">Sign in</a>
            </div>
        </div>

        <div class="sp-dark-card" style="margin-top:16px">
            <h4>Prefer email or phone?</h4>
            <p>You can also sign in instantly with Google or a one-time SMS code &mdash; no password to remember.</p>
            <div class="sp-dark-card-actions">
                <a href="/login" class="btn btn-dark">Sign In Options &rarr;</a>
            </div>
        </div>
    </div>
</section>
