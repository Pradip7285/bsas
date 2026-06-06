<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | BSAS</title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <link rel="stylesheet" href="/assets/css/admin-premium.css">
</head>
<body class="admin-body">

<main class="admin-auth-page">

    <!-- ── Left hero panel ── -->
    <section class="admin-auth-hero">
        <div class="admin-auth-hero-brand">
            <div class="admin-auth-hero-brand-icon">B</div>
            <span class="admin-auth-hero-brand-text">BSAS</span>
        </div>
        <span class="section-label">Protected Access</span>
        <h1>BSAS admin console.</h1>
        <p>Sign in to manage catalogue listings, review RFQs, and process spreadsheet imports through the internal backend.</p>
        <div class="admin-auth-points">
            <span>
                <svg style="display:inline;width:12px;height:12px;vertical-align:-1px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Catalogue operations
            </span>
            <span>
                <svg style="display:inline;width:12px;height:12px;vertical-align:-1px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Lead review
            </span>
            <span>
                <svg style="display:inline;width:12px;height:12px;vertical-align:-1px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk imports
            </span>
        </div>
    </section>

    <!-- ── Right form panel ── -->
    <section class="admin-auth-form">
        <div class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Admin Sign In</h2>
                    <p>Enter your credentials to open the backend.</p>
                </div>
            </div>

            <?php if (! empty($message)): ?>
                <div class="success-banner"><?= esc($message) ?></div>
            <?php endif; ?>
            <?php if (! empty($errors)): ?>
                <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" action="/admin/login" class="form-grid admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group form-full">
                    <label for="adm-user">Username</label>
                    <input id="adm-user" type="text" name="username"
                           value="<?= esc(old('username')) ?>"
                           autocomplete="username" required>
                </div>
                <div class="form-group form-full">
                    <label for="adm-pass">Password</label>
                    <input id="adm-pass" type="password" name="password"
                           autocomplete="current-password" required>
                </div>
                <div class="form-full" style="margin-top:4px">
                    <button type="submit" class="btn" style="width:100%;justify-content:center">
                        Sign In &rarr;
                    </button>
                </div>
            </form>
        </div>
    </section>

</main>

</body>
</html>
