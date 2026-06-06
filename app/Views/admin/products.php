<?php
$pageTitle     = 'Products';
$activeNav     = 'products';
$mastheadLabel = 'Catalogue Management';
$mastheadTitle = 'All Products';
$mastheadText  = 'Create, edit, and manage every product in the e-shop catalogue from one place.';
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
               placeholder="Search by name, SKU, category, or description…">

        <div style="position:relative">
            <select name="category" style="padding-right:36px">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc($cat['name']) ?>"
                        <?= $activeCategory === $cat['name'] ? 'selected' : '' ?>>
                        <?= esc($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="position:relative">
            <select name="status" style="padding-right:36px">
                <option value="">All statuses</option>
                <option value="active"  <?= $activeStatus === 'active'  ? 'selected' : '' ?>>Active</option>
                <option value="hidden"  <?= $activeStatus === 'hidden'  ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>

        <button type="submit" class="btn btn-dark">Apply</button>
        <a href="/admin/products" class="btn btn-outline">Reset</a>
    </form>
</section>

<!-- ── Product table ── -->
<section class="admin-panel" style="padding:0;overflow:hidden">

    <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid var(--adm-border-l)">
        <div>
            <h2 style="font-size:16px;font-weight:800;color:var(--adm-navy);margin:0 0 3px">
                Listing Results
            </h2>
            <p style="font-size:12px;color:var(--adm-faint);margin:0">
                <?php if ($productSearch !== '' || $activeCategory !== '' || $activeStatus !== ''): ?>
                    Filtered view &mdash;
                    <?= esc((string) count($products)) ?> product<?= count($products) !== 1 ? 's' : '' ?> matched.
                <?php else: ?>
                    All <?= esc((string) count($products)) ?> products in the catalogue.
                <?php endif; ?>
            </p>
        </div>
        <span class="admin-table-count"><?= esc((string) count($products)) ?> items</span>
    </div>

    <?php if ($products === []): ?>
        <div class="empty-state" style="border:none;border-radius:0;background:var(--adm-white)">
            <div style="font-size:48px;margin-bottom:16px;opacity:.4">&#128230;</div>
            <h3>No products found.</h3>
            <p><?= $productSearch !== '' || $activeCategory !== '' || $activeStatus !== ''
                ? 'No products matched your filters. Try broadening the search.'
                : 'The catalogue is empty. Add your first product to get started.' ?></p>
            <?php if ($productSearch !== '' || $activeCategory !== '' || $activeStatus !== ''): ?>
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
                        <th style="width:36px"></th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <!-- Thumbnail -->
                        <td style="padding:10px 8px 10px 16px">
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

                        <!-- Sort -->
                        <td style="color:var(--adm-faint);font-size:13px;text-align:center">
                            <?= esc((string) $product['sort_order']) ?>
                        </td>

                        <!-- Status badge -->
                        <td>
                            <span class="admin-badge <?= (int) $product['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                <?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                            </span>
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
    <?php endif; ?>
</section>

<?php $this->endSection() ?>
