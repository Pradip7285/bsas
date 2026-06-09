<?php
$pageTitle = 'Gallery Images';
$activeNav = 'gallery-items';
$mastheadLabel = 'Gallery Manager';
$mastheadTitle = 'Album images';
$mastheadText = 'Manage the visual tiles for one album. Set layout style, captions, and publish state per image.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/gallery/<?= esc($album['slug']) ?>" target="_blank" class="btn btn-outline">Preview Album</a>
    <a href="/admin/gallery/<?= esc((string) $album['id']) ?>/edit" class="btn btn-outline">Edit Album</a>
    <a href="/admin/gallery" class="btn btn-dark">&#8592; All Albums</a>
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
<section class="admin-workbench">
    <section class="admin-panel admin-panel--primary">
        <div class="admin-panel-head">
            <div>
                <h2><?= esc($album['name']) ?></h2>
                <p><?= esc($album['location'] ?: 'No location set') ?>. Use the form on the right to add images, then refine order and layout style.</p>
            </div>
        </div>

        <?php if ($items === []): ?>
            <div class="empty-state empty-state--compact">
                <h3>No images yet.</h3>
                <p>Add the first image tile for this album.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Frame</th>
                            <th>Style</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong style="font-size:14px;color:#172533"><?= esc($item['title']) ?></strong>
                                <div class="admin-row-meta"><?= esc($item['badge_label'] ?: 'No badge') ?></div>
                                <div class="admin-row-meta"><?= esc($item['image_url']) ?></div>
                            </td>
                            <td>
                                <span class="admin-inline-pill"><?= esc($item['display_style']) ?></span>
                                <?php if ((int) $item['is_featured'] === 1): ?><span class="admin-inline-pill">featured</span><?php endif; ?>
                            </td>
                            <td><?= esc((string) $item['sort_order']) ?></td>
                            <td>
                                <span class="admin-badge <?= (int) $item['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                    <?= (int) $item['is_active'] === 1 ? 'Published' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button type="button"
                                            class="edit-gallery-item-btn"
                                            data-id="<?= esc((string) $item['id']) ?>"
                                            data-title="<?= esc($item['title']) ?>"
                                            data-caption="<?= esc($item['caption'] ?? '') ?>"
                                            data-image="<?= esc($item['image_url']) ?>"
                                            data-badge="<?= esc($item['badge_label'] ?? '') ?>"
                                            data-style="<?= esc($item['display_style'] ?? 'standard') ?>"
                                            data-featured="<?= esc((string) $item['is_featured']) ?>"
                                            data-active="<?= esc((string) $item['is_active']) ?>"
                                            data-sort="<?= esc((string) $item['sort_order']) ?>">
                                        Edit
                                    </button>
                                    <form method="post" action="/admin/gallery/items/<?= esc((string) $item['id']) ?>/delete" onsubmit="return confirm('Delete this gallery image?')">
                                        <?= csrf_field() ?>
                                        <button type="submit">Delete</button>
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

    <div class="admin-rail">
        <section class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2 id="gallery-item-form-title">Add image</h2>
                    <p id="gallery-item-form-text">Create image tiles for this album. Wide and tall styles affect the public mosaic layout.</p>
                </div>
            </div>

            <form method="post" action="/admin/gallery/<?= esc((string) $album['id']) ?>/items" id="gallery-item-form" class="admin-form-grid" style="grid-template-columns:1fr" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="gi-title">Title</label>
                    <input id="gi-title" type="text" name="title" value="<?= esc(old('title')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="gi-badge">Badge Label</label>
                    <input id="gi-badge" type="text" name="badge_label" value="<?= esc(old('badge_label')) ?>">
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" id="gi-image-file" accept="image/*"
                           onchange="previewGalleryUpload(this)" style="margin-bottom:8px">
                    <img id="gi-image-preview" src="" alt=""
                         style="max-width:140px;max-height:100px;border-radius:8px;display:none;margin-bottom:6px;border:1.5px solid var(--adm-border)">
                    <p class="adm-field-hint" style="margin:0 0 6px">Or enter an existing image URL:</p>
                    <input id="gi-image" type="text" name="image_url" value="<?= esc(old('image_url')) ?>"
                           placeholder="/uploads/photo.jpg" oninput="previewGalleryUrl(this.value)">
                </div>
                <div class="form-group">
                    <label for="gi-style">Display Style</label>
                    <select id="gi-style" name="display_style">
                        <option value="standard">Standard</option>
                        <option value="wide">Wide</option>
                        <option value="tall">Tall</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gi-sort">Sort Order</label>
                    <input id="gi-sort" type="number" name="sort_order" value="<?= esc(old('sort_order', '0')) ?>">
                </div>
                <div class="form-group">
                    <label for="gi-featured">Featured</label>
                    <select id="gi-featured" name="is_featured">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gi-active">Status</label>
                    <select id="gi-active" name="is_active">
                        <option value="1">Published</option>
                        <option value="0">Hidden</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gi-caption">Caption</label>
                    <textarea id="gi-caption" name="caption" style="min-height:100px"><?= esc(old('caption')) ?></textarea>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn" id="gallery-item-submit">+ Save Image</button>
                    <button type="button" class="btn btn-outline" id="gallery-item-reset" style="display:none">Cancel</button>
                </div>
            </form>
        </section>
    </div>
</section>

<script>
(function () {
    var form = document.getElementById('gallery-item-form');
    var resetBtn = document.getElementById('gallery-item-reset');
    var submitBtn = document.getElementById('gallery-item-submit');
    var title = document.getElementById('gi-title');
    var badge = document.getElementById('gi-badge');
    var image = document.getElementById('gi-image');
    var imageFile = document.getElementById('gi-image-file');
    var imagePreview = document.getElementById('gi-image-preview');
    var style = document.getElementById('gi-style');
    var sort = document.getElementById('gi-sort');
    var featured = document.getElementById('gi-featured');
    var active = document.getElementById('gi-active');
    var caption = document.getElementById('gi-caption');
    var formTitle = document.getElementById('gallery-item-form-title');
    var formText = document.getElementById('gallery-item-form-text');

    document.querySelectorAll('.edit-gallery-item-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = '/admin/gallery/items/' + this.dataset.id;
            title.value = this.dataset.title;
            badge.value = this.dataset.badge;
            image.value = this.dataset.image;
            style.value = this.dataset.style;
            sort.value = this.dataset.sort;
            featured.value = this.dataset.featured;
            active.value = this.dataset.active;
            caption.value = this.dataset.caption;
            // Show existing image in preview
            if (this.dataset.image) {
                imagePreview.src = this.dataset.image;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
            // Clear any previously chosen file
            imageFile.value = '';
            submitBtn.textContent = '✓ Update Image';
            resetBtn.style.display = 'inline-flex';
            formTitle.textContent = 'Edit image';
            formText.textContent = 'Update the image tile details for this album.';
        });
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        form.action = '/admin/gallery/<?= esc((string) $album['id']) ?>/items';
        submitBtn.textContent = '+ Save Image';
        formTitle.textContent = 'Add image';
        formText.textContent = 'Create image tiles for this album. Wide and tall styles affect the public mosaic layout.';
        resetBtn.style.display = 'none';
        imagePreview.style.display = 'none';
        imagePreview.src = '';
    });

    window.previewGalleryUpload = function (input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
            image.value = '';
        }
    };

    window.previewGalleryUrl = function (url) {
        if (url && url.trim() !== '') {
            imagePreview.src = url.trim();
            imagePreview.style.display = 'block';
        } else {
            imagePreview.style.display = 'none';
        }
    };
})();
</script>
<?php $this->endSection() ?>
