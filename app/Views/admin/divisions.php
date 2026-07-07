<?php
$pageTitle     = 'Divisions';
$activeNav     = 'divisions';
$mastheadLabel = 'Product Registry';
$mastheadTitle = 'Division management';
$mastheadText  = 'Divisions are a broader grouping above Category (e.g. Mining Equipment, Construction Equipment) and appear as their own storefront filter facet.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/categories" class="btn btn-outline">Categories</a>
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
        <span>Total Divisions</span>
        <strong><?= esc((string) count($divisions)) ?></strong>
        <p>Broad business groupings registered in the system.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Linked Categories</span>
        <strong><?= esc((string) array_sum($categoryCounts)) ?></strong>
        <p>Categories assigned to a division.</p>
    </article>
</div>

<section class="admin-workbench">

    <!-- ── Division list ── -->
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2>Division Registry</h2>
                <p>Each name here appears in the category form's parent division dropdown. Deleting a division detaches (does not delete) its categories.</p>
            </div>
        </div>

        <?php if ($divisions === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No divisions yet.</h3>
                <p>Use the form on the right to add your first division.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Categories</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($divisions as $division): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:var(--adm-navy)"><?= esc($division['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--adm-faint);font-family:monospace"><?= esc($division['slug']) ?></span>
                            </td>
                            <td>
                                <span class="admin-badge">
                                    <?= esc((string) ($categoryCounts[(int) $division['id']] ?? 0)) ?> categories
                                </span>
                            </td>
                            <td style="color:var(--adm-faint);font-size:13px"><?= esc((string) $division['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $division['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $division['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-div-btn"
                                            data-id="<?= esc((string) $division['id']) ?>"
                                            data-name="<?= esc($division['name']) ?>"
                                            data-sort="<?= esc((string) $division['sort_order']) ?>"
                                            data-desc="<?= esc($division['description'] ?? '') ?>"
                                            data-active="<?= esc((string) $division['is_active']) ?>"
                                            style="background:var(--adm-bg);border:1.5px solid var(--adm-border);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--adm-navy);cursor:pointer;transition:all .15s">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/divisions/<?= esc((string) $division['id']) ?>/delete"
                                          data-confirm="Delete division &quot;<?= esc($division['name']) ?>&quot;? Categories using it will be detached."
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
        <section class="admin-panel" id="div-form-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="div-form-title">Add Division</h2>
                    <p id="div-form-desc">New divisions are immediately available in the category form dropdown.</p>
                </div>
            </div>

            <form method="post" action="/admin/divisions" id="add-div-form" class="admin-form-grid" style="grid-template-columns:1fr">
                <?= csrf_field() ?>
                <input type="hidden" name="_edit_id" id="div-edit-id" value="">

                <div class="form-group">
                    <label for="div-name">Name <span style="color:#e53e3e">*</span></label>
                    <input id="div-name" type="text" name="name" placeholder="e.g. Mining Equipment" required>
                </div>
                <div class="form-group">
                    <label for="div-desc">Description</label>
                    <textarea id="div-desc" name="description" style="min-height:70px" placeholder="Optional short description for internal use."></textarea>
                </div>
                <div class="form-group">
                    <label for="div-sort">Sort Order</label>
                    <input id="div-sort" type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="div-active">Status</label>
                    <select id="div-active" name="is_active">
                        <option value="1">&#128994; Active</option>
                        <option value="0">&#9899; Hidden</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="div-submit-btn">&#43; Save Division</button>
                    <button type="button" id="div-reset-btn" class="btn btn-outline" style="display:none">Cancel</button>
                </div>
            </form>
        </section>
    </div>

</section>

<script>
(function () {
    document.querySelectorAll('.edit-div-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var name   = this.dataset.name;
            var sort   = this.dataset.sort;
            var desc   = this.dataset.desc;
            var active = this.dataset.active;

            document.getElementById('div-form-title').textContent = 'Edit Division';
            document.getElementById('div-form-desc').textContent  = 'Updating this division applies immediately to the storefront filter.';
            document.getElementById('div-name').value    = name;
            document.getElementById('div-desc').value    = desc;
            document.getElementById('div-sort').value    = sort;
            document.getElementById('div-active').value  = active;
            document.getElementById('div-edit-id').value = id;
            document.getElementById('div-submit-btn').textContent = '✓ Update Division';
            document.getElementById('div-reset-btn').style.display = 'inline-flex';

            var form = document.getElementById('add-div-form');
            form.action = '/admin/divisions/' + id;

            document.getElementById('div-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.getElementById('div-reset-btn').addEventListener('click', function () {
        var form = document.getElementById('add-div-form');
        form.reset();
        form.action = '/admin/divisions';
        document.getElementById('div-edit-id').value = '';
        document.getElementById('div-form-title').textContent = 'Add Division';
        document.getElementById('div-form-desc').textContent  = 'New divisions are immediately available in the category form dropdown.';
        document.getElementById('div-submit-btn').textContent = '+ Save Division';
        this.style.display = 'none';
    });
})();
</script>

<?php $this->endSection() ?>
