<?php
$pageTitle = 'Order ' . $order['order_number'];
$activeNav = 'order-detail';
$mastheadLabel = 'Order Detail';
$mastheadTitle = $order['order_number'];
$mastheadText = 'Review line items, update fulfilment status, and record tracking details.';

$statusLabels = [
    'pending'    => 'Pending',
    'confirmed'  => 'Confirmed',
    'processing' => 'Processing',
    'shipped'    => 'Shipped',
    'delivered'  => 'Delivered',
    'cancelled'  => 'Cancelled',
];
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('toolbar') ?>
    <a href="/admin/orders" class="btn btn-outline">&#8592; All Orders</a>
<?php $this->endSection() ?>

<?= $this->section('beforeContent') ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="sp-flash-ok" style="margin-bottom:16px"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="error-banner"><?= esc(implode(' ', session()->getFlashdata('errors'))) ?></div>
    <?php endif; ?>
<?php $this->endSection() ?>

<?= $this->section('content') ?>
<section class="admin-panel admin-panel--primary">
    <div class="admin-panel-head">
        <div>
            <h2>Order <?= esc($order['order_number']) ?></h2>
            <p>Placed <?= esc((string) $order['created_at']) ?> &middot; Status: <span class="admin-badge"><?= esc($statusLabels[$order['status']] ?? $order['status']) ?></span></p>
        </div>
    </div>

    <div class="admin-detail-grid">
        <div>
            <span class="admin-label">Customer</span>
            <p><?= esc($customer['name'] ?? 'Unknown') ?><br><?= esc($customer['email'] ?? '') ?></p>
        </div>
        <div>
            <span class="admin-label">Ship To</span>
            <p>
                <?= esc($order['shipping_name']) ?> &middot; <?= esc($order['shipping_phone']) ?><br>
                <?= esc($order['shipping_address_line1']) ?><?= $order['shipping_address_line2'] ? ', ' . esc($order['shipping_address_line2']) : '' ?><br>
                <?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_state']) ?> <?= esc($order['shipping_postal_code']) ?>
            </p>
        </div>
        <div>
            <span class="admin-label">Payment</span>
            <p><?= esc(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?> &middot; <?= esc(ucfirst($order['payment_status'])) ?></p>
        </div>
        <div>
            <span class="admin-label">Total</span>
            <p><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?>
                <span class="admin-row-meta">(subtotal <?= esc(number_format((float) $order['subtotal'], 2)) ?> + tax <?= esc(number_format((float) $order['tax_total'], 2)) ?>)</span>
            </p>
        </div>
    </div>

    <div class="admin-table-shell" style="margin-top:20px">
        <table class="admin-table">
            <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= esc($item['product_name']) ?></td>
                    <td><?= esc($item['sku'] ?: '—') ?></td>
                    <td><?= esc((string) $item['quantity']) ?></td>
                    <td><?= esc(number_format((float) $item['unit_price'], 2)) ?></td>
                    <td><?= esc(number_format((float) $item['line_total'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-stack" style="margin-top:24px">
        <section class="admin-panel">
            <div class="admin-panel-head"><div><h2>Update Status</h2></div></div>
            <form method="post" action="/admin/orders/<?= esc($order['order_number']) ?>/status" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="oo-status">Status</label>
                    <select id="oo-status" name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= esc($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= esc($statusLabels[$s] ?? $s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-full">
                    <label for="oo-note">Admin Note</label>
                    <textarea id="oo-note" name="admin_note"><?= esc($order['admin_note'] ?? '') ?></textarea>
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Update Status</button>
                </div>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head"><div><h2>Tracking Details</h2></div></div>
            <form method="post" action="/admin/orders/<?= esc($order['order_number']) ?>/tracking" class="admin-form-grid">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="oo-courier">Courier</label>
                    <input id="oo-courier" type="text" name="courier_name" value="<?= esc($order['courier_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="oo-tracking-no">Tracking Number</label>
                    <input id="oo-tracking-no" type="text" name="tracking_number" value="<?= esc($order['tracking_number'] ?? '') ?>">
                </div>
                <div class="form-group form-full">
                    <label for="oo-tracking-url">Tracking URL</label>
                    <input id="oo-tracking-url" type="text" name="tracking_url" value="<?= esc($order['tracking_url'] ?? '') ?>">
                </div>
                <div class="form-full">
                    <button type="submit" class="btn">Save Tracking</button>
                </div>
            </form>
        </section>
    </div>
</section>
<?php $this->endSection() ?>
