<?php
// Base query-string params representing every currently active filter (never includes `page`).
// Reused by the mobile filter-toggle indicator, pagination links, and each filter chip's
// "remove just this one" link further down — computed once, up front, before anything uses it.
$queryBase = [];
if ($searchQuery !== '')        $queryBase['q']          = $searchQuery;
if ($activeCategories !== [])   $queryBase['category']    = $activeCategories;
if ($activeSort !== 'name_asc') $queryBase['sort']        = $activeSort;
if ($activeStockStatuses !== []) $queryBase['stock']      = $activeStockStatuses;
if ($priceMin !== null)         $queryBase['price_min']   = $priceMin;
if ($priceMax !== null)         $queryBase['price_max']   = $priceMax;
if ($featuredOnly)              $queryBase['featured']    = '1';
if ($saleOnly)                  $queryBase['sale']        = '1';
if ($activeVehicleIds !== [])   $queryBase['vehicle']     = $activeVehicleIds;
if ($activeMaterials !== [])    $queryBase['material']    = $activeMaterials;
if ($activeDivisionIds !== [])  $queryBase['division']    = $activeDivisionIds;
if ($activeLabelIds !== [])     $queryBase['label']       = $activeLabelIds;
if ($activeOemId > 0)           $queryBase['oem']         = $activeOemId;

$hasActiveFilters = $queryBase !== [];

$chipUrl = function (array $unsetKeys, ?string $categoryToRemove = null, ?int $vehicleToRemove = null, ?string $stockToRemove = null, ?string $materialToRemove = null, ?int $divisionToRemove = null, ?int $labelToRemove = null) use ($queryBase, $activeCategories, $activeVehicleIds, $activeStockStatuses, $activeMaterials, $activeDivisionIds, $activeLabelIds): string {
    $params = $queryBase;
    foreach ($unsetKeys as $k) {
        unset($params[$k]);
    }
    if ($categoryToRemove !== null) {
        $remaining = array_values(array_diff($activeCategories, [$categoryToRemove]));
        if ($remaining === []) {
            unset($params['category']);
        } else {
            $params['category'] = $remaining;
        }
    }
    if ($vehicleToRemove !== null) {
        $remaining = array_values(array_diff($activeVehicleIds, [$vehicleToRemove]));
        if ($remaining === []) {
            unset($params['vehicle']);
        } else {
            $params['vehicle'] = $remaining;
        }
    }
    if ($stockToRemove !== null) {
        $remaining = array_values(array_diff($activeStockStatuses, [$stockToRemove]));
        if ($remaining === []) {
            unset($params['stock']);
        } else {
            $params['stock'] = $remaining;
        }
    }
    if ($materialToRemove !== null) {
        $remaining = array_values(array_diff($activeMaterials, [$materialToRemove]));
        if ($remaining === []) {
            unset($params['material']);
        } else {
            $params['material'] = $remaining;
        }
    }
    if ($divisionToRemove !== null) {
        $remaining = array_values(array_diff($activeDivisionIds, [$divisionToRemove]));
        if ($remaining === []) {
            unset($params['division']);
        } else {
            $params['division'] = $remaining;
        }
    }
    if ($labelToRemove !== null) {
        $remaining = array_values(array_diff($activeLabelIds, [$labelToRemove]));
        if ($remaining === []) {
            unset($params['label']);
        } else {
            $params['label'] = $remaining;
        }
    }
    return '/e-shop' . ($params !== [] ? '?' . http_build_query($params) : '');
};

$stockLabels = ['in_stock' => 'In Stock', 'made_to_order' => 'Made to Order', 'out_of_stock' => 'Out of Stock'];

// Flat lookup of vehicle id => name, for chip labels.
$vehicleNameById = array_column($vehiclesForSelectedOem, 'name', 'id');

// Flat lookups for division/label chip labels.
$divisionNameById = array_column($divisionSummaries, 'name', 'id');
$labelNameById    = array_column($labelSummaries, 'name', 'id');
?>

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
            <p class="sp-hero-sub">Search by SKU, filter by category and price, and shortlist items for one consolidated RFQ &mdash; no repeat forms, no scattered emails.</p>
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

<!-- ── Search bar (sticky) — filter controls now live in the sidebar below ── -->
<div class="sp-filter-bar">
    <form id="sp-filter-form" method="get" action="/e-shop" class="sp-filter-inner">
        <div class="sp-search-wrap">
            <span class="sp-search-icon">&#128269;</span>
            <input id="sp-search-input" type="search" name="q"
                   value="<?= esc($searchQuery) ?>"
                   placeholder="Search by SKU, product name, or description&hellip;"
                   autocomplete="off">
        </div>
        <button type="button" class="btn btn-outline sp-sidebar-toggle" id="sp-sidebar-toggle">
            &#9776; Filters
            <?php if ($hasActiveFilters): ?><span class="sp-filter-count-dot"></span><?php endif; ?>
        </button>
    </form>
</div>

<!-- ── Sidebar + Results layout ── -->
<div class="sp-shop-layout">

    <!-- ── Sidebar (outside the AJAX-swapped region — its own counts/bounds reflect the whole catalogue) ── -->
    <aside class="sp-shop-sidebar" id="sp-shop-sidebar">
        <div class="sp-sidebar-head">
            <h2>Filters</h2>
            <button type="button" class="sp-sidebar-close" id="sp-sidebar-close" aria-label="Close filters">&times;</button>
        </div>

        <?php if ($hasActiveFilters): ?>
            <a href="/e-shop" class="btn btn-outline sp-reset-all">&#10227; Reset all filters</a>
        <?php endif; ?>

        <!-- Hidden state carried by every AJAX re-submit of the filter form. -->
        <input type="hidden" name="oem" value="<?= esc((string) $activeOemId) ?>" form="sp-filter-form">

        <!-- ── Step 1: pick an OEM, then its vehicles. Every filter below narrows to match. ── -->
        <div class="sp-filter-group sp-filter-group--step">
            <h3>Find by Machine</h3>
            <?php if ($activeOemId <= 0): ?>
                <?php if ($oemSummaries !== []): ?>
                    <div class="sp-oem-chip-row">
                        <?php foreach ($oemSummaries as $oem): ?>
                            <a href="<?= esc('/e-shop?oem=' . $oem['id']) ?>" class="sp-oem-chip">
                                <?= esc($oem['name']) ?> <span class="sp-filter-count"><?= esc((string) $oem['count']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php $selectedOemName = array_column($oemSummaries, 'name', 'id')[$activeOemId] ?? 'OEM'; ?>
                <div class="sp-oem-selected">
                    <span class="sp-oem-selected-pill"><?= esc($selectedOemName) ?></span>
                    <a href="/e-shop" class="sp-oem-change-link">Change</a>
                </div>
                <?php if ($vehiclesForSelectedOem !== []): ?>
                    <div class="sp-filter-checklist" style="margin-top:10px">
                        <?php foreach ($vehiclesForSelectedOem as $vehicle): ?>
                            <label class="sp-filter-checkbox">
                                <input type="checkbox" name="vehicle[]" value="<?= esc((string) $vehicle['id']) ?>" form="sp-filter-form"
                                       <?= in_array($vehicle['id'], $activeVehicleIds, true) ? 'checked' : '' ?>>
                                <span><?= esc($vehicle['name']) ?></span>
                                <span class="sp-filter-count"><?= esc((string) $vehicle['product_count']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="adm-field-hint" style="margin-top:8px">No vehicles registered for this OEM yet.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($categorySummaries !== []): ?>
        <details class="sp-filter-group" open>
            <summary><h3>Category</h3></summary>
            <div class="sp-filter-checklist">
                <?php foreach ($categorySummaries as $summary): ?>
                    <label class="sp-filter-checkbox">
                        <input type="checkbox" name="category[]" value="<?= esc($summary['name']) ?>" form="sp-filter-form"
                               <?= in_array($summary['name'], $activeCategories, true) ? 'checked' : '' ?>>
                        <span><?= esc($summary['name']) ?></span>
                        <span class="sp-filter-count"><?= esc((string) $summary['count']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if ($divisionSummaries !== []): ?>
        <details class="sp-filter-group" <?= $activeDivisionIds !== [] ? 'open' : '' ?>>
            <summary><h3>Division</h3></summary>
            <div class="sp-filter-checklist">
                <?php foreach ($divisionSummaries as $summary): ?>
                    <label class="sp-filter-checkbox">
                        <input type="checkbox" name="division[]" value="<?= esc((string) $summary['id']) ?>" form="sp-filter-form"
                               <?= in_array($summary['id'], $activeDivisionIds, true) ? 'checked' : '' ?>>
                        <span><?= esc($summary['name']) ?></span>
                        <span class="sp-filter-count"><?= esc((string) $summary['count']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if ($priceBounds['max'] > 0): ?>
        <details class="sp-filter-group" <?= ($priceMin !== null || $priceMax !== null) ? 'open' : '' ?>>
            <summary><h3>Price Range</h3></summary>
            <div class="sp-price-range-inputs">
                <input type="number" name="price_min" form="sp-filter-form" id="sp-price-min"
                       min="0" step="1" placeholder="&#8377;<?= esc((string) (int) $priceBounds['min']) ?>"
                       value="<?= esc($priceMin !== null ? (string) $priceMin : '') ?>">
                <span>&ndash;</span>
                <input type="number" name="price_max" form="sp-filter-form" id="sp-price-max"
                       min="0" step="1" placeholder="&#8377;<?= esc((string) (int) $priceBounds['max']) ?>"
                       value="<?= esc($priceMax !== null ? (string) $priceMax : '') ?>">
            </div>
            <button type="button" class="btn btn-outline sp-price-apply" id="sp-price-apply">Go</button>
        </details>
        <?php endif; ?>

        <?php if ($stockStatusSummaries !== []): ?>
        <details class="sp-filter-group" <?= $activeStockStatuses !== [] ? 'open' : '' ?>>
            <summary><h3>Availability</h3></summary>
            <div class="sp-filter-checklist">
                <?php foreach ($stockStatusSummaries as $summary): ?>
                    <label class="sp-filter-checkbox">
                        <input type="checkbox" name="stock[]" value="<?= esc($summary['value']) ?>" form="sp-filter-form"
                               <?= in_array($summary['value'], $activeStockStatuses, true) ? 'checked' : '' ?>>
                        <span><?= esc($summary['label']) ?></span>
                        <span class="sp-filter-count"><?= esc((string) $summary['count']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>

        <details class="sp-filter-group" <?= ($featuredOnly || $saleOnly) ? 'open' : '' ?>>
            <summary><h3>Featured &amp; Deals</h3></summary>
            <label class="sp-filter-checkbox">
                <input type="checkbox" name="featured" value="1" form="sp-filter-form" id="sp-featured-check" <?= $featuredOnly ? 'checked' : '' ?>>
                <span>Featured Products</span>
            </label>
            <label class="sp-filter-checkbox">
                <input type="checkbox" name="sale" value="1" form="sp-filter-form" id="sp-sale-check" <?= $saleOnly ? 'checked' : '' ?>>
                <span>On Sale</span>
            </label>
        </details>

        <?php if ($materialSummaries !== []): ?>
        <details class="sp-filter-group" <?= $activeMaterials !== [] ? 'open' : '' ?>>
            <summary><h3>Material</h3></summary>
            <div class="sp-filter-checklist">
                <?php foreach ($materialSummaries as $summary): ?>
                    <label class="sp-filter-checkbox">
                        <input type="checkbox" name="material[]" value="<?= esc($summary['name']) ?>" form="sp-filter-form"
                               <?= in_array($summary['name'], $activeMaterials, true) ? 'checked' : '' ?>>
                        <span><?= esc($summary['name']) ?></span>
                        <span class="sp-filter-count"><?= esc((string) $summary['count']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if ($labelSummaries !== []): ?>
        <details class="sp-filter-group" <?= $activeLabelIds !== [] ? 'open' : '' ?>>
            <summary><h3>Labels</h3></summary>
            <div class="sp-filter-checklist">
                <?php foreach ($labelSummaries as $summary): ?>
                    <label class="sp-filter-checkbox">
                        <input type="checkbox" name="label[]" value="<?= esc((string) $summary['id']) ?>" form="sp-filter-form"
                               <?= in_array($summary['id'], $activeLabelIds, true) ? 'checked' : '' ?>>
                        <span><?= esc($summary['name']) ?></span>
                        <span class="sp-filter-count"><?= esc((string) $summary['count']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>
    </aside>
    <button type="button" class="sp-sidebar-backdrop" id="sp-sidebar-backdrop" aria-label="Close filters" hidden></button>

    <!-- ── AJAX-replaced live results ── -->
    <div id="sp-live-results" class="sp-shop-main">

        <!-- ── Applied filter chips ── -->
        <?php if ($hasActiveFilters): ?>
        <div class="sp-chip-row">
            <?php if ($searchQuery !== ''): ?>
                <a href="<?= esc($chipUrl(['q'])) ?>" class="sp-filter-chip">Search: &ldquo;<?= esc($searchQuery) ?>&rdquo; &times;</a>
            <?php endif; ?>
            <?php foreach ($activeCategories as $cat): ?>
                <a href="<?= esc($chipUrl([], $cat)) ?>" class="sp-filter-chip"><?= esc($cat) ?> &times;</a>
            <?php endforeach; ?>
            <?php foreach ($activeVehicleIds as $vid): ?>
                <a href="<?= esc($chipUrl([], null, $vid)) ?>" class="sp-filter-chip"><?= esc($vehicleNameById[$vid] ?? 'Vehicle') ?> &times;</a>
            <?php endforeach; ?>
            <?php if ($priceMin !== null || $priceMax !== null): ?>
                <a href="<?= esc($chipUrl(['price_min', 'price_max'])) ?>" class="sp-filter-chip">
                    Price:
                    <?php if ($priceMin !== null && $priceMax !== null): ?>
                        &#8377;<?= esc((string) (int) $priceMin) ?>&ndash;&#8377;<?= esc((string) (int) $priceMax) ?>
                    <?php elseif ($priceMin !== null): ?>
                        Over &#8377;<?= esc((string) (int) $priceMin) ?>
                    <?php else: ?>
                        Under &#8377;<?= esc((string) (int) $priceMax) ?>
                    <?php endif; ?>
                    &times;
                </a>
            <?php endif; ?>
            <?php foreach ($activeStockStatuses as $stockValue): ?>
                <a href="<?= esc($chipUrl([], null, null, $stockValue)) ?>" class="sp-filter-chip"><?= esc($stockLabels[$stockValue] ?? $stockValue) ?> &times;</a>
            <?php endforeach; ?>
            <?php if ($featuredOnly): ?>
                <a href="<?= esc($chipUrl(['featured'])) ?>" class="sp-filter-chip">Featured &times;</a>
            <?php endif; ?>
            <?php if ($saleOnly): ?>
                <a href="<?= esc($chipUrl(['sale'])) ?>" class="sp-filter-chip">On Sale &times;</a>
            <?php endif; ?>
            <?php foreach ($activeMaterials as $mat): ?>
                <a href="<?= esc($chipUrl([], null, null, null, $mat)) ?>" class="sp-filter-chip"><?= esc($mat) ?> &times;</a>
            <?php endforeach; ?>
            <?php foreach ($activeDivisionIds as $did): ?>
                <a href="<?= esc($chipUrl([], null, null, null, null, $did)) ?>" class="sp-filter-chip"><?= esc($divisionNameById[$did] ?? 'Division') ?> &times;</a>
            <?php endforeach; ?>
            <?php foreach ($activeLabelIds as $lid): ?>
                <a href="<?= esc($chipUrl([], null, null, null, null, null, $lid)) ?>" class="sp-filter-chip"><?= esc($labelNameById[$lid] ?? 'Label') ?> &times;</a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── Results header ── -->
        <div class="sp-results-header">
            <div>
                <h2><?= $activeCategories !== [] ? 'Browse ' . esc(implode(', ', $activeCategories)) : 'Browse the catalogue' ?></h2>
                <p class="sp-results-summary"><?= esc($filterSummary) ?></p>
            </div>
            <div class="sp-results-header-actions">
                <span class="sp-count-tag">
                    &#128230; <?= esc((string) $resultCount) ?> product<?= $resultCount !== 1 ? 's' : '' ?>
                </span>
                <div class="sp-select-wrap">
                    <select name="sort" id="sp-sort-select" form="sp-filter-form">
                        <option value="name_asc"       <?= $activeSort === 'name_asc'       ? 'selected' : '' ?>>Name A &rarr; Z</option>
                        <option value="name_desc"      <?= $activeSort === 'name_desc'      ? 'selected' : '' ?>>Name Z &rarr; A</option>
                        <option value="category"       <?= $activeSort === 'category'       ? 'selected' : '' ?>>By Category</option>
                        <option value="in_stock_first" <?= $activeSort === 'in_stock_first' ? 'selected' : '' ?>>In Stock First</option>
                        <option value="price_asc"      <?= $activeSort === 'price_asc'      ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc"     <?= $activeSort === 'price_desc'     ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ── Product Grid ── -->
        <div class="sp-product-grid">
            <?php if ($products === []): ?>
                <div class="sp-empty">
                    <div class="sp-empty-icon">&#128269;</div>
                    <h3>No products matched your search.</h3>
                    <p>Adjust the keyword, change the filters, or clear everything to reopen the full catalogue.</p>
                    <a href="/e-shop" class="btn" style="margin-top:20px">Clear All Filters</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <article class="sp-card">
                        <a href="/e-shop/product/<?= esc($product['slug']) ?>" class="sp-card-img-wrap" tabindex="-1">
                            <img class="sp-card-img"
                                 src="<?= esc($product['image_url'] ?: '/assets/images/sparePart.webp') ?>"
                                 alt="<?= esc($product['image_alt_text'] ?: $product['name']) ?>"
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
                            <?php if (empty($product['price_label']) && (float) ($product['price'] ?? 0) <= 0): ?>
                                <span class="sp-card-rfq-badge">RFQ</span>
                            <?php endif; ?>
                        </a>

                        <div class="sp-card-body">
                            <?php if (! empty($product['sku'])): ?>
                                <span class="sp-card-sku">SKU &mdash; <?= esc($product['sku']) ?></span>
                            <?php endif; ?>
                            <h3><?= esc($product['name']) ?></h3>
                            <?php if (! empty($product['labels'])): ?>
                                <div class="sp-card-labels">
                                    <?php foreach ($product['labels'] as $labelName): ?>
                                        <span class="sp-card-label-badge"><?= esc($labelName) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p class="sp-card-desc"><?= esc($product['short_description'] ?: $product['description']) ?></p>
                            <div class="sp-card-meta">
                                <?php if ((float) ($product['price'] ?? 0) > 0): ?>
                                    <span class="sp-card-price">
                                        &#8377; <?= esc(number_format((float) $product['price'], 2)) ?>
                                        <?php if (! empty($product['compare_at_price']) && (float) $product['compare_at_price'] > (float) $product['price']): ?>
                                            <s style="opacity:.55;font-weight:400;margin-left:6px">&#8377; <?= esc(number_format((float) $product['compare_at_price'], 2)) ?></s>
                                        <?php endif; ?>
                                    </span>
                                <?php elseif (! empty($product['price_label'])): ?>
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

        <!-- ── Pagination ── -->
        <?php if ($pageCount > 1):
            $pgUrl  = fn(int $p): string => '/e-shop?' . http_build_query(array_merge($queryBase, ['page' => $p]));
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

    </div><!-- /#sp-live-results -->
</div><!-- /.sp-shop-layout -->

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

/* ── Merchandising labels ── */
.sp-card-labels { display:flex; flex-wrap:wrap; gap:4px; margin:4px 0 2px; }
.sp-card-label-badge {
    font-size:10px; font-weight:700; letter-spacing:.3px;
    padding:2px 8px; border-radius:20px;
    background:#eef2ff; color:#4338ca;
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

/* ── Pagination ── */
.sp-pg-btn { min-width:36px; padding:8px 12px; font-size:13px; }
.sp-pg-active { background:#f59b23 !important; color:#111 !important; border-color:#f59b23 !important; font-weight:700; }
.sp-pg-ellipsis { display:inline-flex; align-items:center; padding:0 4px; color:#555; font-size:14px; }

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
                }

                /* Sidebar facet counts (Category, Division, Material, etc.) narrow based
                   on the OEM/Vehicle step, so it must be refreshed too — not just the grid. */
                var freshSidebar = doc.getElementById('sp-shop-sidebar');
                var currentSidebar = document.getElementById('sp-shop-sidebar');
                if (freshSidebar && currentSidebar) {
                    currentSidebar.innerHTML = freshSidebar.innerHTML;
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

    /* Auto-submit on any filter control change (sort select, all sidebar checkboxes).
       Delegated on document — the sidebar's checkboxes get replaced wholesale on every
       live search above, so listeners bound to the original nodes would otherwise be lost. */
    document.addEventListener('change', function (e) {
        if (e.target.matches && e.target.matches('select[form="sp-filter-form"], input[type="checkbox"][form="sp-filter-form"]')) {
            doLiveSearch();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'sp-price-apply') {
            doLiveSearch();
        }
    });

    /* ─── AJAX Add-to-Basket ───────────────────────────── */
    document.addEventListener('submit', function (e) {
        var addForm = e.target.closest('.sp-add-form');
        if (!addForm) return;
        e.preventDefault();

        var btn = addForm.querySelector('.sp-add-basket');
        if (btn) btn.classList.add('is-loading');

        var fd = new FormData(addForm);

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
            addForm.submit();
        });
    });

    /* ─── Mobile sidebar drawer ─────────────────────────── */
    var sidebar   = document.getElementById('sp-shop-sidebar');
    var toggleBtn = document.getElementById('sp-sidebar-toggle');
    var backdrop  = document.getElementById('sp-sidebar-backdrop');

    function setSidebarOpen(isOpen) {
        if (!sidebar) return;
        sidebar.classList.toggle('is-open', isOpen);
        if (backdrop) {
            backdrop.classList.toggle('is-open', isOpen);
            backdrop.hidden = !isOpen;
        }
        document.body.classList.toggle('sp-sidebar-open', isOpen);
    }

    if (toggleBtn) toggleBtn.addEventListener('click', function () { setSidebarOpen(true); });
    if (backdrop) backdrop.addEventListener('click', function () { setSidebarOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setSidebarOpen(false);
    });
    /* Delegated — #sp-sidebar-close lives inside the sidebar's innerHTML, which the
       live-search swap above replaces wholesale, so a directly-bound listener would be lost. */
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'sp-sidebar-close') {
            setSidebarOpen(false);
        }
    });

}());
</script>
