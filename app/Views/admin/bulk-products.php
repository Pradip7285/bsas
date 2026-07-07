<?php
$pageTitle = 'Bulk Listing';
$activeNav = 'bulk';
$mastheadLabel = 'Bulk Listing';
$mastheadTitle = 'Spreadsheet-based product update';
$mastheadText = 'Run repeatable catalogue imports and exports without row-by-row manual editing.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/products/template" class="btn btn-outline">Download Template</a>
    <a href="/admin/products/export" class="btn btn-outline">Export Products</a>
    <a href="/admin" class="btn btn-dark">Back to Admin</a>
<?php $this->endSection() ?>

<?= $this->section('beforeContent') ?>
    <?php if (! empty($errors)): ?>
        <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <?php if (! empty($importSummary)): ?>
        <section class="admin-panel admin-panel--soft">
            <div class="admin-panel-head">
                <div>
                    <h2>Latest Import Summary</h2>
                    <p>Use this report to verify what changed before exporting or reviewing products.</p>
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
    <section class="admin-workbench">
        <section class="admin-panel admin-panel--primary">
            <div class="admin-panel-head">
                <div>
                    <h2>Upload Spreadsheet</h2>
                    <p>Import products from a CSV or XLSX file. Existing products are updated by SKU first, then slug, then name.</p>
                </div>
            </div>
            <form method="post" action="/admin/products/bulk" enctype="multipart/form-data" class="admin-upload-form">
                <?= csrf_field() ?>
                <div class="form-group form-full">
                    <label for="spreadsheet">Spreadsheet File</label>
                    <input id="spreadsheet" type="file" name="spreadsheet" accept=".csv,.xlsx" required>
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Process Import</button>
                </div>
            </form>
        </section>

        <div class="admin-rail">
            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Expected Columns</h2>
                        <p>The header row must match the import template.</p>
                    </div>
                </div>
                <div class="admin-column-list">
                    <div class="admin-column-item"><strong>Required</strong><span>`name`, `category`</span></div>
                    <div class="admin-column-item"><strong>Recommended</strong><span>`sku`, `slug`, `short_description`, `description`</span></div>
                    <div class="admin-column-item"><strong>Optional</strong><span>`image_url`, `price_label`, `sort_order`, `is_active`</span></div>
                    <div class="admin-column-item"><strong>Compatibility &amp; tags</strong><span>`vehicles`, `labels` &mdash; comma-separated names, e.g. "MPR100, HP-500 Series Pump".</span></div>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-head">
                    <div>
                        <h2>Update Rules</h2>
                        <p>Keep identifiers stable so imports remain safe and predictable.</p>
                    </div>
                </div>
                <div class="admin-column-list">
                    <div class="admin-column-item"><strong>Match order</strong><span>SKU, then slug, then product name.</span></div>
                    <div class="admin-column-item"><strong>New rows</strong><span>If no match is found, a new product record is created.</span></div>
                    <div class="admin-column-item"><strong>Status values</strong><span>Use `1` for active and `0` for hidden.</span></div>
                    <div class="admin-column-item"><strong>Vehicles</strong><span>Matched against existing Vehicles by name; unknown names are skipped (Vehicles need an OEM, so none are auto-created here).</span></div>
                    <div class="admin-column-item"><strong>Labels</strong><span>Matched by name, and auto-created if a label doesn't exist yet.</span></div>
                </div>
            </section>
        </div>
    </section>
<?php $this->endSection() ?>
