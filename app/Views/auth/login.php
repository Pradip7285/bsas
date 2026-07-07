<?= view('auth/_hero', [
    'eyebrow'      => 'Sign In',
    'heroTitle'    => 'Welcome back.',
    'heroSubtitle' => 'Sign in to check out, view order history, and manage your shipping addresses.',
]) ?>

<section class="sp-cart-section">
    <div class="sp-quote-panel" style="max-width:520px;margin:0 auto">
        <div class="sp-quote-card">
            <h3>Sign In</h3>
            <p>Access your account to check out, view order history, and manage addresses.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('message')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('message')) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="/login">
                <?= csrf_field() ?>
                <div class="sp-quote-form">
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Email *</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="sp-quote-form-full" style="display:flex;flex-direction:column;gap:6px">
                        <label class="sp-quote-form-label">Password *</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="sp-quote-form-full" style="text-align:right">
                        <a href="/forgot-password" style="font-size:12px">Forgot password?</a>
                    </div>
                    <div class="sp-quote-form-full">
                        <button type="submit" class="btn" style="width:100%;justify-content:center">Sign In</button>
                    </div>
                </div>
            </form>

            <div style="margin:20px 0 16px;display:flex;align-items:center;gap:12px;color:#9ca3af;font-size:11px;text-transform:uppercase;letter-spacing:.5px">
                <span style="flex:1;height:1px;background:#e5e7eb"></span>
                Or continue with
                <span style="flex:1;height:1px;background:#e5e7eb"></span>
            </div>

            <a href="/auth/google" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:10px">&#128100; Continue with Google</a>

            <div id="otp-login" style="border:1.5px solid #e5e7eb;border-radius:10px;padding:14px;margin-top:6px">
                <p style="font-size:12px;font-weight:700;color:#374151;margin:0 0 8px">Sign in with a mobile code</p>
                <div style="display:flex;gap:8px">
                    <input type="tel" id="otp-phone" placeholder="Mobile number" style="flex:1">
                    <button type="button" id="otp-send" class="btn btn-outline">Send Code</button>
                </div>
                <div id="otp-verify-row" style="display:none;margin-top:8px;gap:8px">
                    <input type="text" id="otp-code" placeholder="6-digit code" maxlength="6" style="flex:1">
                    <button type="button" id="otp-verify" class="btn">Verify &amp; Sign In</button>
                </div>
                <p id="otp-status" style="font-size:12px;margin-top:8px"></p>
            </div>

            <div style="margin-top:16px;text-align:center;font-size:13px">
                New here? <a href="/register">Create an account</a>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var csrfInput = document.querySelector('input[name^="csrf_"]');
    function csrfName() { return csrfInput ? csrfInput.name : ''; }
    function csrfValue() {
        var el = document.querySelector('input[name="' + csrfName() + '"]');
        return el ? el.value : '';
    }

    var sendBtn = document.getElementById('otp-send');
    var verifyBtn = document.getElementById('otp-verify');
    var verifyRow = document.getElementById('otp-verify-row');
    var status = document.getElementById('otp-status');

    sendBtn.addEventListener('click', function () {
        var phone = document.getElementById('otp-phone').value.trim();
        if (!phone) { status.textContent = 'Enter a mobile number.'; return; }
        var form = new FormData();
        form.append('phone', phone);
        form.append(csrfName(), csrfValue());
        status.textContent = 'Sending code…';
        fetch('/otp/request', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    verifyRow.style.display = 'flex';
                    status.textContent = 'Code sent. Enter it below.';
                } else {
                    status.textContent = (data.errors && data.errors[0]) || 'Could not send code.';
                }
            });
    });

    verifyBtn.addEventListener('click', function () {
        var phone = document.getElementById('otp-phone').value.trim();
        var code = document.getElementById('otp-code').value.trim();
        var form = new FormData();
        form.append('phone', phone);
        form.append('code', code);
        form.append(csrfName(), csrfValue());
        status.textContent = 'Verifying…';
        fetch('/otp/verify', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = data.redirect || '/account';
                } else {
                    status.textContent = (data.errors && data.errors[0]) || 'Invalid code.';
                }
            });
    });
})();
</script>
