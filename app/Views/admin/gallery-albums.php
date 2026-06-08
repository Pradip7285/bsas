<?php
$pageTitle = 'Gallery Albums';
$activeNav = 'gallery-albums';
$mastheadLabel = 'Gallery Manager';
$mastheadTitle = 'Album management';
$mastheadText = 'Create album landing pages for events, facilities, and field stories. Each album has its own public page.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/gallery/new" class="btn">Add Album</a>
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
<div class="admin-summary-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="admin-summary-card">
        <span>Total Albums</span>
        <strong><?= esc((string) count($albums)) ?></strong>
        <p>Separate public album pages available in the gallery module.</p>
    </article>
    <article class="admin-summary-card admin-summary-card--accent">
        <span>Published</span>
        <strong><?= esc((string) count(array_filter($albums, static fn(array $album): bool => (int) $album['is_active'] === 1))) ?></strong>
        <p>Albums currently visible on the gallery home page.</p>
    </article>
    <article class="admin-summary-card">
        <span>Images Linked</span>
        <strong><?= esc((string) array_sum($itemCounts)) ?></strong>
        <p>Total gallery images distributed across all albums.</p>
    </article>
</div>

<section class="admin-panel admin-panel--primary">
    <div class="admin-panel-head">
        <div>
            <h2>Album Directory</h2>
            <p>Each row controls one album landing page and links to the image manager for that album.</p>
        </div>
    </div>

    <?php if ($albums === []): ?>
        <div class="empty-state empty-state--compact">
            <h3>No albums created.</h3>
            <p>Create the first gallery album to start publishing event pages.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-shell">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Album</th>
                        <th>Slug</th>
                        <th>Frames</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($albums as $album): ?>
                    <tr>
                        <td>
                            <strong style="font-size:14px;color:#172533"><?= esc($album['name']) ?></strong>
                            <div class="admin-row-meta"><?= esc($album['location'] ?: 'No location set') ?></div>
                        </td>
                        <td><span class="admin-inline-pill">/gallery/<?= esc($album['slug']) ?></span></td>
                        <td><span class="admin-badge"><?= esc((string) ($itemCounts[(int) $album['id']] ?? 0)) ?> images</span></td>
                        <td><?= esc((string) $album['sort_order']) ?></td>
                        <td>
                            <span class="admin-badge <?= (int) $album['is_active'] === 1 ? 'admin-badge--success' : 'admin-badge--muted' ?>">
                                <?= (int) $album['is_active'] === 1 ? 'Published' : 'Hidden' ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions" style="flex-wrap:wrap">
                                <a href="/admin/gallery/<?= esc((string) $album['id']) ?>/items">Images</a>
                                <a href="/admin/gallery/<?= esc((string) $album['id']) ?>/edit">Edit</a>
                                <a href="/gallery/<?= esc($album['slug']) ?>" target="_blank">Preview</a>
                                <form method="post" action="/admin/gallery/<?= esc((string) $album['id']) ?>/delete" onsubmit="return confirm('Delete this album and all of its images?')">
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
<?php $this->endSection() ?>
