<?php
$specs = [];
if (! empty($product['specifications'])) {
    $decoded = json_decode((string) $product['specifications'], true);
    if (is_array($decoded)) {
        $specs = $decoded;
    }
}
$hasSpecs        = count($specs) > 0;
$hasCompatibility = ! empty($product['compatibility']);
$stockStatus     = $product['stock_status'] ?? 'in_stock';
$moq             = max(1, (int) ($product['min_order_qty'] ?? 1));
?>
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

        <!-- Left: Image + Spec Card -->
        <div class="sp-gallery">
            <div class="sp-product-img sp-product-img-wrap">
                <img class="sp-product-img-tag"
                     src="<?= esc($product['image_url'] ?: '/assets/images/sparePart.webp') ?>"
                     alt="<?= esc($product['name']) ?>"
                     loading="lazy" decoding="async">
                <?php if (! empty($product['is_featured'])): ?>
                    <span class="sp-featured-badge">&#9733; Featured</span>
                <?php endif; ?>
            </div>

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
                    <?php if (isset($product['stock_status'])): ?>
                    <div class="sp-spec-row">
                        <dt>Availability</dt>
                        <dd>
                            <?php if ($stockStatus === 'in_stock'): ?>
                                <span class="sp-stock-pill sp-stock--in">&#9679; In Stock</span>
                            <?php elseif ($stockStatus === 'made_to_order'): ?>
                                <span class="sp-stock-pill sp-stock--mto">&#9679; Made to Order</span>
                            <?php else: ?>
                                <span class="sp-stock-pill sp-stock--out">&#9679; Out of Stock</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($product['lead_time'])): ?>
                    <div class="sp-spec-row">
                        <dt>Lead Time</dt>
                        <dd><?= esc($product['lead_time']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($moq > 1): ?>
                    <div class="sp-spec-row">
                        <dt>Min. Order</dt>
                        <dd><?= esc((string) $moq) ?> units</dd>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($product['weight'])): ?>
                    <div class="sp-spec-row">
                        <dt>Weight</dt>
                        <dd><?= esc($product['weight']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($product['dimensions'])): ?>
                    <div class="sp-spec-row">
                        <dt>Dimensions</dt>
                        <dd><?= esc($product['dimensions']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if (! empty($product['material'])): ?>
                    <div class="sp-spec-row">
                        <dt>Material</dt>
                        <dd><?= esc($product['material']) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
                <?php if (! empty($product['datasheet_url'])): ?>
                <div class="sp-datasheet-row">
                    <a href="<?= esc($product['datasheet_url']) ?>" target="_blank" rel="noopener noreferrer"
                       class="btn btn-outline sp-datasheet-btn">
                        &#11015; Download Datasheet
                    </a>
                </div>
                <?php endif; ?>
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
                    <?php if (isset($product['stock_status'])): ?>
                        <?php if ($stockStatus === 'in_stock'): ?>
                            <span class="sp-stock-inline sp-stock--in">&#9679; In Stock</span>
                        <?php elseif ($stockStatus === 'made_to_order'): ?>
                            <span class="sp-stock-inline sp-stock--mto">&#9679; Made to Order</span>
                        <?php else: ?>
                            <span class="sp-stock-inline sp-stock--out">&#9679; Out of Stock</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <h1><?= esc($product['name']) ?></h1>
                <p class="sp-product-short-desc"><?= esc($product['short_description'] ?: $product['description']) ?></p>
                <div class="sp-price-row">
                    <span class="sp-price-label">
                        &#127991; <?= esc($product['price_label'] ?: 'Quote on request') ?>
                    </span>
                    <?php if (! empty($product['lead_time'])): ?>
                        <span class="sp-lead-tag">&#128340; <?= esc($product['lead_time']) ?></span>
                    <?php endif; ?>
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
                        <span class="sp-qty-label">
                            Quantity
                            <?php if ($moq > 1): ?>
                                <small class="sp-moq-note">(min <?= esc((string) $moq) ?>)</small>
                            <?php endif; ?>
                        </span>
                        <div class="sp-qty-ctrl">
                            <button type="button" class="sp-qty-btn" onclick="spAdjQty(-1)">&#8722;</button>
                            <input id="sp-qty" type="number" min="<?= esc((string) $moq) ?>" name="quantity"
                                   value="<?= esc(old('quantity', (string) $moq)) ?>"
                                   class="sp-qty-num">
                            <button type="button" class="sp-qty-btn" onclick="spAdjQty(1)">&#43;</button>
                        </div>
                    </div>
                    <div class="sp-add-btn-row">
                        <?php if ($stockStatus === 'out_of_stock'): ?>
                            <button type="submit" class="btn" disabled>Out of Stock</button>
                        <?php else: ?>
                            <button type="submit" class="btn">Add to Basket</button>
                        <?php endif; ?>
                        <a href="/cart" class="btn btn-outline">View Basket</a>
                    </div>
                </form>
            </div>

            <!-- Tabs -->
            <div class="sp-tabs-card">
                <nav class="sp-tab-nav">
                    <?php if ($hasSpecs): ?>
                        <button class="sp-tab-btn is-active" data-tab="specs">Specifications</button>
                        <button class="sp-tab-btn" data-tab="desc">Description</button>
                    <?php else: ?>
                        <button class="sp-tab-btn is-active" data-tab="desc">Description</button>
                    <?php endif; ?>
                    <?php if ($hasCompatibility): ?>
                        <button class="sp-tab-btn" data-tab="compat">Compatibility</button>
                    <?php endif; ?>
                    <button class="sp-tab-btn" data-tab="quote">Request Quote</button>
                    <button class="sp-tab-btn" data-tab="why">Why E-Shop</button>
                </nav>

                <!-- Specifications panel -->
                <?php if ($hasSpecs): ?>
                <div class="sp-tab-panel is-active" id="sp-tab-specs">
                    <table class="sp-specs-tab-table">
                        <tbody>
                            <?php foreach ($specs as $row): ?>
                                <?php if (! empty($row['key'])): ?>
                                <tr>
                                    <th><?= esc((string) $row['key']) ?></th>
                                    <td><?= esc((string) ($row['value'] ?? '')) ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Description panel -->
                <div class="sp-tab-panel <?= $hasSpecs ? '' : 'is-active' ?>" id="sp-tab-desc">
                    <p><?= nl2br(esc($product['description'] ?: $product['short_description'])) ?></p>
                </div>

                <!-- Compatibility panel -->
                <?php if ($hasCompatibility): ?>
                <div class="sp-tab-panel" id="sp-tab-compat">
                    <p class="sp-compat-intro">Compatible with the following equipment:</p>
                    <p><?= nl2br(esc($product['compatibility'])) ?></p>
                </div>
                <?php endif; ?>

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
                                       value="<?= esc(old('quantity', (string) $moq)) ?>" required>
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
                        <img class="sp-card-img"
                             src="<?= esc($related['image_url'] ?: '/assets/images/sparePart.webp') ?>"
                             alt="<?= esc($related['name']) ?>"
                             loading="lazy" decoding="async">
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

<style>
.sp-product-img-wrap { position:relative; overflow:hidden; }
.sp-product-img-tag { width:100%; height:100%; object-fit:cover; object-position:center; display:block; }
.sp-featured-badge {
    position:absolute; top:12px; left:12px;
    background:#f59b23; color:#fff;
    font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase;
    padding:4px 10px; border-radius:20px; pointer-events:none;
}
.sp-stock-pill, .sp-stock-inline {
    display:inline-block; font-size:12px; font-weight:700;
    padding:3px 10px; border-radius:20px;
}
.sp-stock--in  { background:#d1fae5; color:#065f46; }
.sp-stock--mto { background:#fef3c7; color:#92400e; }
.sp-stock--out { background:#fee2e2; color:#991b1b; }
.sp-stock-inline { font-size:11px; padding:2px 8px; }
.sp-lead-tag {
    display:inline-block; font-size:12px; color:#6b7280;
    background:#f3f4f6; padding:4px 10px; border-radius:6px; margin-left:8px;
}
.sp-moq-note { font-size:11px; color:#9ca3af; margin-left:4px; }
.sp-datasheet-row { margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb; }
.sp-datasheet-btn { width:100%; text-align:center; font-size:13px; }
.sp-compat-intro { font-size:13px; color:#6b7280; margin-bottom:8px; }
.sp-specs-tab-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.sp-specs-tab-table th,
.sp-specs-tab-table td { padding:9px 12px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top; }
.sp-specs-tab-table th { font-weight:700; background:#f9fafb; width:38%; }
.sp-specs-tab-table tr:last-child th,
.sp-specs-tab-table tr:last-child td { border-bottom:none; }
</style>

<script>
(function () {
    document.querySelectorAll('.sp-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.tab;
            document.querySelectorAll('.sp-tab-btn').forEach(function (b) { b.classList.remove('is-active'); });
            document.querySelectorAll('.sp-tab-panel').forEach(function (p) { p.classList.remove('is-active'); });
            this.classList.add('is-active');
            var panel = document.getElementById('sp-tab-' + id);
            if (panel) { panel.classList.add('is-active'); }
        });
    });
})();

var _spMoq = <?= (int) $moq ?>;
function spAdjQty(delta) {
    var input = document.getElementById('sp-qty');
    if (!input) { return; }
    var v = parseInt(input.value, 10) || _spMoq;
    v = Math.max(_spMoq, v + delta);
    input.value = v;
}
</script>
