<?php
$pageTitle     = 'Categories';
$activeNav     = 'categories';
$mastheadLabel = 'Product Registry';
$mastheadTitle = 'Category management';
$mastheadText  = 'All category names are bound here. Products pick from this list — no free-text typing, no spelling drift.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/products/new" class="btn">Add Product</a>
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
<div class="admin-summary-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="admin-summary-card">
        <span>Total Categories</span>
        <strong><?= esc((string) count($categories)) ?></strong>
        <p>Active product groups registered in the system.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Bound Products</span>
        <strong><?= esc((string) array_sum($productCounts)) ?></strong>
        <p>Products linked to a category via category_id FK.</p>
    </article>
    <article class="admin-summary-card">
        <span>Unbound Products</span>
        <strong id="unbound-count">—</strong>
        <p>Products without a category_id (legacy or bulk-imported rows).</p>
    </article>
</div>

<section class="admin-workbench">

    <!-- ── Category list ── -->
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2>Category Registry</h2>
                <p>Each name here appears in the product form dropdown. Deleting a category detaches (does not delete) its products.</p>
            </div>
        </div>

        <?php if ($categories === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No categories yet.</h3>
                <p>Use the form on the right to add your first category.</p>
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
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:var(--adm-navy)"><?= esc($cat['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--adm-faint);font-family:monospace"><?= esc($cat['slug']) ?></span>
                            </td>
                            <td>
                                <span class="admin-badge">
                                    <?= esc((string) ($productCounts[(int) $cat['id']] ?? 0)) ?> products
                                </span>
                            </td>
                            <td style="color:var(--adm-faint);font-size:13px"><?= esc((string) $cat['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $cat['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $cat['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-cat-btn"
                                            data-id="<?= esc((string) $cat['id']) ?>"
                                            data-name="<?= esc($cat['name']) ?>"
                                            data-sort="<?= esc((string) $cat['sort_order']) ?>"
                                            data-desc="<?= esc($cat['description'] ?? '') ?>"
                                            data-active="<?= esc((string) $cat['is_active']) ?>"
                                            style="background:var(--adm-bg);border:1.5px solid var(--adm-border);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--adm-navy);cursor:pointer;transition:all .15s">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/categories/<?= esc((string) $cat['id']) ?>/delete"
                                          onsubmit="return confirm('Delete category \'<?= esc(addslashes($cat['name'])) ?>\'? Products using it will be detached.')">
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
        <section class="admin-panel" id="cat-form-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="cat-form-title">Add Category</h2>
                    <p id="cat-form-desc">New categories are immediately available in the product form dropdown.</p>
                </div>
            </div>

            <!-- Add new form (default) -->
            <form method="post" action="/admin/categories" id="add-cat-form" class="admin-form-grid" style="grid-template-columns:1fr">
                <?= csrf_field() ?>
                <input type="hidden" name="_edit_id" id="edit-id" value="">

                <div class="form-group">
                    <label for="cat-name">Name <span style="color:#e53e3e">*</span></label>
                    <input id="cat-name" type="text" name="name" placeholder="e.g. Hydraulic Systems" required>
                </div>
                <div class="form-group">
                    <label for="cat-desc">Description</label>
                    <textarea id="cat-desc" name="description" style="min-height:70px" placeholder="Optional short description for internal use."></textarea>
                </div>
                <div class="form-group">
                    <label for="cat-sort">Sort Order</label>
                    <input id="cat-sort" type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="cat-active">Status</label>
                    <select id="cat-active" name="is_active">
                        <option value="1">&#128994; Active</option>
                        <option value="0">&#9899; Hidden</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="cat-submit-btn">&#43; Save Category</button>
                    <button type="button" id="cat-reset-btn" class="btn btn-outline" style="display:none">Cancel</button>
                </div>
            </form>
        </section>

        <section class="admin-panel admin-panel--soft">
            <div class="admin-panel-head" style="border-bottom:none;margin-bottom:0;padding-bottom:0">
                <div>
                    <h2>Naming tips</h2>
                </div>
            </div>
            <div class="admin-column-list" style="margin-top:14px">
                <div class="admin-column-item">
                    <strong>Be specific</strong>
                    <span>"Hydraulic Seals" is more useful than "Parts".</span>
                </div>
                <div class="admin-column-item">
                    <strong>Match the SKU prefix</strong>
                    <span>First 3 letters drive the auto-SKU code — keep names distinct.</span>
                </div>
                <div class="admin-column-item">
                    <strong>Title Case</strong>
                    <span>Use "Spare Parts" not "spare parts" for consistent display.</span>
                </div>
                <div class="admin-column-item">
                    <strong>Bulk import</strong>
                    <span>CSV rows that use a matching category name are auto-bound on import.</span>
                </div>
            </div>
        </section>
    </div>

</section>

<script>
(function () {
    // Edit button fills the sidebar form
    document.querySelectorAll('.edit-cat-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var name   = this.dataset.name;
            var sort   = this.dataset.sort;
            var desc   = this.dataset.desc;
            var active = this.dataset.active;

            document.getElementById('cat-form-title').textContent = 'Edit Category';
            document.getElementById('cat-form-desc').textContent  = 'Updating the name here re-syncs all products on next save.';
            document.getElementById('cat-name').value    = name;
            document.getElementById('cat-desc').value    = desc;
            document.getElementById('cat-sort').value    = sort;
            document.getElementById('cat-active').value  = active;
            document.getElementById('edit-id').value     = id;
            document.getElementById('cat-submit-btn').textContent = '✓ Update Category';
            document.getElementById('cat-reset-btn').style.display = 'inline-flex';

            // Change form action to update endpoint (POST /admin/categories/{id})
            var form = document.getElementById('add-cat-form');
            form.action = '/admin/categories/' + id;

            document.getElementById('cat-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.getElementById('cat-reset-btn').addEventListener('click', function () {
        var form = document.getElementById('add-cat-form');
        form.reset();
        form.action = '/admin/categories';
        document.getElementById('edit-id').value = '';
        document.getElementById('cat-form-title').textContent = 'Add Category';
        document.getElementById('cat-form-desc').textContent  = 'New categories are immediately available in the product form dropdown.';
        document.getElementById('cat-submit-btn').textContent = '+ Save Category';
        this.style.display = 'none';
    });
})();
</script>

<?php $this->endSection() ?>
