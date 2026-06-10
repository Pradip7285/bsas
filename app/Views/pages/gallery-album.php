<?php
$heroImage = $album['hero_image_url'] ?: '/assets/images/b&w.png';
$featuredItems = array_values(array_filter($items, static fn(array $item): bool => (int) ($item['is_featured'] ?? 0) === 1));
$primaryFeature = $featuredItems[0] ?? ($items[0] ?? null);
?>

<section class="gallery-album-hero" style="--gallery-hero-image:url('<?= esc($heroImage) ?>');">
    <div class="gallery-album-hero__overlay"></div>
    <div class="gallery-album-hero__content">
        <a href="/gallery" class="gallery-back">&larr; Back to Albums</a>
        <span class="gallery-kicker"><?= esc($album['eyebrow'] ?: 'Gallery Album') ?></span>
        <h1><?= esc($album['name']) ?></h1>
        <p><?= esc($album['summary']) ?></p>

        <div class="gallery-album-hero__meta">
            <?php if (! empty($album['location'])): ?><span><?= esc($album['location']) ?></span><?php endif; ?>
            <?php if (! empty($album['event_date'])): ?><span><?= esc($album['event_date']) ?></span><?php endif; ?>
            <span><?= esc((string) count($items)) ?> images</span>
        </div>
    </div>
</section>

<section class="gallery-album-shell">
    <div class="gallery-album-layout">
        <aside class="gallery-album-aside">
            <div class="gallery-album-panel">
                <span class="section-label">Album Note</span>
                <h2><?= esc($album['name']) ?></h2>
                <p><?= esc($album['intro_text'] ?: $album['summary']) ?></p>
            </div>

            <?php if ($primaryFeature !== null): ?>
                <div class="gallery-album-panel gallery-album-panel--feature">
                    <span class="section-label">Featured Frame</span>
                    <strong><?= esc($primaryFeature['title']) ?></strong>
                    <p><?= esc($primaryFeature['caption']) ?></p>
                </div>
            <?php endif; ?>
        </aside>

        <div class="gallery-album-content">
            <?php if ($items === []): ?>
                <div class="gallery-empty">
                    <h3>No images published in this album yet.</h3>
                    <p>Add images from the admin gallery manager to populate this page.</p>
                </div>
            <?php else: ?>
                <div class="gallery-album-grid">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $styleClass = match ($item['display_style'] ?? 'standard') {
                            'wide' => 'is-wide',
                            'tall' => 'is-tall',
                            default => '',
                        };
                        ?>
                        <figure class="gallery-album-grid__item <?= esc($styleClass) ?>">
                            <img src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title']) ?>" loading="lazy" decoding="async">
                            <figcaption>
                                <?php if (! empty($item['badge_label'])): ?><span><?= esc($item['badge_label']) ?></span><?php endif; ?>
                                <strong><?= esc($item['title']) ?></strong>
                                <?php if (! empty($item['caption'])): ?><p><?= esc($item['caption']) ?></p><?php endif; ?>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
