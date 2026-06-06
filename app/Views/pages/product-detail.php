<!-- ── Breadcrumbs ── -->
<div class="sp-breadcrumbs">
    <div class="sp-breadcrumbs-inner">
        <a href="/e-shop">Catalogue</a>
        <span class="sp-breadcrumbs-sep">&#8250;</span>
        <a href="/e-shop?category=<?= urlencode($product['category']) ?>"><?= esc($product['category']) ?></a>
        <span class="sp-breadcrumbs-sep">&#8250;</span>
        <span><?= esc($product['name']) ?></span>
    </div>
</div>

<!-- ── Detail Grid ── -->
<section class="sp-detail-section">
    <div class="sp-detail-inner">

        <!-- Left: Gallery + Spec -->
        <div class="sp-gallery">
            <div class="sp-product-img"
                 style="background-image:url('<?= esc($product['image_url'] ?: '/assets/images/sparePart.webp') ?>')"></div>

            <div class="sp-spec-card">
                <p class="sp-spec-card-title">Product Data</p>
                <dl class="sp-spec-table">
                    <div class="sp-spec-row">
                        <dt>Category</dt>
                        <dd><?= esc($product['category']) ?></dd>
                    </div>
                    <div class="sp-spec-row">
                        <dt>SKU</dt>
                        <dd><?= esc($product['sku'] ?: 'Not assigned') ?></dd>
                    </div>
                    <div class="sp-spec-row">
                        <dt>Commercial</dt>
                        <dd><?= esc($product['price_label'] ?: 'Quote on request') ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Right: Info Panel -->
        <div class="sp-product-info">

            <!-- Flash messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="sp-flash-err"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <!-- Title Card -->
            <div class="sp-title-card">
                <div class="sp-product-eyebrow">
                    <span class="sp-cat-tag"><?= esc($product['category']) ?></span>
                    <?php if (! empty($product['sku'])): ?>
                        <span class="sp-sku-tag">SKU <?= esc($product['sku']) ?></span>
                    <?php endif; ?>
                </div>
                <h1><?= esc($product['name']) ?></h1>
                <p class="sp-product-short-desc"><?= esc($product['short_description'] ?: $product['description']) ?></p>
                <div class="sp-price-row">
                    <span class="sp-price-label">
                        &#127991; <?= esc($product['price_label'] ?: 'Quote on request') ?>
                    </span>
                </div>
                <div class="sp-title-card-actions">
                    <a href="/e-shop" class="btn btn-dark">&#8592; Back to Catalogue</a>
                    <a href="/cart" class="btn btn-outline">
                        Quote Basket
                        <?php if ($cartCount > 0): ?>
                            <span class="sp-cart-badge"><?= esc((string) $cartCount) ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Add to Cart Card -->
            <div class="sp-add-card">
                <h3>&#128722; Add to Quote Basket</h3>
                <form method="post" action="/cart/add" id="add-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= esc((string) $product['id']) ?>">
                    <div class="sp-qty-row">
                        <span class="sp-qty-label">Quantity</span>
                        <div class="sp-qty-ctrl">
                            <button type="button" class="sp-qty-btn" onclick="spAdjQty(-1)">&#8722;</button>
                            <input id="sp-qty" type="number" min="1" name="quantity"
                                   value="<?= esc(old('quantity', '1')) ?>"
                                   class="sp-qty-num">
                            <button type="button" class="sp-qty-btn" onclick="spAdjQty(1)">&#43;</button>
                        </div>
                    </div>
                    <div class="sp-add-btn-row">
                        <button type="submit" class="btn">Add to Basket</button>
                        <a href="/cart" class="btn btn-outline">View Basket</a>
                    </div>
                </form>
            </div>

            <!-- Tabs: Description | Quote | Support -->
            <div class="sp-tabs-card">
                <nav class="sp-tab-nav">
                    <button class="sp-tab-btn is-active" data-tab="desc">Description</button>
                    <button class="sp-tab-btn" data-tab="quote">Request Quote</button>
                    <button class="sp-tab-btn" data-tab="why">Why E-Shop</button>
                </nav>

                <!-- Description panel -->
                <div class="sp-tab-panel is-active" id="sp-tab-desc">
                    <p><?= nl2br(esc($product['description'] ?: $product['short_description'])) ?></p>
                </div>

                <!-- Quote form panel -->
                <div class="sp-tab-panel" id="sp-tab-quote">
                    <?php if (! empty($errors)): ?>
                        <div class="sp-flash-err"><?= esc(implode(' ', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="post" action="/product-quote/<?= esc($product['slug']) ?>">
                        <?= csrf_field() ?>
                        <div class="sp-quote-grid">
                            <div class="sp-form-group">
                                <label class="sp-form-label">Name *</label>
                                <input type="text" name="name" value="<?= esc(old('name')) ?>" required>
                            </div>
                            <div class="sp-form-group">
                                <label class="sp-form-label">Company</label>
                                <input type="text" name="company" value="<?= esc(old('company')) ?>">
                            </div>
                            <div class="sp-form-group">
                                <label class="sp-form-label">Email</label>
                                <input type="email" name="email" value="<?= esc(old('email')) ?>">
                            </div>
                            <div class="sp-form-group">
                                <label class="sp-form-label">Phone *</label>
                                <input type="tel" name="phone" value="<?= esc(old('phone')) ?>" required>
                            </div>
                            <div class="sp-form-group">
                                <label class="sp-form-label">Designation</label>
                                <input type="text" name="designation" value="<?= esc(old('designation')) ?>">
                            </div>
                            <div class="sp-form-group">
                                <label class="sp-form-label">Quantity *</label>
                                <input type="number" min="1" name="quantity"
                                       value="<?= esc(old('quantity', '1')) ?>" required>
                            </div>
                            <div class="sp-form-group sp-form-full">
                                <label class="sp-form-label">Requirement Details</label>
                                <textarea name="message"><?= esc(old('message')) ?></textarea>
                            </div>
                            <div class="sp-form-full">
                                <button type="submit" class="btn">Submit Quote Request</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Why Us panel -->
                <div class="sp-tab-panel" id="sp-tab-why">
                    <p>The BSAS E-Shop is built around the B2B procurement workflow — shortlist, bundle, and submit a single coordinated RFQ rather than sending multiple one-off emails.</p>
                    <div class="sp-checklist">
                        <div class="sp-check">Shortlist the part and quantity before reaching out to sales — saves multiple back-and-forth messages.</div>
                        <div class="sp-check">Bundle multiple products into one quote basket for faster commercial handling across your team.</div>
                        <div class="sp-check">Use the support team when interchange, application matching, or lead-time validation is required before ordering.</div>
                    </div>
                    <div class="sp-support-band" style="margin-top:18px">
                        <p>For project buying, fleet planning, or compatibility queries, connect with the BSAS sales team directly.</p>
                        <div class="sp-support-actions">
                            <a href="/cart" class="btn btn-dark">Review Basket</a>
                            <a href="/support" class="btn btn-outline">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div><!-- /sp-tabs-card -->

        </div><!-- /sp-product-info -->
    </div><!-- /sp-detail-inner -->
</section>

<!-- ── Related Products ── -->
<?php if ($relatedProducts !== []): ?>
<section class="sp-related-section">
    <div class="sp-related-inner">
        <div class="sp-related-header">
            <span class="sp-section-eyebrow">You May Also Need</span>
            <h2>Related Products</h2>
        </div>
        <div class="sp-related-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <article class="sp-card">
                    <a href="/e-shop/product/<?= esc($related['slug']) ?>" class="sp-card-img-wrap" tabindex="-1">
                        <div class="sp-card-img"
                             style="background-image:url('<?= esc($related['image_url'] ?: '/assets/images/sparePart.webp') ?>')"></div>
                        <span class="sp-card-cat-badge"><?= esc($related['category']) ?></span>
                    </a>
                    <div class="sp-card-body">
                        <h3><?= esc($related['name']) ?></h3>
                        <p class="sp-card-desc"><?= esc($related['short_description'] ?: $related['description']) ?></p>
                    </div>
                    <div class="sp-card-footer">
                        <a href="/e-shop/product/<?= esc($related['slug']) ?>" class="btn">View Product</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
(function () {
    // Tab switching
    document.querySelectorAll('.sp-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.tab;
            document.querySelectorAll('.sp-tab-btn').forEach(function (b) { b.classList.remove('is-active'); });
            document.querySelectorAll('.sp-tab-panel').forEach(function (p) { p.classList.remove('is-active'); });
            this.classList.add('is-active');
            var panel = document.getElementById('sp-tab-' + id);
            if (panel) panel.classList.add('is-active');
        });
    });
})();

function spAdjQty(delta) {
    var input = document.getElementById('sp-qty');
    if (!input) return;
    var v = parseInt(input.value, 10) || 1;
    v = Math.max(1, v + delta);
    input.value = v;
}
</script>
