<?php
$pageTitle     = $product ? 'Edit Product' : 'Add Product';
$activeNav     = 'product-editor';
$mastheadLabel = 'Product Editor';
$mastheadTitle = $product ? 'Edit product record' : 'New product record';
$mastheadText  = 'Maintain product metadata, storefront visibility, and catalogue details. Category and SKU are bound to the registry.';

$isEdit     = $product !== null;
$productId  = $isEdit ? (int) $product['id'] : 0;
$formAction = $isEdit ? '/admin/products/' . $productId : '/admin/products';

// Resolve initial specs for the key-value editor
$specsInit = [];
$specsOld  = old('specifications', '');
if ($specsOld !== '') {
    $specsInit = json_decode((string) $specsOld, true) ?: [];
} elseif (! empty($product['specifications'])) {
    $specsInit = json_decode((string) $product['specifications'], true) ?: [];
}
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/categories" class="btn btn-outline">Manage Categories</a>
    <a href="/admin/products/bulk" class="btn btn-outline">Bulk Upload</a>
    <a href="/admin/products" class="btn btn-dark">&#8592; All Products</a>
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
            <h2><?= $isEdit ? 'Edit: ' . esc($product['name']) : 'Create new product' ?></h2>
            <p>All fields except Name and Category are optional. SKU and Slug are auto-generated if left blank.</p>
        </div>
        <?php if ($isEdit): ?>
            <a href="/e-shop/product/<?= esc($product['slug']) ?>" target="_blank"
               class="btn btn-outline" style="font-size:12px;padding:8px 14px">
                Preview &#8599;
            </a>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= esc($formAction) ?>" id="product-form" class="admin-form-grid" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- ── Name ── -->
        <div class="form-group form-full">
            <label for="pf-name">Name <span style="color:#e53e3e">*</span></label>
            <input id="pf-name" type="text" name="name"
                   value="<?= esc(old('name', $product['name'] ?? '')) ?>"
                   placeholder="e.g. Hydraulic Pump Service Kit" required>
        </div>

        <!-- ── Slug ── -->
        <div class="form-group form-full">
            <label for="pf-slug">
                URL Slug
                <span class="admin-badge admin-badge--muted" style="margin-left:6px">auto-generated</span>
            </label>
            <input id="pf-slug" type="text" name="slug"
                   value="<?= esc(old('slug', $product['slug'] ?? '')) ?>"
                   placeholder="hydraulic-pump-service-kit">
            <p class="adm-field-hint">Filled automatically from the product name. Edit if you need a different URL.</p>
        </div>

        <!-- ── Category ── -->
        <?php
        $preselectedCategoryId = (int) old('category_id', (string) ($product['category_id'] ?? 0));
        ?>
        <?php if (! empty($categories)): ?>
        <div class="form-group">
            <label for="pf-category">Category <span style="color:#e53e3e">*</span></label>
            <div class="adm-select-row">
                <select id="pf-category" name="category_id" required>
                    <option value="">— Select a category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc((string) $cat['id']) ?>"
                            <?= $preselectedCategoryId === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= esc($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="new-cat-toggle" class="btn btn-outline" style="flex-shrink:0;white-space:nowrap">
                    + New category
                </button>
            </div>
        </div>
        <input type="hidden" name="category"
               value="<?= esc(old('category', $product['category'] ?? '')) ?>">
        <?php else: ?>
        <div class="form-group">
            <label for="pf-category-text">Category <span style="color:#e53e3e">*</span></label>
            <input id="pf-category-text" type="text" name="category"
                   value="<?= esc(old('category', $product['category'] ?? '')) ?>"
                   placeholder="e.g. Spare Parts" required>
            <p class="adm-field-hint">
                &#9888; Categories registry not set up yet &mdash; typing free text for now.
                Run <code style="background:var(--adm-bg);padding:1px 5px;border-radius:4px">php spark migrate</code>
                then <a href="/admin/categories" style="color:var(--adm-orange)">create categories</a>
                to use the dropdown.
            </p>
        </div>
        <?php endif; ?>

        <!-- ── SKU ── -->
        <div class="form-group">
            <label for="pf-sku">
                SKU
                <span class="admin-badge admin-badge--muted" style="margin-left:6px">optional</span>
            </label>
            <div class="adm-select-row">
                <input id="pf-sku" type="text" name="sku"
                       value="<?= esc(old('sku', $product['sku'] ?? '')) ?>"
                       placeholder="BSAS-SPA-0001"
                       autocomplete="off">
                <button type="button" id="gen-sku-btn" class="btn btn-outline" style="flex-shrink:0;white-space:nowrap"
                        title="Generate SKU from selected category">
                    &#9881; Generate
                </button>
            </div>
            <p class="adm-field-hint">
                Auto-generate a unique code from the selected category, or type your own.
                <span id="sku-status" style="margin-left:4px"></span>
            </p>
        </div>

        <!-- ── Part Number ── -->
        <div class="form-group">
            <label for="pf-partno">
                Part Number
                <span class="admin-badge admin-badge--muted" style="margin-left:6px">optional</span>
            </label>
            <input id="pf-partno" type="text" name="part_number"
                   value="<?= esc(old('part_number', $product['part_number'] ?? '')) ?>"
                   placeholder="e.g. HP-3080-OEM, AT123456"
                   autocomplete="off">
            <p class="adm-field-hint">OEM or manufacturer part number. Shown on the product page and included in quote submissions.</p>
        </div>

        <!-- ── Product Image ── -->
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image_file" id="pf-image-file" accept="image/*"
                   onchange="previewUpload(this)" style="margin-bottom:8px">
            <p class="adm-field-hint" style="margin:0 0 6px">Or enter an existing image URL:</p>
            <input id="pf-image" type="text" name="image_url"
                   value="<?= esc(old('image_url', $product['image_url'] ?? '')) ?>"
                   placeholder="/assets/images/sparePart.webp"
                   oninput="previewImage(this.value)">
        </div>

        <!-- ── Price Label ── -->
        <div class="form-group">
            <label for="pf-price">Price Label</label>
            <input id="pf-price" type="text" name="price_label"
                   value="<?= esc(old('price_label', $product['price_label'] ?? '')) ?>"
                   placeholder="Quote on request">
        </div>

        <!-- ── Sort Order ── -->
        <div class="form-group">
            <label for="pf-sort">Sort Order</label>
            <input id="pf-sort" type="number" name="sort_order"
                   value="<?= esc(old('sort_order', (string) ($product['sort_order'] ?? 0))) ?>"
                   min="0" step="1">
            <p class="adm-field-hint">Lower number = appears first in the catalogue.</p>
        </div>

        <!-- ── Status ── -->
        <div class="form-group">
            <label for="pf-status">Visibility</label>
            <select id="pf-status" name="is_active">
                <option value="1" <?= old('is_active', (string) ($product['is_active'] ?? '1')) === '1' ? 'selected' : '' ?>>
                    &#128994; Active — visible in e-shop
                </option>
                <option value="0" <?= old('is_active', (string) ($product['is_active'] ?? '1')) === '0' ? 'selected' : '' ?>>
                    &#9899; Hidden — not visible
                </option>
            </select>
        </div>

        <!-- ── Short Description ── -->
        <div class="form-group form-full">
            <label for="pf-short">Short Description <span class="admin-badge admin-badge--muted" style="margin-left:4px">max 2000 chars</span></label>
            <textarea id="pf-short" name="short_description"
                      maxlength="2000"
                      placeholder="One or two sentences displayed in catalogue cards."><?= esc(old('short_description', $product['short_description'] ?? '')) ?></textarea>
            <p id="short-count" class="adm-field-hint" style="text-align:right"></p>
        </div>

        <!-- ── Description ── -->
        <div class="form-group form-full">
            <label for="pf-desc">Full Description</label>
            <textarea id="pf-desc" name="description" style="min-height:130px"
                      placeholder="Detailed product description shown on the product page."><?= esc(old('description', $product['description'] ?? '')) ?></textarea>
        </div>

        <!-- ── Pricing ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Pricing</h3>
            <p class="adm-field-hint" style="margin:4px 0 0">Set a real price to make this product orderable through checkout. Leave at 0 to keep it quote-only.</p>
        </div>

        <div class="form-group">
            <label for="pf-real-price">Price</label>
            <input id="pf-real-price" type="number" step="0.01" min="0" name="price"
                   value="<?= esc(old('price', (string) ($product['price'] ?? '0'))) ?>">
        </div>

        <div class="form-group">
            <label for="pf-compare-price">Compare-at Price</label>
            <input id="pf-compare-price" type="number" step="0.01" min="0" name="compare_at_price"
                   value="<?= esc(old('compare_at_price', (string) ($product['compare_at_price'] ?? ''))) ?>"
                   placeholder="Optional — shown as a strikethrough MRP">
        </div>

        <div class="form-group">
            <label for="pf-currency">Currency</label>
            <input id="pf-currency" type="text" name="currency" maxlength="3"
                   value="<?= esc(old('currency', (string) ($product['currency'] ?? 'INR'))) ?>">
        </div>

        <div class="form-group">
            <label for="pf-tax-rate">Tax Rate (%)</label>
            <input id="pf-tax-rate" type="number" step="0.01" min="0" name="tax_rate"
                   value="<?= esc(old('tax_rate', (string) ($product['tax_rate'] ?? '0'))) ?>"
                   placeholder="e.g. 18 for GST">
        </div>

        <div class="form-group">
            <label for="pf-stock-qty">Stock Quantity</label>
            <input id="pf-stock-qty" type="number" step="1" min="0" name="stock_quantity"
                   value="<?= esc(old('stock_quantity', (string) ($product['stock_quantity'] ?? '0'))) ?>">
            <p class="adm-field-hint">Decremented automatically when a customer places an order.</p>
        </div>

        <!-- ── Availability & Stock ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Availability &amp; Stock</h3>
        </div>

        <div class="form-group">
            <label for="pf-stock">Stock Status</label>
            <select id="pf-stock" name="stock_status">
                <?php
                $stockOptions = [
                    'in_stock'      => '🟢 In Stock',
                    'made_to_order' => '🟡 Made to Order',
                    'out_of_stock'  => '🔴 Out of Stock',
                ];
                $currentStock = old('stock_status', $product['stock_status'] ?? 'in_stock');
                foreach ($stockOptions as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= $currentStock === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="pf-lead">Lead Time</label>
            <input id="pf-lead" type="text" name="lead_time"
                   value="<?= esc(old('lead_time', $product['lead_time'] ?? '')) ?>"
                   placeholder="e.g. Ex-stock, 2–4 weeks, 8–12 weeks">
        </div>

        <div class="form-group">
            <label for="pf-moq">Min. Order Qty</label>
            <input id="pf-moq" type="number" name="min_order_qty" min="1"
                   value="<?= esc(old('min_order_qty', (string) ($product['min_order_qty'] ?? 1))) ?>">
            <p class="adm-field-hint">Default 1. Raise for bulk-only products.</p>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600">
                <input type="checkbox" name="is_featured" value="1"
                       style="width:18px;height:18px;accent-color:var(--adm-orange)"
                       <?= old('is_featured', (string) ($product['is_featured'] ?? 0)) === '1' ? 'checked' : '' ?>>
                Featured product
            </label>
            <p class="adm-field-hint">Featured products can be highlighted in catalogue promotions.</p>
        </div>

        <!-- ── Technical Details ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Technical Details</h3>
        </div>

        <div class="form-group">
            <label for="pf-weight">Weight</label>
            <input id="pf-weight" type="text" name="weight"
                   value="<?= esc(old('weight', $product['weight'] ?? '')) ?>"
                   placeholder="e.g. 2.5 kg, 450 g">
        </div>

        <div class="form-group">
            <label for="pf-dims">Dimensions</label>
            <input id="pf-dims" type="text" name="dimensions"
                   value="<?= esc(old('dimensions', $product['dimensions'] ?? '')) ?>"
                   placeholder="e.g. 120 × 80 × 40 mm">
        </div>

        <div class="form-group form-full">
            <label for="pf-material">Material</label>
            <input id="pf-material" type="text" name="material"
                   value="<?= esc(old('material', $product['material'] ?? '')) ?>"
                   placeholder="e.g. Alloy Steel, Stainless Steel 316">
        </div>

        <!-- ── Compatible Vehicles ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Compatible Vehicles</h3>
            <p class="adm-field-hint" style="margin:4px 0 0">Select every vehicle model this product fits, grouped by OEM. Shown as a Compatibility section on the product page, and used by the storefront's vehicle filter.</p>
        </div>

        <div class="form-full">
            <?php if (empty($vehiclesByOem)): ?>
                <p class="adm-field-hint">No vehicles registered yet. <a href="/admin/vehicles" style="color:var(--adm-orange)">Add vehicles</a> to enable compatibility selection.</p>
            <?php else: ?>
                <?php foreach ($vehiclesByOem as $oemName => $vehiclesForOem): ?>
                    <div style="margin-bottom:14px">
                        <p style="font-size:12px;font-weight:800;color:var(--adm-navy);text-transform:uppercase;letter-spacing:.5px;margin:0 0 8px"><?= esc($oemName) ?></p>
                        <div style="display:flex;flex-wrap:wrap;gap:8px 18px">
                            <?php foreach ($vehiclesForOem as $vehicle): ?>
                                <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--adm-text-2);cursor:pointer">
                                    <input type="checkbox" name="vehicle_ids[]" value="<?= esc((string) $vehicle['id']) ?>"
                                           style="width:16px;height:16px;accent-color:var(--adm-orange)"
                                           <?= in_array($vehicle['id'], old('vehicle_ids', $productVehicleIds ?? [])) ? 'checked' : '' ?>>
                                    <?= esc($vehicle['name']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── Labels ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Labels</h3>
            <p class="adm-field-hint" style="margin:4px 0 0">Free-form merchandising tags (e.g. New Arrival, Best Seller). Shown as badges and used by the storefront's label filter.</p>
        </div>

        <div class="form-full">
            <?php if (empty($labels)): ?>
                <p class="adm-field-hint">No labels registered yet. <a href="/admin/labels" style="color:var(--adm-orange)">Add labels</a> to enable tagging.</p>
            <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px 18px">
                    <?php foreach ($labels as $label): ?>
                        <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--adm-text-2);cursor:pointer">
                            <input type="checkbox" name="label_ids[]" value="<?= esc((string) $label['id']) ?>"
                                   style="width:16px;height:16px;accent-color:var(--adm-orange)"
                                   <?= in_array($label['id'], old('label_ids', $productLabelIds ?? [])) ? 'checked' : '' ?>>
                            <?= esc($label['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Documents ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Documents</h3>
        </div>

        <div class="form-group form-full">
            <label for="pf-datasheet">Datasheet URL</label>
            <input id="pf-datasheet" type="text" name="datasheet_url"
                   value="<?= esc(old('datasheet_url', $product['datasheet_url'] ?? '')) ?>"
                   placeholder="https:// or /assets/docs/product-name.pdf">
            <p class="adm-field-hint">PDF or web link. Shown as a download button on the product page.</p>
        </div>

        <!-- ── Technical Specifications ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">Technical Specifications</h3>
            <p class="adm-field-hint" style="margin:4px 0 0">Key-value pairs shown as a structured spec table on the product page.</p>
        </div>

        <div class="form-full">
            <div id="specs-rows"></div>
            <button type="button" id="add-spec-row" class="btn btn-outline"
                    style="font-size:13px;padding:8px 16px;margin-top:4px">
                &#43; Add Specification Row
            </button>
            <input type="hidden" name="specifications" id="specs-json-input">
        </div>

        <!-- ── SEO & Structured Data ── -->
        <div class="form-full adm-section-divider">
            <h3 class="adm-section-head">SEO &amp; Structured Data</h3>
            <p class="adm-field-hint" style="margin:4px 0 0">All fields optional. Leave blank to fall back to auto-generated titles/descriptions from the product name and description.</p>
        </div>

        <div class="form-group form-full">
            <label for="pf-meta-title">Meta Title <span class="admin-badge admin-badge--muted" style="margin-left:4px">max 160 chars</span></label>
            <input id="pf-meta-title" type="text" name="meta_title" maxlength="160"
                   value="<?= esc(old('meta_title', $product['meta_title'] ?? '')) ?>"
                   placeholder="Overrides the auto-generated page <title>">
            <p id="meta-title-count" class="adm-field-hint" style="text-align:right"></p>
        </div>

        <div class="form-group form-full">
            <label for="pf-meta-desc">Meta Description <span class="admin-badge admin-badge--muted" style="margin-left:4px">max 300 chars</span></label>
            <textarea id="pf-meta-desc" name="meta_description" maxlength="300"
                      placeholder="Overrides the auto-generated meta description"><?= esc(old('meta_description', $product['meta_description'] ?? '')) ?></textarea>
            <p id="meta-desc-count" class="adm-field-hint" style="text-align:right"></p>
        </div>

        <div class="form-group">
            <label for="pf-meta-keyword">Search Keywords</label>
            <input id="pf-meta-keyword" type="text" name="meta_keyword"
                   value="<?= esc(old('meta_keyword', $product['meta_keyword'] ?? '')) ?>"
                   placeholder="comma, separated, keywords">
            <p class="adm-field-hint">Used to match this product in on-site search (admin and storefront). Not emitted as an SEO meta tag — search engines ignore that anyway.</p>
        </div>

        <div class="form-group">
            <label for="pf-focus-keyword">Focus Keyword</label>
            <input id="pf-focus-keyword" type="text" name="focus_keyword"
                   value="<?= esc(old('focus_keyword', $product['focus_keyword'] ?? '')) ?>"
                   placeholder="e.g. hydraulic pump seal kit">
            <p class="adm-field-hint">Internal reference for the term this page targets &mdash; not shown publicly.</p>
        </div>

        <div class="form-group">
            <label for="pf-image-alt">Image Alt Text</label>
            <input id="pf-image-alt" type="text" name="image_alt_text"
                   value="<?= esc(old('image_alt_text', $product['image_alt_text'] ?? '')) ?>"
                   placeholder="Falls back to the product name">
        </div>

        <div class="form-group">
            <label for="pf-og-image">OG Image URL</label>
            <input id="pf-og-image" type="text" name="og_image"
                   value="<?= esc(old('og_image', $product['og_image'] ?? '')) ?>"
                   placeholder="Falls back to the product image">
        </div>

        <div class="form-group form-full">
            <label for="pf-canonical">Canonical URL</label>
            <input id="pf-canonical" type="text" name="canonical_url"
                   value="<?= esc(old('canonical_url', $product['canonical_url'] ?? '')) ?>"
                   placeholder="Leave blank to auto-generate from the product page URL">
        </div>

        <div class="form-group">
            <label for="pf-structured-type">Structured Data Type</label>
            <input id="pf-structured-type" type="text" name="structured_data_type"
                   value="<?= esc(old('structured_data_type', $product['structured_data_type'] ?? 'Product')) ?>"
                   placeholder="Product">
            <p class="adm-field-hint">Schema.org @type used in the product page's JSON-LD.</p>
        </div>

        <div class="form-group">
            <label for="pf-robots">Robots Meta</label>
            <?php $currentRobots = old('robots_meta', $product['robots_meta'] ?? 'index, follow'); ?>
            <select id="pf-robots" name="robots_meta">
                <?php foreach (['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $robotsOption): ?>
                    <option value="<?= esc($robotsOption) ?>" <?= $currentRobots === $robotsOption ? 'selected' : '' ?>><?= esc($robotsOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- ── Image preview ── -->
        <div class="form-full" id="img-preview-wrap" style="display:none">
            <label>Image Preview</label>
            <div style="width:200px;height:150px;border-radius:12px;border:1.5px solid var(--adm-border);overflow:hidden;background:var(--adm-bg)">
                <img id="img-preview" src="" alt="preview"
                     style="width:100%;height:100%;object-fit:cover">
            </div>
        </div>

        <!-- ── Inline new category ── -->
        <div class="form-full" id="new-cat-row" style="display:none">
            <div class="adm-new-cat-card">
                <p class="adm-new-cat-title">&#43; Add new category</p>
                <div class="adm-select-row" style="max-width:480px">
                    <input type="text" id="new-cat-name" placeholder="Category name (e.g. Hydraulic Systems)"
                           autocomplete="off">
                    <button type="button" id="new-cat-save" class="btn" style="flex-shrink:0">Save</button>
                    <button type="button" id="new-cat-cancel" class="btn btn-outline" style="flex-shrink:0">Cancel</button>
                </div>
                <p id="new-cat-msg" class="adm-field-hint" style="margin-top:8px"></p>
            </div>
        </div>

        <!-- ── Submit ── -->
        <div class="form-full" style="margin-top:8px">
            <button type="submit" class="btn">
                <?= $isEdit ? '&#10003; Update Product' : '&#43; Create Product' ?>
            </button>
        </div>
    </form>

    <?php if ($isEdit): ?>
    <form method="post"
          action="/admin/products/<?= esc((string) $productId) ?>/delete"
          style="margin-top:12px"
          onsubmit="return confirm('Delete this product? This cannot be undone.')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline" style="border-color:#fca5a5;color:#dc2626">
            &#10005; Delete Product
        </button>
    </form>
    <?php endif; ?>

</section>

<style>
.adm-select-row { display:flex; gap:10px; align-items:center; }
.adm-select-row select,
.adm-select-row input { flex:1; }
.adm-field-hint { font-size:12px; color:var(--adm-faint); margin:5px 0 0; line-height:1.55; }
.adm-new-cat-card {
    background:var(--adm-bg);
    border:1.5px solid var(--adm-border);
    border-radius:12px;
    padding:18px 20px;
}
.adm-new-cat-title { font-size:13px; font-weight:800; color:var(--adm-navy); margin-bottom:12px; }
.adm-section-head { font-size:14px; font-weight:800; color:var(--adm-navy); margin:0 0 4px; letter-spacing:.3px; }
.adm-section-divider { padding-top:24px; border-top:1.5px solid var(--adm-border); margin-top:8px !important; }
</style>

<script>
(function () {

    /* ── Slug auto-generation ── */
    var nameEl    = document.getElementById('pf-name');
    var slugEl    = document.getElementById('pf-slug');
    var slugEdited = slugEl.value !== '';

    function toSlug(str) {
        return str.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s]+/g, '-')
            .replace(/-+/g, '-');
    }

    nameEl.addEventListener('input', function () {
        if (!slugEdited) { slugEl.value = toSlug(this.value); }
    });
    slugEl.addEventListener('input', function () {
        slugEdited = this.value !== '';
    });

    /* ── SKU generation ── */
    var skuEl     = document.getElementById('pf-sku');
    var catEl     = document.getElementById('pf-category');
    var skuStatus = document.getElementById('sku-status');
    var genBtn    = document.getElementById('gen-sku-btn');

    genBtn.addEventListener('click', function () {
        var catId = catEl ? catEl.value : '';
        if (!catId) {
            skuStatus.textContent = '⚠ Select a category first.';
            skuStatus.style.color = '#dc2626';
            return;
        }
        genBtn.disabled = true;
        skuStatus.textContent = 'Generating…';
        skuStatus.style.color = 'var(--adm-faint)';
        fetch('/admin/sku/suggest?category_id=' + encodeURIComponent(catId))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                skuEl.value = data.sku || '';
                skuStatus.textContent = '✓ ' + (data.sku || '');
                skuStatus.style.color = '#15803d';
            })
            .catch(function () {
                skuStatus.textContent = 'Error — try again.';
                skuStatus.style.color = '#dc2626';
            })
            .finally(function () { genBtn.disabled = false; });
    });

    /* ── New category inline ── */
    var toggleBtn  = document.getElementById('new-cat-toggle');
    var newCatRow  = document.getElementById('new-cat-row');
    var newCatName = document.getElementById('new-cat-name');
    var saveBtn    = document.getElementById('new-cat-save');
    var cancelBtn  = document.getElementById('new-cat-cancel');
    var catMsg     = document.getElementById('new-cat-msg');

    if (!toggleBtn) { return; }

    toggleBtn.addEventListener('click', function () {
        newCatRow.style.display = newCatRow.style.display === 'none' ? 'block' : 'none';
        if (newCatRow.style.display !== 'none') { newCatName.focus(); }
    });

    cancelBtn.addEventListener('click', function () {
        newCatRow.style.display = 'none';
        newCatName.value = '';
        catMsg.textContent = '';
    });

    saveBtn.addEventListener('click', function () {
        var name = newCatName.value.trim();
        if (!name) {
            catMsg.textContent = 'Enter a category name.';
            catMsg.style.color = '#dc2626';
            return;
        }
        saveBtn.disabled = true;
        catMsg.textContent = 'Saving…';
        catMsg.style.color = 'var(--adm-faint)';

        var form = new FormData();
        form.append('name', name);
        var csrfInput = document.querySelector('input[name^="csrf_"]');
        if (csrfInput) { form.append(csrfInput.name, csrfInput.value); }

        fetch('/admin/categories', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    catMsg.textContent = data.error;
                    catMsg.style.color = '#dc2626';
                    return;
                }
                var exists = catEl.querySelector('option[value="' + data.id + '"]');
                if (!exists) {
                    var opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.name;
                    catEl.appendChild(opt);
                }
                catEl.value = data.id;
                catMsg.textContent = data.exists ? 'Category already existed — selected.' : '✓ Category created and selected.';
                catMsg.style.color = '#15803d';
                newCatName.value = '';
                setTimeout(function () { newCatRow.style.display = 'none'; catMsg.textContent = ''; }, 1500);
            })
            .catch(function () {
                catMsg.textContent = 'Error — try again.';
                catMsg.style.color = '#dc2626';
            })
            .finally(function () { saveBtn.disabled = false; });
    });

    /* ── Image preview ── */
    window.previewImage = function (url) {
        var wrap = document.getElementById('img-preview-wrap');
        var img  = document.getElementById('img-preview');
        if (url && url.trim() !== '') {
            img.src = url.trim();
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }
    };

    window.previewUpload = function (input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var wrap = document.getElementById('img-preview-wrap');
                var img  = document.getElementById('img-preview');
                img.src = e.target.result;
                wrap.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
            document.getElementById('pf-image').value = '';
        }
    };

    var imgInput = document.getElementById('pf-image');
    if (imgInput && imgInput.value) { previewImage(imgInput.value); }

    /* ── Short description counter ── */
    var shortEl  = document.getElementById('pf-short');
    var countEl  = document.getElementById('short-count');
    function updateCount() {
        var len = shortEl.value.length;
        countEl.textContent = len + ' / 2000';
        countEl.style.color = len > 1800 ? '#dc2626' : 'var(--adm-faint)';
    }
    shortEl.addEventListener('input', updateCount);
    updateCount();

    /* ── SEO field counters ── */
    function bindCounter(fieldId, countId, max) {
        var field = document.getElementById(fieldId);
        var count = document.getElementById(countId);
        if (!field || !count) { return; }
        function update() {
            var len = field.value.length;
            count.textContent = len + ' / ' + max;
            count.style.color = len > max * 0.9 ? '#dc2626' : 'var(--adm-faint)';
        }
        field.addEventListener('input', update);
        update();
    }
    bindCounter('pf-meta-title', 'meta-title-count', 160);
    bindCounter('pf-meta-desc', 'meta-desc-count', 300);

})();

/* ── Specifications key-value editor ── */
(function () {
    var existingSpecs = <?= json_encode($specsInit) ?>;
    var container = document.getElementById('specs-rows');
    var jsonInput = document.getElementById('specs-json-input');
    var addBtn    = document.getElementById('add-spec-row');

    function htmlEsc(str) {
        var d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function serialize() {
        var rows = container.querySelectorAll('.adm-spec-row');
        var data = [];
        rows.forEach(function (row) {
            var k = row.querySelector('.adm-spec-key').value.trim();
            var v = row.querySelector('.adm-spec-val').value.trim();
            if (k !== '') { data.push({ key: k, value: v }); }
        });
        jsonInput.value = data.length > 0 ? JSON.stringify(data) : '';
    }

    function createRow(key, value) {
        var row = document.createElement('div');
        row.className = 'adm-spec-row';
        row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px';
        row.innerHTML =
            '<input type="text" class="adm-spec-key" placeholder="Property (e.g. Weight)" value="' + htmlEsc(key || '') + '" style="flex:1;min-width:0">' +
            '<input type="text" class="adm-spec-val" placeholder="Value (e.g. 2.5 kg)" value="' + htmlEsc(value || '') + '" style="flex:1.5;min-width:0">' +
            '<button type="button" class="btn btn-outline adm-spec-rm" style="flex-shrink:0;padding:8px 12px;color:#dc2626;border-color:#fca5a5" title="Remove row">&#10005;</button>';
        row.querySelector('.adm-spec-rm').addEventListener('click', function () {
            row.remove();
            serialize();
        });
        row.querySelectorAll('input').forEach(function (inp) {
            inp.addEventListener('input', serialize);
        });
        container.appendChild(row);
    }

    existingSpecs.forEach(function (s) { createRow(s.key || '', s.value || ''); });
    serialize();

    addBtn.addEventListener('click', function () { createRow('', ''); });

    document.getElementById('product-form').addEventListener('submit', serialize);
})();
</script>
<?php $this->endSection() ?>
