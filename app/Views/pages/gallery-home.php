<section class="gallery-home-hero">
    <div class="gallery-home-hero__backdrop"></div>
    <div class="gallery-home-hero__content">
        <span class="gallery-kicker">Gallery Archive</span>
        <h1>Album-wise stories from the <span>BSAS field and floor</span></h1>
        <p>Browse exhibitions, workshop activity, and engineering moments as dedicated albums instead of a single flat image wall.</p>
    </div>
</section>

<section class="gallery-home-shell">
    <div class="gallery-home-intro">
        <div>
            <span class="section-label">Album Index</span>
            <h2>Every gallery now has its <span class="accent">own page</span></h2>
        </div>
        <p>The home page acts as the gallery directory. Each album opens into a separate premium visual page, and the content is ready to be managed from the admin backend.</p>
    </div>

    <div class="gallery-home-stats">
        <article>
            <strong><?= esc((string) count($albums)) ?></strong>
            <span>albums published</span>
        </article>
        <article>
            <strong><?= esc((string) array_sum(array_map(static fn(array $album): int => (int) ($album['item_count'] ?? 0), $albums))) ?></strong>
            <span>images in archive</span>
        </article>
        <article>
            <strong>Admin</strong>
            <span>managed content flow</span>
        </article>
    </div>

    <?php if ($albums === []): ?>
        <div class="gallery-empty">
            <h3>No gallery albums published yet.</h3>
            <p>Create albums and add images from the admin area to populate this page.</p>
        </div>
    <?php else: ?>
        <div class="gallery-album-directory">
            <?php foreach ($albums as $album): ?>
                <article class="gallery-album-directory__card">
                    <a href="/gallery/<?= esc($album['slug']) ?>" class="gallery-album-directory__media">
                        <span class="gallery-album-directory__overlay"></span>
                        <img src="<?= esc($album['cover_image_url']) ?>" alt="<?= esc($album['name']) ?>" loading="lazy" decoding="async">
                        <span class="gallery-album-directory__eyebrow"><?= esc($album['eyebrow'] ?: 'Gallery Album') ?></span>
                    </a>

                    <div class="gallery-album-directory__body">
                        <div class="gallery-album-directory__meta">
                            <span><?= esc($album['location'] ?: 'BSAS') ?></span>
                            <span><?= esc((string) ($album['item_count'] ?? 0)) ?> frames</span>
                        </div>
                        <h3><a href="/gallery/<?= esc($album['slug']) ?>"><?= esc($album['name']) ?></a></h3>
                        <p><?= esc($album['summary']) ?></p>

                        <?php if (! empty($album['preview_items'])): ?>
                            <div class="gallery-album-directory__strip">
                                <?php foreach ($album['preview_items'] as $preview): ?>
                                    <span style="background-image:url('<?= esc($preview['image_url']) ?>')"></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <a href="/gallery/<?= esc($album['slug']) ?>" class="gallery-link">Open Album &rarr;</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
