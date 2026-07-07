<?php
$pageTitle     = 'Labels';
$activeNav     = 'labels';
$mastheadLabel = 'Product Registry';
$mastheadTitle = 'Label management';
$mastheadText  = 'Free-form merchandising tags (e.g. New Arrival, Best Seller, Clearance) assignable to any number of products, and filterable on the storefront.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/products" class="btn btn-outline">Products</a>
    <a href="/admin" class="btn btn-dark">&#8592; Dashboard</a>
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

<!-- ── Summary strip ── -->
<div class="admin-summary-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
    <article class="admin-summary-card">
        <span>Total Labels</span>
        <strong><?= esc((string) count($labels)) ?></strong>
        <p>Merchandising tags registered in the system.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Linked Products</span>
        <strong><?= esc((string) array_sum($productCounts)) ?></strong>
        <p>Product &harr; label links across the catalogue.</p>
    </article>
</div>

<section class="admin-workbench">

    <!-- ── Label list ── -->
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2>Label Registry</h2>
                <p>Each label appears in the product form's label checklist and as a storefront filter facet.</p>
            </div>
        </div>

        <?php if ($labels === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No labels yet.</h3>
                <p>Use the form on the right to add your first label.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($labels as $label): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:var(--adm-navy)"><?= esc($label['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--adm-faint);font-family:monospace"><?= esc($label['slug']) ?></span>
                            </td>
                            <td>
                                <span class="admin-badge">
                                    <?= esc((string) ($productCounts[(int) $label['id']] ?? 0)) ?> products
                                </span>
                            </td>
                            <td style="color:var(--adm-faint);font-size:13px"><?= esc((string) $label['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $label['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $label['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-label-btn"
                                            data-id="<?= esc((string) $label['id']) ?>"
                                            data-name="<?= esc($label['name']) ?>"
                                            data-sort="<?= esc((string) $label['sort_order']) ?>"
                                            data-desc="<?= esc($label['description'] ?? '') ?>"
                                            data-active="<?= esc((string) $label['is_active']) ?>"
                                            style="background:var(--adm-bg);border:1.5px solid var(--adm-border);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--adm-navy);cursor:pointer;transition:all .15s">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/labels/<?= esc((string) $label['id']) ?>/delete"
                                          data-confirm="Delete label &quot;<?= esc($label['name']) ?>&quot;? Product links will be removed."
                                          onsubmit="return confirm(this.dataset.confirm)">
                                        <?= csrf_field() ?>
                                        <button type="submit" style="font-size:12px;font-weight:700;color:#dc2626;padding:5px 12px;border:1.5px solid #fca5a5;border-radius:8px;background:#fff0f0;cursor:pointer;font-family:inherit;transition:all .15s">
                                            Delete
                                        </button>
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

    <!-- ── Sidebar: Add / Edit form ── -->
    <div class="admin-rail">
        <section class="admin-panel" id="label-form-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="label-form-title">Add Label</h2>
                    <p id="label-form-desc">New labels are immediately available in the product form's checklist.</p>
                </div>
            </div>

            <form method="post" action="/admin/labels" id="add-label-form" class="admin-form-grid" style="grid-template-columns:1fr">
                <?= csrf_field() ?>
                <input type="hidden" name="_edit_id" id="label-edit-id" value="">

                <div class="form-group">
                    <label for="label-name">Name <span style="color:#e53e3e">*</span></label>
                    <input id="label-name" type="text" name="name" placeholder="e.g. Best Seller" required>
                </div>
                <div class="form-group">
                    <label for="label-desc">Description</label>
                    <textarea id="label-desc" name="description" style="min-height:70px" placeholder="Optional short description for internal use."></textarea>
                </div>
                <div class="form-group">
                    <label for="label-sort">Sort Order</label>
                    <input id="label-sort" type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="label-active">Status</label>
                    <select id="label-active" name="is_active">
                        <option value="1">&#128994; Active</option>
                        <option value="0">&#9899; Hidden</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="label-submit-btn">&#43; Save Label</button>
                    <button type="button" id="label-reset-btn" class="btn btn-outline" style="display:none">Cancel</button>
                </div>
            </form>
        </section>
    </div>

</section>

<script>
(function () {
    document.querySelectorAll('.edit-label-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var name   = this.dataset.name;
            var sort   = this.dataset.sort;
            var desc   = this.dataset.desc;
            var active = this.dataset.active;

            document.getElementById('label-form-title').textContent = 'Edit Label';
            document.getElementById('label-form-desc').textContent  = 'Updating this label applies immediately to the storefront filter.';
            document.getElementById('label-name').value    = name;
            document.getElementById('label-desc').value    = desc;
            document.getElementById('label-sort').value    = sort;
            document.getElementById('label-active').value  = active;
            document.getElementById('label-edit-id').value = id;
            document.getElementById('label-submit-btn').textContent = '✓ Update Label';
            document.getElementById('label-reset-btn').style.display = 'inline-flex';

            var form = document.getElementById('add-label-form');
            form.action = '/admin/labels/' + id;

            document.getElementById('label-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.getElementById('label-reset-btn').addEventListener('click', function () {
        var form = document.getElementById('add-label-form');
        form.reset();
        form.action = '/admin/labels';
        document.getElementById('label-edit-id').value = '';
        document.getElementById('label-form-title').textContent = 'Add Label';
        document.getElementById('label-form-desc').textContent  = "New labels are immediately available in the product form's checklist.";
        document.getElementById('label-submit-btn').textContent = '+ Save Label';
        this.style.display = 'none';
    });
})();
</script>

<?php $this->endSection() ?>
