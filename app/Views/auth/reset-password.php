<?= view('auth/_hero', [
    'eyebrow'      => 'Account Recovery',
    'heroTitle'    => 'Choose a new password.',
    'heroSubtitle' => 'Pick something secure — at least 8 characters.',
]) ?>

<section class="sp-cart-section">
    <div class="sp-quote-panel" style="max-width:520px;margin:0 auto">
        <div class="sp-quote-card">
            <h3>Reset Password</h3>

            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="/reset-password">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token) ?>">
                <div class="sp-quote-form">
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">New Password *</label>
                        <input type="password" name="password" minlength="8" required>
                    </div>
                    <div class="sp-quote-form-full">
                        <button type="submit" class="btn" style="width:100%;justify-content:center">Reset Password</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
