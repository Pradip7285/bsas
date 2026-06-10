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
            <p class="sp-hero-sub">Search by SKU, compare categories, and shortlist items for one consolidated RFQ — no repeat forms, no scattered emails.</p>
            <div class="sp-hero-actions">
                <a href="/cart" class="btn">
                    Quote Basket
                    <?php if ($cartCount > 0): ?>
                        <span class="sp-cart-badge"><?= esc((string) $cartCount) ?></span>
                    <?php endif; ?>
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
                <strong><?= esc((string) $cartCount) ?></strong>
                <span>In Basket</span>
            </div>
        </div>
    </div>
</div>

<!-- ── Filter Bar (sticky) ── -->
<div class="sp-filter-bar">
    <form method="get" action="/e-shop" class="sp-filter-inner">
        <div class="sp-search-wrap">
            <span class="sp-search-icon">&#128269;</span>
            <input type="search" name="q"
                   value="<?= esc($searchQuery) ?>"
                   placeholder="Search by SKU, product name, or description&hellip;">
        </div>
        <div class="sp-filter-selects">
            <div class="sp-select-wrap">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat) ?>" <?= $activeCategory === $cat ? 'selected' : '' ?>>
                            <?= esc($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sp-select-wrap">
                <select name="sort">
                    <option value="name_asc"  <?= $activeSort === 'name_asc'  ? 'selected' : '' ?>>Name A &rarr; Z</option>
                    <option value="name_desc" <?= $activeSort === 'name_desc' ? 'selected' : '' ?>>Name Z &rarr; A</option>
                    <option value="category"  <?= $activeSort === 'category'  ? 'selected' : '' ?>>By Category</option>
                </select>
            </div>
        </div>
        <div class="sp-filter-actions">
            <button type="submit" class="btn">Apply</button>
            <a href="/e-shop" class="btn btn-outline">Reset</a>
        </div>
    </form>
</div>

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
            <strong><?= esc((string) $cartCount) ?></strong>
        </div>
        <?php if ($activeCategory): ?>
            <div class="sp-metric">
                <span>Viewing</span>
                <strong style="font-size:16px"><?= esc($activeCategory) ?></strong>
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
            <a href="/e-shop" class="sp-cat-chip <?= $activeCategory === '' ? 'is-active' : '' ?>">
                All Products
                <span class="sp-cat-count"><?= esc((string) $totalProducts) ?></span>
            </a>
            <?php foreach ($categorySummaries as $summary): ?>
                <a href="/e-shop?category=<?= urlencode($summary['name']) ?>"
                   class="sp-cat-chip <?= $activeCategory === $summary['name'] ? 'is-active' : '' ?>">
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
                <h2>Browse the catalogue</h2>
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
                    <a href="/e-shop" class="btn">Clear Filters</a>
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
                            <?php if (! empty($product['price_label'])): ?>
                                <span class="sp-card-rfq-badge">RFQ</span>
                            <?php endif; ?>
                        </a>

                        <div class="sp-card-body">
                            <?php if (! empty($product['sku'])): ?>
                                <span class="sp-card-sku">SKU &mdash; <?= esc($product['sku']) ?></span>
                            <?php endif; ?>
                            <h3><?= esc($product['name']) ?></h3>
                            <p class="sp-card-desc"><?= esc($product['short_description'] ?: $product['description']) ?></p>
                            <?php if (! empty($product['price_label'])): ?>
                                <span class="sp-card-price">&#127991; <?= esc($product['price_label']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="sp-card-footer">
                            <a href="/e-shop/product/<?= esc($product['slug']) ?>" class="btn btn-dark">View Details</a>
                            <form method="post" action="/cart/add">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= esc((string) $product['id']) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-outline">+ Basket</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Pagination ── -->
<?php if ($pageCount > 1): ?>
<nav class="sp-pagination-wrap" aria-label="Product catalogue pages">
    <div class="sp-pagination">
        <?php if ($page > 1): ?>
            <a href="/e-shop?<?= esc(http_build_query(['q' => $searchQuery, 'category' => $activeCategory, 'sort' => $activeSort, 'page' => $page - 1])) ?>"
               class="btn btn-outline">&#8592; Previous</a>
        <?php endif; ?>
        <span class="sp-page-info">Page <?= esc((string) $page) ?> of <?= esc((string) $pageCount) ?></span>
        <?php if ($page < $pageCount): ?>
            <a href="/e-shop?<?= esc(http_build_query(['q' => $searchQuery, 'category' => $activeCategory, 'sort' => $activeSort, 'page' => $page + 1])) ?>"
               class="btn btn-outline">Next &#8594;</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

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
