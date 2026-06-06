<?php
$pageTitle = 'Admin Dashboard';
$activeNav = 'dashboard';
$mastheadLabel = 'BSAS Admin Console';
$mastheadTitle = 'E-commerce operations dashboard';
$mastheadText = 'Track catalogue readiness, quote activity, and bulk listing work from one business-facing backend.';
$activeCoverage = $stats['totalProducts'] > 0 ? (int) round(($stats['activeProducts'] / $stats['totalProducts']) * 100) : 0;
$timelineMax = 0;
$quotePoints = [];
$brochurePoints = [];
$productPoints = [];

if ($activityTimeline !== []) {
    $timelineMax = max(array_map(static fn(array $point): int => max($point['quotes'], $point['brochures'], $point['products']), $activityTimeline));
    $timelineMax = max($timelineMax, 1);
    $count = count($activityTimeline);

    foreach ($activityTimeline as $index => $point) {
        $x = $count > 1 ? ($index / ($count - 1)) * 100 : 50;
        $quotePoints[] = number_format($x, 2, '.', '') . ',' . number_format(100 - (($point['quotes'] / $timelineMax) * 100), 2, '.', '');
        $brochurePoints[] = number_format($x, 2, '.', '') . ',' . number_format(100 - (($point['brochures'] / $timelineMax) * 100), 2, '.', '');
        $productPoints[] = number_format($x, 2, '.', '') . ',' . number_format(100 - (($point['products'] / $timelineMax) * 100), 2, '.', '');
    }
}
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/products/new" class="btn">Add Product</a>
    <a href="/admin/products/bulk" class="btn btn-dark">Bulk Upload</a>
    <a href="/admin/leads" class="btn btn-outline">View Leads</a>
<?php $this->endSection() ?>

<?= $this->section('beforeContent') ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="success-banner"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (! empty($errors)): ?>
        <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <?php if (! empty($importSummary)): ?>
        <section class="admin-panel admin-panel--soft">
            <div class="admin-panel-head">
                <div>
                    <h2>Latest Bulk Import</h2>
                    <p>Summary of the most recent spreadsheet processing run.</p>
                </div>
            </div>
            <div class="admin-mini-metrics">
                <div class="admin-mini-metric">
                    <span>Created</span>
                    <strong><?= esc((string) $importSummary['created']) ?></strong>
                </div>
                <div class="admin-mini-metric">
                    <span>Updated</span>
                    <strong><?= esc((string) $importSummary['updated']) ?></strong>
                </div>
                <div class="admin-mini-metric">
                    <span>Skipped</span>
                    <strong><?= esc((string) $importSummary['skipped']) ?></strong>
                </div>
            </div>
            <?php if (($importSummary['errors'] ?? []) !== []): ?>
                <div class="admin-import-errors">
                    <?php foreach ($importSummary['errors'] as $error): ?>
                        <p><?= esc($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
<?php $this->endSection() ?>

<?= $this->section('content') ?>
    <section class="admin-summary-grid admin-summary-grid--five">
        <article class="admin-summary-card">
            <span>Total Products</span>
            <strong><?= esc((string) $stats['totalProducts']) ?></strong>
            <p>Catalogue records currently managed in the backend.</p>
        </article>
        <article class="admin-summary-card">
            <span>Active Listings</span>
            <strong><?= esc((string) $stats['activeProducts']) ?></strong>
            <p><?= esc((string) $activeCoverage) ?>% of the catalogue is visible on the storefront.</p>
        </article>
        <article class="admin-summary-card">
            <span>Hidden Listings</span>
            <strong><?= esc((string) $stats['hiddenProducts']) ?></strong>
            <p>Products held back from the public catalogue.</p>
        </article>
        <article class="admin-summary-card admin-summary-card--accent">
            <span>Quote Requests</span>
            <strong><?= esc((string) $stats['quoteRequests']) ?></strong>
            <p><?= esc((string) $stats['cartQuotes']) ?> arrived through basket-based RFQ flow.</p>
        </article>
        <article class="admin-summary-card">
            <span>Brochure Leads</span>
            <strong><?= esc((string) $stats['brochureLeads']) ?></strong>
            <p>Download enquiries captured from gated brochure access.</p>
        </article>
    </section>

    <section class="admin-executive-grid">
        <article class="admin-panel admin-panel--hero">
            <div class="admin-panel-head admin-panel-head--hero">
                <div>
                    <span class="admin-kicker">Executive Snapshot</span>
                    <h2>Commercial health and lead momentum</h2>
                    <p>Live operating picture based on the last 7 days of catalogue activity and incoming demand.</p>
                </div>
            </div>
            <div class="admin-hero-metrics">
                <div class="admin-hero-stat">
                    <span>Health Score</span>
                    <strong><?= esc((string) $performanceSnapshot['healthScore']) ?>%</strong>
                    <p>Catalogue completeness across status, SKU, image, and descriptive content.</p>
                </div>
                <div class="admin-hero-stat">
                    <span>RFQs, Last 7 Days</span>
                    <strong><?= esc((string) $performanceSnapshot['last7Quotes']) ?></strong>
                    <p class="<?= $performanceSnapshot['quoteChangePercent'] >= 0 ? 'is-up' : 'is-down' ?>">
                        <?= $performanceSnapshot['quoteChangePercent'] >= 0 ? '+' : '' ?><?= esc((string) $performanceSnapshot['quoteChangePercent']) ?>% versus previous 7 days
                    </p>
                </div>
                <div class="admin-hero-stat">
                    <span>Brochure Leads, Last 7 Days</span>
                    <strong><?= esc((string) $performanceSnapshot['last7Brochures']) ?></strong>
                    <p class="<?= $performanceSnapshot['brochureChangePercent'] >= 0 ? 'is-up' : 'is-down' ?>">
                        <?= $performanceSnapshot['brochureChangePercent'] >= 0 ? '+' : '' ?><?= esc((string) $performanceSnapshot['brochureChangePercent']) ?>% versus previous 7 days
                    </p>
                </div>
                <div class="admin-hero-stat">
                    <span>Listings Added, Last 7 Days</span>
                    <strong><?= esc((string) $performanceSnapshot['last7Products']) ?></strong>
                    <p>Fresh catalogue additions processed through the backend.</p>
                </div>
            </div>
        </article>

        <article class="admin-panel admin-panel--soft">
            <div class="admin-panel-head">
                <div>
                    <h2>Demand Sources</h2>
                    <p>Pages and entry points generating request volume.</p>
                </div>
            </div>
            <div class="admin-source-list">
                <?php if ($sourcePageBreakdown === []): ?>
                    <p class="admin-row-meta">No source data captured yet.</p>
                <?php else: ?>
                    <?php foreach ($sourcePageBreakdown as $source): ?>
                        <?php $sourcePercent = $stats['quoteRequests'] > 0 ? (int) round(($source['count'] / $stats['quoteRequests']) * 100) : 0; ?>
                        <div class="admin-source-item">
                            <div class="admin-source-head">
                                <strong><?= esc($source['source']) ?></strong>
                                <span><?= esc((string) $source['count']) ?> RFQs</span>
                            </div>
                            <div class="admin-progress-track"><div style="width: <?= esc((string) $sourcePercent) ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <section class="admin-analytics-grid">
        <article class="admin-panel admin-panel--primary">
            <div class="admin-panel-head">
                <div>
                    <h2>14-Day Activity Trend</h2>
                    <p>Quotes, brochure leads, and product additions across the last 14 days.</p>
                </div>
                <div class="admin-chart-legend">
                    <span><i class="admin-dot admin-dot--quote"></i>RFQs</span>
                    <span><i class="admin-dot admin-dot--brochure"></i>Brochure Leads</span>
                    <span><i class="admin-dot admin-dot--product"></i>Product Adds</span>
                </div>
            </div>
            <div class="admin-chart-card">
                <div class="admin-line-chart">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="<?= esc(implode(' ', $quotePoints)) ?>" class="admin-line admin-line--quote"></polyline>
                        <polyline points="<?= esc(implode(' ', $brochurePoints)) ?>" class="admin-line admin-line--brochure"></polyline>
                        <polyline points="<?= esc(implode(' ', $productPoints)) ?>" class="admin-line admin-line--product"></polyline>
                    </svg>
                </div>
                <div class="admin-chart-axis">
                    <?php foreach ($activityTimeline as $point): ?>
                        <span><?= esc($point['label']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>

        <article class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Top Requested Products</h2>
                    <p>Items attracting the most RFQ quantity from buyers.</p>
                </div>
            </div>
            <div class="admin-ranking-list">
                <?php if ($topRequestedProducts === []): ?>
                    <p class="admin-row-meta">No requested line items have been captured yet.</p>
                <?php else: ?>
                    <?php foreach ($topRequestedProducts as $index => $product): ?>
                        <article class="admin-ranking-item">
                            <div class="admin-ranking-index"><?= esc((string) ($index + 1)) ?></div>
                            <div class="admin-ranking-copy">
                                <strong><?= esc($product['name']) ?></strong>
                                <p><?= esc((string) $product['requests']) ?> request lines recorded</p>
                            </div>
                            <div class="admin-ranking-value"><?= esc((string) $product['quantity']) ?></div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <section class="admin-workbench">
        <section class="admin-panel admin-panel--primary admin-catalog-panel">
            <div class="admin-catalog-hero">
                <div class="admin-catalog-hero-copy">
                    <span class="admin-kicker admin-kicker--dark">Catalogue Control</span>
                    <h2>Inventory workspace for live e-shop listings</h2>
                    <p>Filter the catalogue, review listing readiness, and move directly into update actions without leaving the dashboard.</p>
                </div>
                <div class="admin-catalog-actions">
                    <a href="/admin/products/template" class="btn btn-outline">Template CSV</a>
                    <a href="/admin/products/export" class="btn btn-outline">Export Products</a>
                </div>
            </div>

            <div class="admin-catalog-strip">
                <article class="admin-catalog-strip-card">
                    <span>Visible</span>
                    <strong><?= esc((string) $stats['activeProducts']) ?></strong>
                    <p>Active storefront listings.</p>
                </article>
                <article class="admin-catalog-strip-card">
                    <span>Hidden</span>
                    <strong><?= esc((string) $stats['hiddenProducts']) ?></strong>
                    <p>Listings kept out of view.</p>
                </article>
                <article class="admin-catalog-strip-card">
                    <span>Categories</span>
                    <strong><?= esc((string) count($categories)) ?></strong>
                    <p>Groups currently in catalogue.</p>
                </article>
                <article class="admin-catalog-strip-card">
                    <span>Current Result Set</span>
                    <strong><?= esc((string) count($products)) ?></strong>
                    <p>Products returned by current filters.</p>
                </article>
            </div>

            <div class="admin-catalog-filter-shell">
                <div class="admin-catalog-filter-head">
                    <div>
                        <h3>Refine Listing View</h3>
                        <p>Search by identifier, filter by visibility, and isolate a category before making changes.</p>
                    </div>
                </div>
                <form method="get" action="/admin" class="admin-filters admin-filters--catalog">
                    <input type="search" name="q" value="<?= esc($productSearch) ?>" placeholder="Search by name, SKU, or category">
                    <select name="status">
                        <option value="">All statuses</option>
                        <option value="active" <?= $activeStatus === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="hidden" <?= $activeStatus === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                    </select>
                    <select name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category) ?>" <?= $activeCategory === $category ? 'selected' : '' ?>><?= esc($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-dark">Apply</button>
                    <a href="/admin" class="btn btn-outline">Reset</a>
                </form>
            </div>

            <div class="admin-catalog-table-head">
                <div>
                    <h3>Listing Results</h3>
                    <p>Product master records ordered for quick review and editing.</p>
                </div>
                <span class="admin-table-count"><?= esc((string) count($products)) ?> items</span>
            </div>

            <?php if ($products === []): ?>
                <div class="empty-state empty-state--compact">
                    <h3>No products found.</h3>
                    <p>Seed or create products to populate the e-shop.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-shell admin-table-shell--catalog">
                    <table class="admin-table admin-table--catalog">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="admin-product-cell">
                                        <strong><?= esc($product['name']) ?></strong>
                                        <div class="admin-row-meta"><?= esc($product['slug']) ?></div>
                                    </div>
                                </td>
                                <td><span class="admin-inline-pill"><?= esc($product['category']) ?></span></td>
                                <td><?= esc($product['sku'] ?: 'Not set') ?></td>
                                <td><?= esc((string) $product['sort_order']) ?></td>
                                <td>
                                    <span class="admin-badge <?= (int) $product['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                        <?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td class="admin-actions">
                                    <a href="/admin/products/<?= esc((string) $product['id']) ?>/edit">Edit</a>
                                    <form method="post" action="/admin/products/<?= esc((string) $product['id']) ?>/delete">
                                        <?= csrf_field() ?>
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <div class="admin-rail">
            <section class="admin-panel admin-panel--soft">
                <div class="admin-panel-head">
                    <div>
                        <h2>Catalogue Readiness</h2>
                        <p>Operational checks that matter for a business-facing storefront.</p>
                    </div>
                </div>
                <div class="admin-insight-list">
                    <article class="admin-insight-item">
                        <span>Active Coverage</span>
                        <strong><?= esc((string) $activeCoverage) ?>%</strong>
                        <p>Products currently visible in the e-shop.</p>
                    </article>
                    <article class="admin-insight-item">
                        <span>Missing SKU</span>
                        <strong><?= esc((string) $catalogAudit['missingSku']) ?></strong>
                        <p>Listings without product codes are harder to process operationally.</p>
                    </article>
                    <article class="admin-insight-item">
                        <span>Missing Image</span>
                        <strong><?= esc((string) $catalogAudit['missingImage']) ?></strong>
                        <p>Products without imagery reduce credibility and buyer confidence.</p>
                    </article>
                    <article class="admin-insight-item">
                        <span>Described Listings</span>
                        <strong><?= esc((string) $catalogAudit['withDescriptions']) ?></strong>
                        <p>Products carrying either short or long descriptive content.</p>
                    </article>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Category Mix</h2>
                        <p>Top catalogue groups by product count.</p>
                    </div>
                </div>
                <div class="admin-progress-list">
                    <?php foreach ($categoryBreakdown as $item): ?>
                        <?php $percent = $stats['totalProducts'] > 0 ? (int) round(($item['count'] / $stats['totalProducts']) * 100) : 0; ?>
                        <div class="admin-progress-item">
                            <div class="admin-progress-head">
                                <strong><?= esc($item['category']) ?></strong>
                                <span><?= esc((string) $item['count']) ?> products</span>
                            </div>
                            <div class="admin-progress-track">
                                <div style="width: <?= esc((string) $percent) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Lead Intake Mix</h2>
                        <p>Where demand is entering the sales workflow.</p>
                    </div>
                </div>
                <div class="admin-quick-mix">
                    <div><span>Product RFQ</span><strong><?= esc((string) $quoteBreakdown['product']) ?></strong></div>
                    <div><span>Cart RFQ</span><strong><?= esc((string) $quoteBreakdown['cart']) ?></strong></div>
                    <div><span>Support</span><strong><?= esc((string) $quoteBreakdown['support']) ?></strong></div>
                    <?php if (($quoteBreakdown['other'] ?? 0) > 0): ?>
                        <div><span>Other</span><strong><?= esc((string) $quoteBreakdown['other']) ?></strong></div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </section>

    <section class="admin-grid-main admin-grid-main--equal">
        <section class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Recent Quote Requests</h2>
                    <p>Latest product, cart, and support enquiries entering the sales pipeline.</p>
                </div>
            </div>
            <div class="admin-card-list">
                <?php if ($quotes === []): ?>
                    <p>No quote requests captured yet.</p>
                <?php else: ?>
                    <?php foreach ($quotes as $quote): ?>
                        <article class="admin-list-card">
                            <div class="admin-list-card-head">
                                <div>
                                    <strong><?= esc($quote['name']) ?></strong>
                                    <div class="admin-row-meta"><?= esc($quote['company'] ?: 'No company provided') ?></div>
                                </div>
                                <span class="admin-badge"><?= esc(ucfirst($quote['request_type'])) ?></span>
                            </div>
                            <div class="admin-chip-row">
                                <span><?= esc($quote['phone']) ?></span>
                                <span><?= esc($quote['email'] ?: 'No email') ?></span>
                            </div>
                            <p><?= esc($quote['concern'] ?: $quote['source_page']) ?></p>
                            <?php $items = $quoteItemsByRequest[(int) $quote['id']] ?? []; ?>
                            <?php if ($items !== []): ?>
                                <div class="admin-mini-list">
                                    <?php foreach ($items as $item): ?>
                                        <div class="admin-mini-list-item">
                                            <span><?= esc($item['product_name']) ?></span>
                                            <strong><?= esc((string) $item['quantity']) ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Recent Brochure Leads</h2>
                    <p>Direct brochure capture activity from the public website.</p>
                </div>
            </div>
            <div class="admin-card-list">
                <?php if ($brochureLeads === []): ?>
                    <p>No brochure leads captured yet.</p>
                <?php else: ?>
                    <?php foreach ($brochureLeads as $lead): ?>
                        <article class="admin-list-card">
                            <div class="admin-list-card-head">
                                <strong><?= esc($lead['mobile']) ?></strong>
                                <span class="admin-badge admin-badge--muted"><?= esc($lead['source']) ?></span>
                            </div>
                            <div class="admin-row-meta"><?= esc($lead['ip_address'] ?: 'IP not available') ?></div>
                            <p><?= esc((string) $lead['created_at']) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </section>
<?php $this->endSection() ?>
