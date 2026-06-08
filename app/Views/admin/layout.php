<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle ?? 'Admin Console') ?> | BSAS</title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <link rel="stylesheet" href="/assets/css/admin-premium.css">
</head>
<body class="admin-body">
<?php $toolbar = trim($this->renderSection('toolbar')); ?>
<?php $beforeContent = trim($this->renderSection('beforeContent')); ?>
<main class="admin-shell">

    <!-- ── Sidebar ── -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-brand-logo">
                <div class="admin-brand-logo-icon">B</div>
                <span class="admin-brand-logo-text">BSAS</span>
            </div>
            <span class="section-label">Admin Console</span>
            <strong>Operations Backend</strong>
            <p>Catalogue, leads, and bulk listing in one workspace.</p>
        </div>

        <nav class="admin-side-nav" aria-label="Admin navigation">

            <a href="/admin" class="<?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Dashboard</span>
                    <span class="adm-nav-desc">Overview &amp; analytics</span>
                </span>
            </a>

            <a href="/admin/products" class="<?= in_array(($activeNav ?? ''), ['products', 'product-editor'], true) ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="4" rx="1"/>
                        <rect x="2" y="10" width="20" height="4" rx="1"/>
                        <rect x="2" y="17" width="20" height="4" rx="1"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Products</span>
                    <span class="adm-nav-desc">List, edit &amp; delete</span>
                </span>
            </a>

            <a href="/admin/products/bulk" class="<?= ($activeNav ?? '') === 'bulk' ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Bulk Listing</span>
                    <span class="adm-nav-desc">Import &amp; export CSV</span>
                </span>
            </a>

            <a href="/admin/leads" class="<?= ($activeNav ?? '') === 'leads' ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Lead Inbox</span>
                    <span class="adm-nav-desc">RFQs &amp; brochure leads</span>
                </span>
            </a>

            <a href="/admin/categories" class="<?= ($activeNav ?? '') === 'categories' ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h10M4 18h14"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Categories</span>
                    <span class="adm-nav-desc">Manage product groups</span>
                </span>
            </a>

            <a href="/admin/gallery" class="<?= in_array(($activeNav ?? ''), ['gallery-albums', 'gallery-album-editor', 'gallery-items'], true) ? 'is-active' : '' ?>">
                <span class="adm-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <circle cx="9" cy="10" r="1.5"/>
                        <path d="M21 16l-5-5-4 4-2-2-5 5"/>
                    </svg>
                </span>
                <span class="adm-nav-text">
                    <span class="adm-nav-label">Gallery</span>
                    <span class="adm-nav-desc">Albums and media</span>
                </span>
            </a>

        </nav>

        <section class="admin-side-card">
            <span class="admin-side-card-label">Signed In</span>
            <strong><?= esc((string) (session()->get('admin_username') ?? 'admin')) ?></strong>
            <p>Backend access for catalogue and lead operations.</p>
            <div class="admin-side-card-actions">
                <a href="/e-shop" class="btn btn-outline">Storefront</a>
                <a href="/admin/logout" class="btn btn-dark">Sign Out</a>
            </div>
        </section>
    </aside>

    <!-- ── Main ── -->
    <div class="admin-main">
        <section class="admin-masthead">
            <div style="display:flex;align-items:center;flex:1;gap:0;min-width:0">
                <button class="adm-sidebar-toggle" id="adm-sidebar-toggle" aria-label="Toggle sidebar">
                    &#9776;
                </button>
                <div class="admin-masthead-copy" style="min-width:0">
                    <span class="section-label"><?= esc($mastheadLabel ?? 'Admin Console') ?></span>
                    <h1><?= esc($mastheadTitle ?? 'Operations workspace') ?></h1>
                    <p><?= esc($mastheadText ?? 'Manage storefront data and buyer activity.') ?></p>
                </div>
            </div>
            <?php if ($toolbar !== ''): ?>
                <div class="admin-toolbar"><?= $toolbar ?></div>
            <?php endif; ?>
        </section>

        <div class="admin-content-wrap">
            <?php if ($beforeContent !== ''): ?>
                <?= $beforeContent ?>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>

</main>

<!-- Mobile sidebar overlay -->
<div class="adm-overlay" id="adm-overlay"></div>

<script>
(function () {
    var toggle  = document.getElementById('adm-sidebar-toggle');
    var sidebar = document.querySelector('.admin-sidebar');
    var overlay = document.getElementById('adm-overlay');

    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    // Close on nav link click (useful on mobile)
    sidebar.querySelectorAll('.admin-side-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 900) closeSidebar();
        });
    });
})();
</script>
</body>
</html>
