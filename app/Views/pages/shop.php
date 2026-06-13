<?php if (session()->getFlashdata('success')): ?>
    <div style="padding:0 5vw"><div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div style="padding:0 5vw"><div class="sp-flash-err"><?= esc(session()->getFlashdata('error')) ?></div></div>
<?php endif; ?>

<!-- ── Hero Banner ── -->
<div class="sp-hero">
    <div class="sp-hero-inner">
        <div class="sp-hero-text">
            <div class="sp-hero-eyebrow">BSAS E-Shop &mdash; Catalogue</div>
            <h1>Industrial parts <span class="accent">catalogue.</span></h1>
            <p class="sp-hero-sub">Search by SKU, compare categories, and shortlist items for one consolidated RFQ &mdash; no repeat forms, no scattered emails.</p>
            <div class="sp-hero-actions">
                <a href="/cart" class="btn sp-cart-link">
                    Quote Basket
                    <span class="sp-cart-badge <?= $cartCount > 0 ? '' : 'sp-cart-badge--hidden' ?>"><?= esc((string) $cartCount) ?></span>
                </a>
                <a href="/support" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.35)">Get Support</a>
            </div>
        </div>
        <div class="sp-hero-stats">
            <div class="sp-hero-stat">
                <strong><?= esc((string) $totalProducts) ?></strong>
                <span>Products</span>
            </div>
            <div class="sp-hero-stat">
                <strong><?= esc((string) count($categories)) ?></strong>
                <span>Categories</span>
            </div>
            <div class="sp-hero-stat">
                <strong class="js-cart-count"><?= esc((string) $cartCount) ?></strong>
                <span>In Basket</span>
            </div>
        </div>
    </div>
</div>

<!-- ── Filter Bar (sticky) ── -->
<div class="sp-filter-bar">
    <form id="sp-filter-form" method="get" action="/e-shop" class="sp-filter-inner">
        <div class="sp-search-wrap">
            <span class="sp-search-icon">&#128269;</span>
            <input id="sp-search-input" type="search" name="q"
                   value="<?= esc($searchQuery) ?>"
                   placeholder="Search by SKU, product name, or description&hellip;"
                   autocomplete="off">
        </div>
        <div class="sp-filter-selects">
            <div class="sp-select-wrap">
                <select name="category" id="sp-cat-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat) ?>" <?= $activeCategory === $cat ? 'selected' : '' ?>>
                            <?= esc($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sp-select-wrap">
                <select name="sort" id="sp-sort-select">
                    <option value="name_asc"       <?= $activeSort === 'name_asc'       ? 'selected' : '' ?>>Name A &rarr; Z</option>
                    <option value="name_desc"      <?= $activeSort === 'name_desc'      ? 'selected' : '' ?>>Name Z &rarr; A</option>
                    <option value="category"       <?= $activeSort === 'category'       ? 'selected' : '' ?>>By Category</option>
                    <option value="in_stock_first" <?= $activeSort === 'in_stock_first' ? 'selected' : '' ?>>In Stock First</option>
                </select>
            </div>
            <label class="sp-stock-chip <?= $inStockOnly ? 'is-active' : '' ?>" title="Show only immediately available products">
                <input type="checkbox" name="stock" value="in_stock" id="sp-stock-check" <?= $inStockOnly ? 'checked' : '' ?>>
                &#10003; In Stock Only
            </label>
        </div>
        <div class="sp-filter-actions">
            <button type="submit" class="btn sp-apply-btn">Apply</button>
            <a href="/e-shop" class="btn btn-outline">Reset</a>
        </div>
    </form>
</div>

<!-- ── AJAX-replaced live results ── -->
<div id="sp-live-results">

<!-- ── Metrics strip ── -->
<div class="sp-metrics-strip">
    <div class="sp-metrics-inner">
        <div class="sp-metric">
            <span>Results</span>
            <strong><?= esc((string) $resultCount) ?></strong>
        </div>
        <div class="sp-metric">
            <span>Categories</span>
            <strong><?= esc((string) count($categories)) ?></strong>
        </div>
        <div class="sp-metric">
            <span>Basket</span>
            <strong class="js-cart-count-strip"><?= esc((string) $cartCount) ?></strong>
        </div>
        <?php if ($activeCategory !== ''): ?>
            <div class="sp-metric">
                <span>Viewing</span>
                <strong style="font-size:16px"><?= esc($activeCategory) ?></strong>
            </div>
        <?php endif; ?>
        <?php if ($inStockOnly): ?>
            <div class="sp-metric">
                <span>Filter</span>
                <strong style="font-size:13px;color:#4ade80">&#10003; In Stock</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Category rail ── -->
<?php if ($categorySummaries !== []): ?>
<section class="sp-cat-section">
    <div class="sp-cat-inner">
        <span class="sp-section-eyebrow">Browse by Category</span>
        <div class="sp-cat-rail">
            <a href="/e-shop" class="sp-cat-chip sp-cat-chip--js <?= $activeCategory === '' ? 'is-active' : '' ?>" data-cat="">
                All Products
                <span class="sp-cat-count"><?= esc((string) $totalProducts) ?></span>
            </a>
            <?php foreach ($categorySummaries as $summary): ?>
                <a href="/e-shop?category=<?= urlencode($summary['name']) ?>"
                   class="sp-cat-chip sp-cat-chip--js <?= $activeCategory === $summary['name'] ? 'is-active' : '' ?>"
                   data-cat="<?= esc($summary['name']) ?>">
                    <?= esc($summary['name']) ?>
                    <span class="sp-cat-count"><?= esc((string) $summary['count']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Product Grid ── -->
<section class="sp-results-section">
    <div class="sp-results-inner">

        <div class="sp-results-header">
            <div>
                <h2><?= $activeCategory !== '' ? 'Browse ' . esc($activeCategory) : 'Browse the catalogue' ?></h2>
                <p class="sp-results-summary"><?= esc($filterSummary) ?></p>
            </div>
            <span class="sp-count-tag">
                &#128230; <?= esc((string) $resultCount) ?> product<?= $resultCount !== 1 ? 's' : '' ?>
            </span>
        </div>

        <div class="sp-product-grid">
            <?php if ($products === []): ?>
                <div class="sp-empty">
                    <div class="sp-empty-icon">&#128269;</div>
                    <h3>No products matched your search.</h3>
                    <p>Adjust the keyword, change the category, or clear all filters to reopen the full catalogue.</p>
                    <?php if ($categorySummaries !== []): ?>
                    <div class="sp-empty-cats">
                        <p class="sp-empty-cats-label">Try browsing by category:</p>
                        <div class="sp-empty-cat-chips">
                            <?php foreach (array_slice($categorySummaries, 0, 6) as $summary): ?>
                                <a href="/e-shop?category=<?= urlencode($summary['name']) ?>"
                                   class="sp-cat-chip sp-cat-chip--js"
                                   data-cat="<?= esc($summary['name']) ?>">
                                    <?= esc($summary['name']) ?>
                                    <span class="sp-cat-count"><?= esc((string) $summary['count']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="/e-shop" class="btn" style="margin-top:20px">Clear All Filters</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <article class="sp-card">
                        <a href="/e-shop/product/<?= esc($product['slug']) ?>" class="sp-card-img-wrap" tabindex="-1">
                            <img class="sp-card-img"
                                 src="<?= esc($product['image_url'] ?: '/assets/images/sparePart.webp') ?>"
                                 alt="<?= esc($product['name']) ?>"
                                 loading="lazy"
                                 decoding="async">
                            <span class="sp-card-cat-badge"><?= esc($product['category']) ?></span>
                            <?php if (! empty($product['is_featured'])): ?>
                                <span class="sp-card-featured-badge">&#9733; Featured</span>
                            <?php endif; ?>
                            <?php if (isset($product['stock_status']) && $product['stock_status'] !== 'in_stock'): ?>
                                <span class="sp-card-stock-badge sp-stock--<?= esc($product['stock_status']) ?>">
                                    <?= $product['stock_status'] === 'made_to_order' ? 'MTO' : 'Out of Stock' ?>
                                </span>
                            <?php endif; ?>
                            <?php if (empty($product['price_label'])): ?>
                                <span class="sp-card-rfq-badge">RFQ</span>
                            <?php endif; ?>
                        </a>

                        <div class="sp-card-body">
                            <?php if (! empty($product['sku'])): ?>
                                <span class="sp-card-sku">SKU &mdash; <?= esc($product['sku']) ?></span>
                            <?php endif; ?>
                            <h3><?= esc($product['name']) ?></h3>
                            <p class="sp-card-desc"><?= esc($product['short_description'] ?: $product['description']) ?></p>
                            <div class="sp-card-meta">
                                <?php if (! empty($product['price_label'])): ?>
                                    <span class="sp-card-price">&#127991; <?= esc($product['price_label']) ?></span>
                                <?php endif; ?>
                                <?php if (! empty($product['lead_time'])): ?>
                                    <span class="sp-card-lead">&#9201; <?= esc($product['lead_time']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sp-card-footer">
                            <a href="/e-shop/product/<?= esc($product['slug']) ?>" class="btn btn-dark">View Details</a>
                            <form method="post" action="/cart/add" class="sp-add-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= esc((string) $product['id']) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-outline sp-add-basket" data-name="<?= esc($product['name']) ?>">+ Basket</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Pagination ── -->
<?php if ($pageCount > 1):
    $pgBase = [];
    if ($searchQuery  !== '')         $pgBase['q']        = $searchQuery;
    if ($activeCategory !== '')       $pgBase['category'] = $activeCategory;
    if ($activeSort !== 'name_asc')   $pgBase['sort']     = $activeSort;
    if ($inStockOnly)                 $pgBase['stock']    = 'in_stock';
    $pgUrl  = fn(int $p): string => '/e-shop?' . http_build_query(array_merge($pgBase, ['page' => $p]));
    $pgFrom = max(1, $page - 2);
    $pgTo   = min($pageCount, $page + 2);
?>
<nav class="sp-pagination-wrap" aria-label="Product catalogue pages">
    <div class="sp-pagination">
        <?php if ($page > 1): ?>
            <a href="<?= esc($pgUrl(1)) ?>" class="btn btn-outline sp-pg-btn" aria-label="First page">&#171;</a>
            <a href="<?= esc($pgUrl($page - 1)) ?>" class="btn btn-outline sp-pg-btn" aria-label="Previous page">&#8592;</a>
        <?php endif; ?>

        <?php if ($pgFrom > 1): ?><span class="sp-pg-ellipsis">&hellip;</span><?php endif; ?>

        <?php for ($p = $pgFrom; $p <= $pgTo; $p++): ?>
            <a href="<?= esc($pgUrl($p)) ?>"
               class="btn sp-pg-btn <?= $p === $page ? 'sp-pg-active' : 'btn-outline' ?>"
               aria-current="<?= $p === $page ? 'page' : 'false' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($pgTo < $pageCount): ?><span class="sp-pg-ellipsis">&hellip;</span><?php endif; ?>

        <?php if ($page < $pageCount): ?>
            <a href="<?= esc($pgUrl($page + 1)) ?>" class="btn btn-outline sp-pg-btn" aria-label="Next page">&#8594;</a>
            <a href="<?= esc($pgUrl($pageCount)) ?>" class="btn btn-outline sp-pg-btn" aria-label="Last page">&#187;</a>
        <?php endif; ?>

        <span class="sp-page-info">Page <?= esc((string) $page) ?> of <?= esc((string) $pageCount) ?></span>
    </div>
</nav>
<?php endif; ?>

</div><!-- #sp-live-results -->

<!-- ── Toast notification ── -->
<div id="sp-toast" aria-live="polite" aria-atomic="true"></div>

<!-- ── Promo cards ── -->
<section class="sp-promo-section">
    <div class="sp-promo-inner">
        <div class="sp-promo-grid">
            <div class="sp-promo-card sp-promo-card--dark">
                <span class="sp-promo-label">RFQ Workflow</span>
                <p class="sp-promo-title">Build one shortlist, send one enquiry.</p>
                <p class="sp-promo-desc">Move from catalogue scan to a consolidated commercial request without repeating buyer details across multiple emails.</p>
            </div>
            <div class="sp-promo-card">
                <span class="sp-promo-label">Category Browse</span>
                <p class="sp-promo-title"><?= esc((string) count($categories)) ?> active product groups</p>
                <p class="sp-promo-desc">Jump between equipment families, kits, and spare-part collections without losing your search context.</p>
            </div>
            <div class="sp-promo-card">
                <span class="sp-promo-label">Procurement Ready</span>
                <p class="sp-promo-title"><?= esc((string) $resultCount) ?> live catalogue lines</p>
                <p class="sp-promo-desc">Review available SKUs and escalate to technical support when fitment or application validation is required.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA Banner ── -->
<div class="sp-cta-wrap">
    <div class="sp-cta-section">
        <div class="sp-cta-text">
            <span>Commercial Support</span>
            <h2>Need a faster procurement path?</h2>
            <p>Send the entire basket as a single quote request, or speak directly with the BSAS team for application matching, interchange validation, and dispatch guidance.</p>
        </div>
        <div class="sp-cta-actions">
            <a href="/cart" class="btn">Submit Basket RFQ</a>
            <a href="/support" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.35)">Talk to Support</a>
        </div>
    </div>
</div>

<style>
/* ── Stock badge ── */
.sp-card-stock-badge {
    position:absolute; top:34px; left:8px;
    font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    padding:2px 9px; border-radius:20px; pointer-events:none;
}
.sp-stock--made_to_order { background:#fef3c7; color:#92400e; }
.sp-stock--out_of_stock  { background:#fee2e2; color:#991b1b; }

/* ── Featured badge ── */
.sp-card-featured-badge {
    position:absolute; top:8px; right:8px;
    font-size:10px; font-weight:700; letter-spacing:.3px;
    padding:3px 10px; border-radius:20px;
    background:linear-gradient(135deg,#f59b23,#e07c00);
    color:#111; pointer-events:none;
}

/* ── Lead time ── */
.sp-card-meta { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
.sp-card-lead {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:600; color:#888;
    background:#1a1a1a; border:1px solid #2a2a2a;
    padding:2px 8px; border-radius:12px;
}

/* ── Cart badge ── */
.sp-cart-badge--hidden { display:none; }

/* ── Stock chip in filter bar ── */
.sp-stock-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 14px; border-radius:6px; cursor:pointer;
    border:1.5px solid rgba(255,255,255,.15);
    font-size:13px; font-weight:600; color:#999;
    background:transparent; transition:all .15s; white-space:nowrap;
    user-select:none;
}
.sp-stock-chip:hover { border-color:rgba(255,255,255,.35); color:#fff; }
.sp-stock-chip.is-active {
    background:rgba(74,222,128,.12);
    border-color:#4ade80;
    color:#4ade80;
}
.sp-stock-chip input { position:absolute; opacity:0; width:0; height:0; }

/* ── Pagination ── */
.sp-pg-btn { min-width:36px; padding:8px 12px; font-size:13px; }
.sp-pg-active { background:#f59b23 !important; color:#111 !important; border-color:#f59b23 !important; font-weight:700; }
.sp-pg-ellipsis { display:inline-flex; align-items:center; padding:0 4px; color:#555; font-size:14px; }

/* ── Empty state categories ── */
.sp-empty-cats { margin-top:24px; text-align:center; }
.sp-empty-cats-label { font-size:13px; color:#666; margin-bottom:12px; }
.sp-empty-cat-chips { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; max-width:560px; margin:0 auto; }

/* ── Toast ── */
#sp-toast {
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background:#1a1a1a; color:#fff;
    padding:12px 20px; border-radius:8px;
    font-size:14px; font-weight:600;
    border-left:4px solid #f59b23;
    box-shadow:0 4px 20px rgba(0,0,0,.45);
    transform:translateY(16px); opacity:0;
    transition:transform .2s ease, opacity .2s ease;
    pointer-events:none; max-width:300px;
}
#sp-toast.sp-toast--show { transform:translateY(0); opacity:1; }

/* ── Loading state on basket button ── */
.sp-add-basket.is-loading { opacity:.5; pointer-events:none; }
</style>

<script>
(function () {
    'use strict';

    /* ─── Helpers ─────────────────────────────────────── */
    function showToast(msg) {
        var t = document.getElementById('sp-toast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('sp-toast--show');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () { t.classList.remove('sp-toast--show'); }, 2600);
    }

    function updateCartBadges(count) {
        /* hero badge */
        document.querySelectorAll('.sp-cart-badge').forEach(function (b) {
            b.textContent = count;
            b.classList.toggle('sp-cart-badge--hidden', count < 1);
        });
        /* hero stat and metrics strip */
        document.querySelectorAll('.js-cart-count, .js-cart-count-strip').forEach(function (el) {
            el.textContent = count;
        });
    }

    /* ─── AJAX Live Search ─────────────────────────────── */
    var form = document.getElementById('sp-filter-form');
    var searchInput = document.getElementById('sp-search-input');
    var searchTimer;

    function doLiveSearch() {
        if (!form) return;
        var params = new URLSearchParams(new FormData(form));
        /* remove empty values for a clean URL */
        Array.from(params.keys()).forEach(function (k) {
            if (params.get(k) === '') params.delete(k);
        });
        var url = '/e-shop?' + params.toString();

        var live = document.getElementById('sp-live-results');
        if (live) live.style.opacity = '0.5';

        fetch(url, { headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var fresh = doc.getElementById('sp-live-results');
                var current = document.getElementById('sp-live-results');
                if (fresh && current) {
                    current.innerHTML = fresh.innerHTML;
                    current.style.opacity = '1';
                    /* re-wire category chips inside the updated fragment */
                    wireCatChips();
                }
                history.replaceState(null, '', url);
            })
            .catch(function () {
                if (live) live.style.opacity = '1';
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(doLiveSearch, 420);
        });
    }

    /* auto-submit on select/checkbox change */
    if (form) {
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', doLiveSearch);
        });
        var stockCheck = document.getElementById('sp-stock-check');
        if (stockCheck) {
            stockCheck.addEventListener('change', function () {
                /* toggle chip style immediately */
                var chip = this.closest('.sp-stock-chip');
                if (chip) chip.classList.toggle('is-active', this.checked);
                doLiveSearch();
            });
        }
    }

    /* ─── Category chip AJAX navigation ───────────────── */
    function wireCatChips() {
        document.querySelectorAll('.sp-cat-chip--js').forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                var catSel = document.getElementById('sp-cat-select');
                if (!catSel) return;
                e.preventDefault();
                catSel.value = this.dataset.cat || '';
                doLiveSearch();
            });
        });
    }
    wireCatChips();

    /* ─── AJAX Add-to-Basket ───────────────────────────── */
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.sp-add-form');
        if (!form) return;
        e.preventDefault();

        var btn = form.querySelector('.sp-add-basket');
        if (btn) btn.classList.add('is-loading');

        var fd = new FormData(form);

        fetch('/cart/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (btn) btn.classList.remove('is-loading');
            if (data.success) {
                updateCartBadges(data.cartCount);
                showToast('✓ Added: ' + data.productName);
            } else {
                showToast('Could not add to basket.');
            }
        })
        .catch(function () {
            if (btn) btn.classList.remove('is-loading');
            /* fall back to normal form submit */
            form.submit();
        });
    });

}());
</script>
