<?php
$pageTitle = 'Lead Inbox';
$activeNav = 'leads';
$mastheadLabel = 'Lead Backend';
$mastheadTitle = 'Quote and enquiry management';
$mastheadText = 'Review buyer intent, track incoming requirements, and inspect requested line items from one consistent inbox.';
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin" class="btn btn-outline">Dashboard</a>
    <a href="/admin/products/bulk" class="btn">Bulk Upload</a>
    <a href="/admin/products/new" class="btn btn-dark">Add Product</a>
<?php $this->endSection() ?>

<?= $this->section('content') ?>
    <section class="admin-summary-grid">
        <article class="admin-summary-card">
            <span>Product RFQ</span>
            <strong><?= esc((string) $quoteBreakdown['product']) ?></strong>
            <p>Single-product and direct listing quote requests.</p>
        </article>
        <article class="admin-summary-card admin-summary-card--accent">
            <span>Cart RFQ</span>
            <strong><?= esc((string) $quoteBreakdown['cart']) ?></strong>
            <p>Requests submitted through the basket workflow.</p>
        </article>
        <article class="admin-summary-card">
            <span>Support Leads</span>
            <strong><?= esc((string) $quoteBreakdown['support']) ?></strong>
            <p>Technical or operational support enquiries.</p>
        </article>
        <article class="admin-summary-card">
            <span>Other</span>
            <strong><?= esc((string) ($quoteBreakdown['other'] ?? 0)) ?></strong>
            <p>Traffic that did not match the main enquiry routes.</p>
        </article>
    </section>

    <section class="admin-stack">
        <section class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Quote Requests</h2>
                    <p>Review incoming enquiries with customer context and requested line items.</p>
                </div>
            </div>

            <form method="get" action="/admin/leads" class="admin-filters">
                <input type="search" name="q" value="<?= esc($leadSearch) ?>" placeholder="Search by name, company, phone, email, or concern">
                <select name="type">
                    <option value="">All request types</option>
                    <option value="product" <?= $quoteType === 'product' ? 'selected' : '' ?>>Product</option>
                    <option value="cart" <?= $quoteType === 'cart' ? 'selected' : '' ?>>Cart</option>
                    <option value="support" <?= $quoteType === 'support' ? 'selected' : '' ?>>Support</option>
                </select>
                <button type="submit" class="btn btn-dark">Apply</button>
                <a href="/admin/leads" class="btn btn-outline">Reset</a>
            </form>

            <div class="admin-card-grid">
                <?php if ($quotes === []): ?>
                    <div class="empty-state empty-state--compact">
                        <h3>No quote requests found.</h3>
                        <p>Adjust the search or request-type filter to broaden the result set.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($quotes as $quote): ?>
                        <article class="admin-list-card admin-list-card--full">
                            <div class="admin-list-card-head">
                                <div>
                                    <strong><?= esc($quote['name']) ?></strong>
                                    <div class="admin-row-meta"><?= esc($quote['company'] ?: 'No company provided') ?></div>
                                </div>
                                <span class="admin-badge"><?= esc(ucfirst($quote['request_type'])) ?></span>
                            </div>
                            <div class="admin-chip-row">
                                <span><?= esc($quote['phone']) ?></span>
                                <span><?= esc($quote['email'] ?: 'No email address') ?></span>
                                <span><?= esc($quote['designation'] ?: 'No designation') ?></span>
                                <span><?= esc((string) $quote['created_at']) ?></span>
                            </div>
                            <div class="admin-detail-grid">
                                <div>
                                    <span class="admin-label">Concern</span>
                                    <p><?= esc($quote['concern'] ?: $quote['source_page']) ?></p>
                                </div>
                                <div>
                                    <span class="admin-label">Message</span>
                                    <p><?= esc($quote['message'] ?: 'No additional message provided.') ?></p>
                                </div>
                            </div>
                            <?php $items = $quoteItemsByRequest[(int) $quote['id']] ?? []; ?>
                            <div>
                                <span class="admin-label">Requested Items</span>
                                <?php if ($items === []): ?>
                                    <p class="admin-row-meta">No line items attached to this enquiry.</p>
                                <?php else: ?>
                                    <div class="admin-mini-list">
                                        <?php foreach ($items as $item): ?>
                                            <div class="admin-mini-list-item">
                                                <div>
                                                    <span><?= esc($item['product_name']) ?></span>
                                                    <div class="admin-row-meta"><?= esc($item['sku'] ?: 'SKU not captured') ?></div>
                                                </div>
                                                <strong>Qty <?= esc((string) $item['quantity']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head">
                <div>
                    <h2>Brochure Leads</h2>
                    <p>Direct downloads captured through the brochure request modal.</p>
                </div>
            </div>
            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Mobile</th>
                        <th>Source</th>
                        <th>IP</th>
                        <th>Created At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($brochureLeads as $lead): ?>
                        <tr>
                            <td><?= esc($lead['mobile']) ?></td>
                            <td><?= esc($lead['source']) ?></td>
                            <td><?= esc($lead['ip_address']) ?></td>
                            <td><?= esc((string) $lead['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
<?php $this->endSection() ?>
