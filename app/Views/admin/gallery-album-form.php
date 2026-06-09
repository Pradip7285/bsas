<?php
$isEdit = $album !== null;
$pageTitle = $isEdit ? 'Edit Gallery Album' : 'Add Gallery Album';
$activeNav = 'gallery-album-editor';
$mastheadLabel = 'Gallery Manager';
$mastheadTitle = $isEdit ? 'Edit gallery album' : 'Create gallery album';
$mastheadText = 'This record defines the album card on gallery home and the hero/content for the album detail page.';
$formAction = $isEdit ? '/admin/gallery/' . (int) $album['id'] : '/admin/gallery';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <?php if ($isEdit): ?><a href="/admin/gallery/<?= esc((string) $album['id']) ?>/items" class="btn btn-outline">Manage Images</a><?php endif; ?>
    <a href="/admin/gallery" class="btn btn-dark">&#8592; All Albums</a>
<?php $this->endSection() ?>

<?= $this->section('beforeContent') ?>
    <?php if (! empty($errors)): ?>
        <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
    <?php endif; ?>
<?php $this->endSection() ?>

<?= $this->section('content') ?>
<section class="admin-panel admin-panel--primary">
    <div class="admin-panel-head">
        <div>
            <h2><?= $isEdit ? 'Edit: ' . esc($album['name']) : 'Create new gallery album' ?></h2>
            <p>Use this to define the album card, hero image, summary, and intro copy. Images are managed separately.</p>
        </div>
    </div>

    <form method="post" action="<?= esc($formAction) ?>" class="admin-form-grid" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group form-full">
            <label for="ga-name">Album Name</label>
            <input id="ga-name" type="text" name="name" value="<?= esc(old('name', $album['name'] ?? '')) ?>" required>
        </div>

        <div class="form-group form-full">
            <label for="ga-slug">Slug</label>
            <input id="ga-slug" type="text" name="slug" value="<?= esc(old('slug', $album['slug'] ?? '')) ?>" placeholder="ime-2025">
        </div>

        <div class="form-group">
            <label for="ga-eyebrow">Eyebrow</label>
            <input id="ga-eyebrow" type="text" name="eyebrow" value="<?= esc(old('eyebrow', $album['eyebrow'] ?? '')) ?>" placeholder="Flagship Event">
        </div>

        <div class="form-group">
            <label for="ga-location">Location</label>
            <input id="ga-location" type="text" name="location" value="<?= esc(old('location', $album['location'] ?? '')) ?>" placeholder="Dhanbad, Jharkhand">
        </div>

        <div class="form-group">
            <label for="ga-event-date">Event Date</label>
            <input id="ga-event-date" type="date" name="event_date" value="<?= esc(old('event_date', $album['event_date'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label for="ga-sort">Sort Order</label>
            <input id="ga-sort" type="number" name="sort_order" value="<?= esc(old('sort_order', (string) ($album['sort_order'] ?? 0))) ?>">
        </div>

        <div class="form-group">
            <label>Cover Image</label>
            <input type="file" name="cover_image_file" id="ga-cover-file" accept="image/*"
                   onchange="previewAlbumUpload(this,'ga-cover-preview')" style="margin-bottom:8px">
            <?php if ($isEdit && ! empty($album['cover_image_url'])): ?>
                <img id="ga-cover-preview" src="<?= esc($album['cover_image_url']) ?>"
                     alt="Cover preview" style="max-width:160px;max-height:110px;border-radius:8px;display:block;margin-bottom:6px;border:1.5px solid var(--adm-border)">
            <?php else: ?>
                <img id="ga-cover-preview" src="" alt="" style="max-width:160px;max-height:110px;border-radius:8px;display:none;margin-bottom:6px;border:1.5px solid var(--adm-border)">
            <?php endif; ?>
            <p class="adm-field-hint" style="margin:0 0 6px">Or enter an existing image URL:</p>
            <input id="ga-cover" type="text" name="cover_image_url"
                   value="<?= esc(old('cover_image_url', $album['cover_image_url'] ?? '')) ?>"
                   placeholder="/assets/images/b&w.png">
        </div>

        <div class="form-group">
            <label>Hero Image</label>
            <input type="file" name="hero_image_file" id="ga-hero-file" accept="image/*"
                   onchange="previewAlbumUpload(this,'ga-hero-preview')" style="margin-bottom:8px">
            <?php if ($isEdit && ! empty($album['hero_image_url'])): ?>
                <img id="ga-hero-preview" src="<?= esc($album['hero_image_url']) ?>"
                     alt="Hero preview" style="max-width:160px;max-height:110px;border-radius:8px;display:block;margin-bottom:6px;border:1.5px solid var(--adm-border)">
            <?php else: ?>
                <img id="ga-hero-preview" src="" alt="" style="max-width:160px;max-height:110px;border-radius:8px;display:none;margin-bottom:6px;border:1.5px solid var(--adm-border)">
            <?php endif; ?>
            <p class="adm-field-hint" style="margin:0 0 6px">Or enter an existing image URL:</p>
            <input id="ga-hero" type="text" name="hero_image_url"
                   value="<?= esc(old('hero_image_url', $album['hero_image_url'] ?? '')) ?>"
                   placeholder="/assets/images/photo1.webp">
        </div>

        <div class="form-group">
            <label for="ga-status">Status</label>
            <select id="ga-status" name="is_active">
                <option value="1" <?= old('is_active', (string) ($album['is_active'] ?? '1')) === '1' ? 'selected' : '' ?>>Published</option>
                <option value="0" <?= old('is_active', (string) ($album['is_active'] ?? '1')) === '0' ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>

        <div class="form-group form-full">
            <label for="ga-summary">Summary</label>
            <textarea id="ga-summary" name="summary" style="min-height:100px"><?= esc(old('summary', $album['summary'] ?? '')) ?></textarea>
        </div>

        <div class="form-group form-full">
            <label for="ga-intro">Album Intro</label>
            <textarea id="ga-intro" name="intro_text" style="min-height:140px"><?= esc(old('intro_text', $album['intro_text'] ?? '')) ?></textarea>
        </div>

        <div class="form-full">
            <button type="submit" class="btn"><?= $isEdit ? '&#10003; Update Album' : '&#43; Create Album' ?></button>
        </div>
    </form>
</section>

<script>
function previewAlbumUpload(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
        // Clear the paired URL text field
        var urlField = input.id === 'ga-cover-file' ? 'ga-cover' : 'ga-hero';
        document.getElementById(urlField).value = '';
    }
}
</script>
<?php $this->endSection() ?>
