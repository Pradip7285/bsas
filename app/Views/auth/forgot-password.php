<?= view('auth/_hero', [
    'eyebrow'      => 'Account Recovery',
    'heroTitle'    => 'Forgot your password?',
    'heroSubtitle' => "No problem — enter your account email and we'll send you a reset link.",
]) ?>

<section class="sp-cart-section">
    <div class="sp-quote-panel" style="max-width:520px;margin:0 auto">
        <div class="sp-quote-card">
            <h3>Reset Your Password</h3>
            <p>Enter your account email and we'll send you a link to reset your password.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="/forgot-password">
                <?= csrf_field() ?>
                <div class="sp-quote-form">
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Email *</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="sp-quote-form-full">
                        <button type="submit" class="btn" style="width:100%;justify-content:center">Send Reset Link</button>
                    </div>
                </div>
            </form>

            <div style="margin-top:16px;text-align:center;font-size:13px">
                <a href="/login">Back to sign in</a>
            </div>
        </div>
    </div>
</section>
