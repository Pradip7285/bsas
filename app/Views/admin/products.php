<?php
$pageTitle     = 'Products';
$activeNav     = 'products';
$mastheadLabel = 'Catalogue Management';
$mastheadTitle = 'All Products';
$mastheadText  = 'Create, edit, and manage every product in the e-shop catalogue from one place.';

$stockLabels = [
    'in_stock'      => 'In Stock',
    'made_to_order' => 'Made to Order',
    'out_of_stock'  => 'Out of Stock',
];

/** Builds a query string preserving current filters, overriding the given params. */
$buildQuery = static function (array $overrides) use ($productSearch, $activeCategory, $activeStatus, $activeStock, $activeFeatured, $activeSort, $activeDir, $page) {
    $params = array_filter([
        'q'        => $productSearch,
        'category' => $activeCategory,
        'status'   => $activeStatus,
        'stock'    => $activeStock,
        'featured' => $activeFeatured,
        'sort'     => $activeSort,
        'dir'      => $activeDir,
        'page'     => $page,
    ], static fn($v): bool => $v !== '' && $v !== null);

    return '/admin/products?' . http_build_query(array_merge($params, $overrides));
};

$sortLink = static function (string $column, string $label) use ($buildQuery, $activeSort, $activeDir): string {
    $nextDir = ($activeSort === $column && $activeDir === 'asc') ? 'desc' : 'asc';
    $arrow   = $activeSort === $column ? ($activeDir === 'asc' ? ' &#8593;' : ' &#8595;') : '';
    $url     = $buildQuery(['sort' => $column, 'dir' => $nextDir, 'page' => null]);

    return '<a href="' . esc($url) . '" style="color:inherit;text-decoration:none">' . esc($label) . $arrow . '</a>';
};
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/products/new" class="btn">&#43; Add Product</a>
    <a href="/admin/products/bulk" class="btn btn-outline">&#8679; Bulk Upload</a>
    <a href="/admin/products/export" class="btn btn-outline">&#8681; Export CSV</a>
<?php $this->endSection() ?>

<?= $this->section('beforeContent') ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="success-banner"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (! empty($errors)): ?>
        <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="error-banner"><?= esc(implode(' ', session()->getFlashdata('errors'))) ?></div>
    <?php endif; ?>
<?php $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Stat strip ── -->
<div class="admin-summary-grid" style="grid-template-columns:repeat(5,minmax(0,1fr))">
    <article class="admin-summary-card">
        <span>Total</span>
        <strong><?= esc((string) $stats['totalProducts']) ?></strong>
        <p>Catalogue records managed.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Active</span>
        <strong><?= esc((string) $stats['activeProducts']) ?></strong>
        <p>Visible in the e-shop.</p>
    </article>
    <article class="admin-summary-card">
        <span>Hidden</span>
        <strong><?= esc((string) $stats['hiddenProducts']) ?></strong>
        <p>Not visible to buyers.</p>
    </article>
    <article class="admin-summary-card">
        <span>No SKU</span>
        <strong><?= esc((string) $catalogAudit['missingSku']) ?></strong>
        <p>Products without a code.</p>
    </article>
    <article class="admin-summary-card">
        <span>No Image</span>
        <strong><?= esc((string) $catalogAudit['missingImage']) ?></strong>
        <p>Products without artwork.</p>
    </article>
</div>

<!-- ── Filter / search ── -->
<section class="admin-panel" style="padding:18px 22px">
    <form method="get" action="/admin/products" class="admin-filters" style="margin-bottom:0">
        <input type="search" name="q"
               value="<?= esc($productSearch) ?>"
               placeholder="Search by name, SKU, part no., category, or meta title…">

        <select name="category" style="padding-right:36px">
            <option value="">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= esc($cat['name']) ?>"
                    <?= $activeCategory === $cat['name'] ? 'selected' : '' ?>>
                    <?= esc($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" style="padding-right:36px">
            <option value="">All statuses</option>
            <option value="active"  <?= $activeStatus === 'active'  ? 'selected' : '' ?>>Active</option>
            <option value="hidden"  <?= $activeStatus === 'hidden'  ? 'selected' : '' ?>>Hidden</option>
        </select>

        <select name="stock" style="padding-right:36px">
            <option value="">All stock</option>
            <?php foreach ($stockLabels as $val => $label): ?>
                <option value="<?= esc($val) ?>" <?= $activeStock === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="featured" style="padding-right:36px">
            <option value="">Featured &amp; non-featured</option>
            <option value="1" <?= $activeFeatured === '1' ? 'selected' : '' ?>>Featured only</option>
            <option value="0" <?= $activeFeatured === '0' ? 'selected' : '' ?>>Non-featured only</option>
        </select>

        <button type="submit" class="btn btn-dark">Apply</button>
        <a href="/admin/products" class="btn btn-outline">Reset</a>
    </form>
</section>

<!-- ── Bulk action bar ── -->
<!-- Empty form — the actual controls (select/checkboxes/button) live outside it in the
     DOM and reference it via form="bulk-action-form", so it never wraps the per-row
     delete <form> elements below (nested <form> tags are invalid HTML). -->
<form method="post" action="/admin/products/bulk-action" id="bulk-action-form">
    <?= csrf_field() ?>
</form>

<section class="admin-panel adm-bulk-bar" style="padding:12px 22px;display:flex;align-items:center;gap:10px;margin-top:16px">
    <span style="font-size:12px;color:var(--adm-faint)"><span id="bulk-selected-count">0</span> selected</span>
    <select name="bulk_action" id="bulk-action-select" form="bulk-action-form" style="max-width:220px">
        <option value="activate">Activate</option>
        <option value="deactivate">Hide</option>
        <option value="delete">Delete</option>
    </select>
    <button type="submit" class="btn btn-outline" id="bulk-apply-btn" form="bulk-action-form" disabled>Apply to Selected</button>
</section>

<!-- ── Product table ── -->
<section class="admin-panel" style="padding:0;overflow:hidden;margin-top:16px">

    <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid var(--adm-border-l)">
        <div>
            <h2 style="font-size:16px;font-weight:800;color:var(--adm-navy);margin:0 0 3px">
                Listing Results
            </h2>
            <p style="font-size:12px;color:var(--adm-faint);margin:0">
                <?php if ($productSearch !== '' || $activeCategory !== '' || $activeStatus !== '' || $activeStock !== '' || $activeFeatured !== ''): ?>
                    Filtered view &mdash;
                    <?= esc((string) $resultCount) ?> product<?= $resultCount !== 1 ? 's' : '' ?> matched.
                <?php else: ?>
                    All <?= esc((string) $resultCount) ?> products in the catalogue.
                <?php endif; ?>
            </p>
        </div>
        <span class="admin-table-count">Page <?= esc((string) $page) ?> of <?= esc((string) $pageCount) ?></span>
    </div>

    <?php if ($products === []): ?>
        <div class="empty-state" style="border:none;border-radius:0;background:var(--adm-white)">
            <div style="font-size:48px;margin-bottom:16px;opacity:.4">&#128230;</div>
            <h3>No products found.</h3>
            <p><?= $productSearch !== '' || $activeCategory !== '' || $activeStatus !== '' || $activeStock !== '' || $activeFeatured !== ''
                ? 'No products matched your filters. Try broadening the search.'
                : 'The catalogue is empty. Add your first product to get started.' ?></p>
            <?php if ($productSearch !== '' || $activeCategory !== '' || $activeStatus !== '' || $activeStock !== '' || $activeFeatured !== ''): ?>
                <a href="/admin/products" class="btn btn-outline">Clear Filters</a>
            <?php else: ?>
                <a href="/admin/products/new" class="btn">&#43; Add First Product</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="admin-table-shell" style="border:none;border-radius:0">
            <table class="admin-table" style="border-radius:0">
                <thead>
                    <tr>
                        <th style="width:32px"><input type="checkbox" id="bulk-select-all"></th>
                        <th style="width:36px"></th>
                        <th><?= $sortLink('name', 'Product') ?></th>
                        <th><?= $sortLink('category', 'Category') ?></th>
                        <th><?= $sortLink('sku', 'SKU') ?></th>
                        <th><?= $sortLink('price', 'Price') ?></th>
                        <th><?= $sortLink('stock_quantity', 'Stock') ?></th>
                        <th><?= $sortLink('sort_order', 'Sort') ?></th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <!-- Bulk checkbox -->
                        <td style="padding:10px 8px 10px 16px">
                            <input type="checkbox" name="product_ids[]" value="<?= esc((string) $product['id']) ?>" class="bulk-row-checkbox" form="bulk-action-form">
                        </td>

                        <!-- Thumbnail -->
                        <td style="padding:10px 8px">
                            <div style="
                                width:36px;height:36px;border-radius:8px;
                                background:url('<?= esc($product['image_url'] ?: '/assets/images/sparePart.webp') ?>') center/cover no-repeat var(--adm-bg);
                                border:1px solid var(--adm-border);flex-shrink:0">
                            </div>
                        </td>

                        <!-- Name + slug -->
                        <td>
                            <strong style="font-size:13px;font-weight:700;color:var(--adm-navy);display:block;white-space:nowrap">
                                <?= esc($product['name']) ?>
                            </strong>
                            <span style="font-size:11px;color:var(--adm-faint);font-family:monospace">
                                /<?= esc($product['slug']) ?>
                            </span>
                        </td>

                        <!-- Category -->
                        <td>
                            <?php if (! empty($product['category'])): ?>
                                <span class="admin-inline-pill"><?= esc($product['category']) ?></span>
                            <?php else: ?>
                                <span style="font-size:12px;color:var(--adm-faint)">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- SKU -->
                        <td>
                            <?php if (! empty($product['sku'])): ?>
                                <span style="font-size:12px;font-family:monospace;color:var(--adm-text-2);font-weight:600">
                                    <?= esc($product['sku']) ?>
                                </span>
                            <?php else: ?>
                                <span style="font-size:12px;color:var(--adm-faint)">No SKU</span>
                            <?php endif; ?>
                        </td>

                        <!-- Price -->
                        <td style="font-size:12px;color:var(--adm-text-2)">
                            <?php if ((float) ($product['price'] ?? 0) > 0): ?>
                                <?= esc($product['currency'] ?? 'INR') ?> <?= esc(number_format((float) $product['price'], 2)) ?>
                            <?php else: ?>
                                <span style="color:var(--adm-faint)">Quote only</span>
                            <?php endif; ?>
                        </td>

                        <!-- Stock -->
                        <td style="font-size:12px;color:var(--adm-text-2)">
                            <?= esc((string) ($product['stock_quantity'] ?? 0)) ?>
                            <span style="color:var(--adm-faint)">(<?= esc($stockLabels[$product['stock_status'] ?? 'in_stock'] ?? '') ?>)</span>
                        </td>

                        <!-- Sort -->
                        <td style="color:var(--adm-faint);font-size:13px;text-align:center">
                            <?= esc((string) $product['sort_order']) ?>
                        </td>

                        <!-- Status badge -->
                        <td>
                            <span class="admin-badge <?= (int) $product['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                <?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                            </span>
                            <?php if (! empty($product['is_featured'])): ?>
                                <span class="admin-badge">&#9733; Featured</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td style="text-align:right;white-space:nowrap">
                            <div class="admin-actions" style="justify-content:flex-end">
                                <a href="/admin/products/<?= esc((string) $product['id']) ?>/edit"
                                   style="display:inline-flex;align-items:center;gap:5px">
                                    &#9998; Edit
                                </a>

                                <form method="post"
                                      action="/admin/products/<?= esc((string) $product['id']) ?>/delete"
                                      onsubmit="return confirm('Delete \'<?= esc(addslashes($product['name'])) ?>\'? This cannot be undone.')">
                                    <?= csrf_field() ?>
                                    <button type="submit">&#10005; Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Pagination ── -->
        <?php if ($pageCount > 1): ?>
            <div class="adm-pagination" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:18px 22px;border-top:1px solid var(--adm-border-l)">
                <?php if ($page > 1): ?>
                    <a href="<?= esc($buildQuery(['page' => $page - 1])) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">&#8592; Prev</a>
                <?php endif; ?>
                <span style="font-size:12px;color:var(--adm-faint)">Page <?= esc((string) $page) ?> of <?= esc((string) $pageCount) ?></span>
                <?php if ($page < $pageCount): ?>
                    <a href="<?= esc($buildQuery(['page' => $page + 1])) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">Next &#8594;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<script>
(function () {
    var selectAll   = document.getElementById('bulk-select-all');
    var applyBtn    = document.getElementById('bulk-apply-btn');
    var countLabel  = document.getElementById('bulk-selected-count');
    var bulkForm    = document.getElementById('bulk-action-form');

    function rowCheckboxes() {
        return Array.prototype.slice.call(document.querySelectorAll('.bulk-row-checkbox'));
    }

    function updateState() {
        var checked = rowCheckboxes().filter(function (cb) { return cb.checked; });
        countLabel.textContent = checked.length;
        applyBtn.disabled = checked.length === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes().forEach(function (cb) { cb.checked = selectAll.checked; });
            updateState();
        });
    }

    rowCheckboxes().forEach(function (cb) {
        cb.addEventListener('change', updateState);
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            var action = document.getElementById('bulk-action-select').value;
            var checked = rowCheckboxes().filter(function (cb) { return cb.checked; });
            if (checked.length === 0) { e.preventDefault(); return; }
            if (action === 'delete' && !confirm('Delete ' + checked.length + ' selected product(s)? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    }

    updateState();
})();
</script>

<?php $this->endSection() ?>
