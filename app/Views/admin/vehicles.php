<?php
$pageTitle     = 'Vehicles';
$activeNav     = 'vehicles';
$mastheadLabel = 'Product Registry';
$mastheadTitle = 'Vehicle management';
$mastheadText  = 'Each vehicle belongs to a parent OEM and can be linked to any number of compatible products.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/oems" class="btn btn-outline">OEMs</a>
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
        <span>Total Vehicles</span>
        <strong><?= esc((string) count($vehicles)) ?></strong>
        <p>Machine/equipment models registered for compatibility filtering.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Linked Products</span>
        <strong><?= esc((string) array_sum($productCounts)) ?></strong>
        <p>Product &harr; vehicle links across the catalogue.</p>
    </article>
</div>

<section class="admin-workbench">

    <!-- ── Vehicle list ── -->
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2>Vehicle Registry</h2>
                <p>Each vehicle appears in the product form's compatibility checklist, grouped by its parent category.</p>
            </div>
        </div>

        <?php if (empty($oemOptions)): ?>
            <div class="empty-state empty-state--compact">
                <h3>No OEMs yet.</h3>
                <p>Vehicles need a parent OEM &mdash; <a href="/admin/oems">create one first</a>.</p>
            </div>
        <?php elseif ($vehicles === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No vehicles yet.</h3>
                <p>Use the form on the right to add your first vehicle.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>OEM</th>
                            <th>Products</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:var(--adm-navy)"><?= esc($vehicle['name']) ?></strong>
                                <div style="font-size:11px;color:var(--adm-faint);font-family:monospace"><?= esc($vehicle['slug']) ?></div>
                            </td>
                            <td>
                                <?php if (! empty($vehicle['oem_name'])): ?>
                                    <span class="admin-inline-pill"><?= esc($vehicle['oem_name']) ?></span>
                                <?php else: ?>
                                    <span style="font-size:12px;color:var(--adm-faint)">No OEM</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="admin-badge">
                                    <?= esc((string) ($productCounts[(int) $vehicle['id']] ?? 0)) ?> products
                                </span>
                            </td>
                            <td style="color:var(--adm-faint);font-size:13px"><?= esc((string) $vehicle['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $vehicle['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $vehicle['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-veh-btn"
                                            data-id="<?= esc((string) $vehicle['id']) ?>"
                                            data-name="<?= esc($vehicle['name']) ?>"
                                            data-oem="<?= esc((string) ($vehicle['oem_id'] ?? '')) ?>"
                                            data-sort="<?= esc((string) $vehicle['sort_order']) ?>"
                                            data-desc="<?= esc($vehicle['description'] ?? '') ?>"
                                            data-active="<?= esc((string) $vehicle['is_active']) ?>"
                                            style="background:var(--adm-bg);border:1.5px solid var(--adm-border);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--adm-navy);cursor:pointer;transition:all .15s">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/vehicles/<?= esc((string) $vehicle['id']) ?>/delete"
                                          data-confirm="Delete vehicle &quot;<?= esc($vehicle['name']) ?>&quot;? Product links will be removed."
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
        <section class="admin-panel" id="veh-form-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="veh-form-title">Add Vehicle</h2>
                    <p id="veh-form-desc">New vehicles are immediately available in the product form's compatibility checklist.</p>
                </div>
            </div>

            <?php if (empty($oemOptions)): ?>
                <p class="adm-field-hint">Create an OEM first before adding vehicles.</p>
            <?php else: ?>
            <form method="post" action="/admin/vehicles" id="add-veh-form" class="admin-form-grid" style="grid-template-columns:1fr">
                <?= csrf_field() ?>
                <input type="hidden" name="_edit_id" id="veh-edit-id" value="">

                <div class="form-group">
                    <label for="veh-name">Name <span style="color:#e53e3e">*</span></label>
                    <input id="veh-name" type="text" name="name" placeholder="e.g. MPR-100" required>
                </div>
                <div class="form-group">
                    <label for="veh-oem">Parent OEM <span style="color:#e53e3e">*</span></label>
                    <select id="veh-oem" name="oem_id" required>
                        <option value="">&mdash; Select an OEM &mdash;</option>
                        <?php foreach ($oemOptions as $oem): ?>
                            <option value="<?= esc((string) $oem['id']) ?>"><?= esc($oem['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="veh-desc">Description</label>
                    <textarea id="veh-desc" name="description" style="min-height:70px" placeholder="Optional short description for internal use."></textarea>
                </div>
                <div class="form-group">
                    <label for="veh-sort">Sort Order</label>
                    <input id="veh-sort" type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="veh-active">Status</label>
                    <select id="veh-active" name="is_active">
                        <option value="1">&#128994; Active</option>
                        <option value="0">&#9899; Hidden</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="veh-submit-btn">&#43; Save Vehicle</button>
                    <button type="button" id="veh-reset-btn" class="btn btn-outline" style="display:none">Cancel</button>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </div>

</section>

<script>
(function () {
    var editButtons = document.querySelectorAll('.edit-veh-btn');
    if (editButtons.length === 0) { return; }

    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var name   = this.dataset.name;
            var oem    = this.dataset.oem;
            var sort   = this.dataset.sort;
            var desc   = this.dataset.desc;
            var active = this.dataset.active;

            document.getElementById('veh-form-title').textContent = 'Edit Vehicle';
            document.getElementById('veh-form-desc').textContent  = 'Updating this vehicle applies immediately to the storefront filter.';
            document.getElementById('veh-name').value    = name;
            document.getElementById('veh-oem').value     = oem;
            document.getElementById('veh-desc').value    = desc;
            document.getElementById('veh-sort').value    = sort;
            document.getElementById('veh-active').value  = active;
            document.getElementById('veh-edit-id').value = id;
            document.getElementById('veh-submit-btn').textContent = '✓ Update Vehicle';
            document.getElementById('veh-reset-btn').style.display = 'inline-flex';

            var form = document.getElementById('add-veh-form');
            form.action = '/admin/vehicles/' + id;

            document.getElementById('veh-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    var resetBtn = document.getElementById('veh-reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            var form = document.getElementById('add-veh-form');
            form.reset();
            form.action = '/admin/vehicles';
            document.getElementById('veh-edit-id').value = '';
            document.getElementById('veh-form-title').textContent = 'Add Vehicle';
            document.getElementById('veh-form-desc').textContent  = "New vehicles are immediately available in the product form's compatibility checklist.";
            document.getElementById('veh-submit-btn').textContent = '+ Save Vehicle';
            this.style.display = 'none';
        });
    }
})();
</script>

<?php $this->endSection() ?>
