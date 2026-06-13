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

            <!-- Content sections — always visible (no tabs) for SEO indexing -->
            <div class="sp-content-sections">

                <!-- Description -->
                <section class="sp-content-block" id="description">
                    <h2 class="sp-section-heading">Product Description</h2>
                    <div class="sp-desc-body">
                        <?= nl2br(esc($product['description'] ?: $product['short_description'])) ?>
                    </div>
                </section>

                <!-- Technical Specifications -->
                <?php if ($hasSpecs): ?>
                <section class="sp-content-block" id="specifications">
                    <h2 class="sp-section-heading">Technical Specifications</h2>
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
                </section>
                <?php endif; ?>

                <!-- Compatible Equipment -->
                <?php if ($hasCompatibility): ?>
                <section class="sp-content-block" id="compatibility">
                    <h2 class="sp-section-heading">Compatible Equipment</h2>
                    <p class="sp-compat-intro">This <?= esc(strtolower($product['category'])) ?> is validated for use with the following machines and systems:</p>
                    <ul class="sp-compat-list">
                        <?php foreach (array_filter(array_map('trim', explode("\n", $product['compatibility']))) as $compat): ?>
                            <li><?= esc($compat) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <!-- Applications & Industries -->
                <section class="sp-content-block" id="applications">
                    <h2 class="sp-section-heading">Applications &amp; Industries</h2>
                    <p>This <?= esc(strtolower($product['category'])) ?> is sourced and engineering-validated by BSAS for use across mining, construction, and geotechnical drilling operations. All components are benchmarked against OEM specifications for fit, form, and function in demanding field conditions across India.</p>
                    <ul class="sp-app-list">
                        <li>Surface and underground rock drilling</li>
                        <li>Mining fleet maintenance and scheduled overhaul</li>
                        <li>Tunnelling and construction machinery</li>
                        <li>Geotechnical investigation and exploration rigs</li>
                    </ul>
                    <p class="sp-app-links">
                        Browse more <a href="/e-shop?category=<?= urlencode($product['category']) ?>"><?= esc($product['category']) ?></a>
                        &nbsp;&middot;&nbsp;
                        <a href="/spare-parts">All Spare Parts</a>
                        &nbsp;&middot;&nbsp;
                        <a href="/support">Get Engineering Advice</a>
                    </p>
                </section>

                <!-- How to Request a Quote -->
                <section class="sp-content-block" id="how-to-order">
                    <h2 class="sp-section-heading">How to Request a Quote</h2>
                    <ol class="sp-how-list">
                        <li>Add this product to your <strong>Quote Basket</strong> using the quantity selector above.</li>
                        <li>Include other required parts in the same basket for a single consolidated RFQ.</li>
                        <li>Submit from the <a href="/cart">Quote Basket</a> — the BSAS sales team will respond within <strong>one business day</strong> with pricing and lead time.</li>
                    </ol>

                    <details class="sp-quote-details">
                        <summary class="sp-quote-summary">Or submit a direct quote request for this product</summary>
                        <?php if (! empty($errors)): ?>
                            <div class="sp-flash-err" style="margin-top:12px"><?= esc(implode(' ', $errors)) ?></div>
                        <?php endif; ?>
                        <form method="post" action="/product-quote/<?= esc($product['slug']) ?>" class="sp-quote-form-inline">
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
                                    <input type="number" min="1" name="quantity" value="<?= esc(old('quantity', (string) $moq)) ?>" required>
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
                    </details>
                </section>

                <!-- Why Source from BSAS -->
                <section class="sp-content-block" id="why-bsas">
                    <h2 class="sp-section-heading">Why Source from BSAS</h2>
                    <ul class="sp-why-list">
                        <li><strong>Engineering validation</strong> — Every component is benchmarked against OEM specifications before supply. No unvetted alternatives reach our catalogue.</li>
                        <li><strong>Reverse engineering capability</strong> — Where OEM drawings or sources are unavailable, BSAS manufactures to sample or field measurement.</li>
                        <li><strong>Fleet-scale procurement</strong> — Consolidate multiple parts into a single RFQ, reducing procurement overhead for large mining and construction fleets.</li>
                        <li><strong>After-sales accountability</strong> — BSAS takes engineering responsibility for every component supplied. If it fails under normal operating conditions, we investigate and act.</li>
                    </ul>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;">
                        <a href="/cart" class="btn btn-dark">Review Basket</a>
                        <a href="/support" class="btn btn-outline">Contact Sales Team</a>
                    </div>
                </section>

            </div><!-- /sp-content-sections -->

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
.sp-specs-tab-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.sp-specs-tab-table th,
.sp-specs-tab-table td { padding:9px 12px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top; }
.sp-specs-tab-table th { font-weight:700; background:#f9fafb; width:38%; }
.sp-specs-tab-table tr:last-child th,
.sp-specs-tab-table tr:last-child td { border-bottom:none; }

/* Content sections */
.sp-content-sections { display:flex; flex-direction:column; gap:0; }
.sp-content-block {
    padding:24px 0;
    border-bottom:1px solid #f0f0f0;
}
.sp-content-block:last-child { border-bottom:none; }
.sp-section-heading {
    font-size:15px;
    font-weight:700;
    color:#111;
    margin-bottom:14px;
    padding-bottom:8px;
    border-bottom:2px solid #f59b23;
    display:inline-block;
}
.sp-desc-body { font-size:14px; line-height:1.75; color:#444; }
.sp-compat-intro { font-size:13px; color:#6b7280; margin-bottom:10px; }
.sp-compat-list, .sp-app-list, .sp-why-list {
    list-style:none; padding:0; margin:0 0 12px;
    display:flex; flex-direction:column; gap:8px;
}
.sp-compat-list li, .sp-app-list li {
    font-size:13.5px; color:#374151;
    padding-left:20px; position:relative;
}
.sp-compat-list li::before, .sp-app-list li::before {
    content:'›'; position:absolute; left:0; color:#f59b23; font-weight:700;
}
.sp-why-list li {
    font-size:13.5px; color:#374151;
    padding:10px 10px 10px 38px; position:relative;
    background:#fafafa; border-radius:6px;
    border-left:3px solid #f59b23;
}
.sp-how-list {
    padding-left:20px; margin:0 0 18px;
    display:flex; flex-direction:column; gap:10px;
}
.sp-how-list li { font-size:13.5px; color:#374151; line-height:1.6; }
.sp-app-links { font-size:13px; color:#6b7280; margin-top:12px; }
.sp-app-links a { color:#f59b23; text-decoration:none; font-weight:600; }
.sp-app-links a:hover { text-decoration:underline; }

/* Quote details/summary */
.sp-quote-details { margin-top:16px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
.sp-quote-summary {
    padding:12px 16px;
    font-size:13.5px; font-weight:600; color:#374151;
    cursor:pointer; background:#f9fafb;
    list-style:none;
    display:flex; align-items:center; gap:8px;
}
.sp-quote-summary::before { content:'＋'; color:#f59b23; font-weight:700; }
.sp-quote-details[open] .sp-quote-summary::before { content:'－'; }
.sp-quote-form-inline { padding:16px; }
</style>

<script>
var _spMoq = <?= (int) $moq ?>;
function spAdjQty(delta) {
    var input = document.getElementById('sp-qty');
    if (!input) { return; }
    var v = parseInt(input.value, 10) || _spMoq;
    v = Math.max(_spMoq, v + delta);
    input.value = v;
}
</script>
