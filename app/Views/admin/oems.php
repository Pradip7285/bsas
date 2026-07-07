<?php
$pageTitle     = 'OEMs';
$activeNav     = 'oems';
$mastheadLabel = 'Product Registry';
$mastheadTitle = 'OEM management';
$mastheadText  = 'Equipment brands (OEMs) group vehicles in the storefront\'s Compatible Vehicle filter and the product form\'s compatibility checklist.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/vehicles" class="btn btn-outline">Vehicles</a>
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
        <span>Total OEMs</span>
        <strong><?= esc((string) count($oems)) ?></strong>
        <p>Equipment brands registered in the system.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Linked Vehicles</span>
        <strong><?= esc((string) array_sum($vehicleCounts)) ?></strong>
        <p>Vehicles assigned to an OEM.</p>
    </article>
</div>

<section class="admin-workbench">

    <!-- ── OEM list ── -->
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2>OEM Registry</h2>
                <p>Each name here appears in the vehicle form's parent OEM dropdown. Deleting an OEM detaches (does not delete) its vehicles.</p>
            </div>
        </div>

        <?php if ($oems === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No OEMs yet.</h3>
                <p>Use the form on the right to add your first OEM.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Vehicles</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($oems as $oem): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:var(--adm-navy)"><?= esc($oem['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--adm-faint);font-family:monospace"><?= esc($oem['slug']) ?></span>
                            </td>
                            <td>
                                <span class="admin-badge">
                                    <?= esc((string) ($vehicleCounts[(int) $oem['id']] ?? 0)) ?> vehicles
                                </span>
                            </td>
                            <td style="color:var(--adm-faint);font-size:13px"><?= esc((string) $oem['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $oem['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $oem['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-oem-btn"
                                            data-id="<?= esc((string) $oem['id']) ?>"
                                            data-name="<?= esc($oem['name']) ?>"
                                            data-sort="<?= esc((string) $oem['sort_order']) ?>"
                                            data-desc="<?= esc($oem['description'] ?? '') ?>"
                                            data-active="<?= esc((string) $oem['is_active']) ?>"
                                            style="background:var(--adm-bg);border:1.5px solid var(--adm-border);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--adm-navy);cursor:pointer;transition:all .15s">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/oems/<?= esc((string) $oem['id']) ?>/delete"
                                          data-confirm="Delete OEM &quot;<?= esc($oem['name']) ?>&quot;? Vehicles using it will be detached."
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
        <section class="admin-panel" id="oem-form-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="oem-form-title">Add OEM</h2>
                    <p id="oem-form-desc">New OEMs are immediately available in the vehicle form dropdown.</p>
                </div>
            </div>

            <form method="post" action="/admin/oems" id="add-oem-form" class="admin-form-grid" style="grid-template-columns:1fr">
                <?= csrf_field() ?>
                <input type="hidden" name="_edit_id" id="oem-edit-id" value="">

                <div class="form-group">
                    <label for="oem-name">Name <span style="color:#e53e3e">*</span></label>
                    <input id="oem-name" type="text" name="name" placeholder="e.g. Atlas Copco" required>
                </div>
                <div class="form-group">
                    <label for="oem-desc">Description</label>
                    <textarea id="oem-desc" name="description" style="min-height:70px" placeholder="Optional short description for internal use."></textarea>
                </div>
                <div class="form-group">
                    <label for="oem-sort">Sort Order</label>
                    <input id="oem-sort" type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="oem-active">Status</label>
                    <select id="oem-active" name="is_active">
                        <option value="1">&#128994; Active</option>
                        <option value="0">&#9899; Hidden</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="oem-submit-btn">&#43; Save OEM</button>
                    <button type="button" id="oem-reset-btn" class="btn btn-outline" style="display:none">Cancel</button>
                </div>
            </form>
        </section>
    </div>

</section>

<script>
(function () {
    document.querySelectorAll('.edit-oem-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var name   = this.dataset.name;
            var sort   = this.dataset.sort;
            var desc   = this.dataset.desc;
            var active = this.dataset.active;

            document.getElementById('oem-form-title').textContent = 'Edit OEM';
            document.getElementById('oem-form-desc').textContent  = 'Updating this OEM applies immediately to the storefront filter.';
            document.getElementById('oem-name').value    = name;
            document.getElementById('oem-desc').value    = desc;
            document.getElementById('oem-sort').value    = sort;
            document.getElementById('oem-active').value  = active;
            document.getElementById('oem-edit-id').value = id;
            document.getElementById('oem-submit-btn').textContent = '✓ Update OEM';
            document.getElementById('oem-reset-btn').style.display = 'inline-flex';

            var form = document.getElementById('add-oem-form');
            form.action = '/admin/oems/' + id;

            document.getElementById('oem-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.getElementById('oem-reset-btn').addEventListener('click', function () {
        var form = document.getElementById('add-oem-form');
        form.reset();
        form.action = '/admin/oems';
        document.getElementById('oem-edit-id').value = '';
        document.getElementById('oem-form-title').textContent = 'Add OEM';
        document.getElementById('oem-form-desc').textContent  = 'New OEMs are immediately available in the vehicle form dropdown.';
        document.getElementById('oem-submit-btn').textContent = '+ Save OEM';
        this.style.display = 'none';
    });
})();
</script>

<?php $this->endSection() ?>
