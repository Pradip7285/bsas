<?php
$pageTitle = 'Orders';
$activeNav = 'orders';
$mastheadLabel = 'Order Backend';
$mastheadTitle = 'Order fulfilment';
$mastheadText = 'Track paid, pending, and shipped orders and update their fulfilment status.';

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
    <a href="/admin" class="btn btn-outline">Dashboard</a>
<?php $this->endSection() ?>

<?= $this->section('content') ?>
    <section class="admin-panel">
        <div class="admin-panel-head">
            <div>
                <h2>Orders</h2>
                <p>All customer orders placed through the storefront checkout.</p>
            </div>
        </div>

        <form method="get" action="/admin/orders" class="admin-filters">
            <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Search by order number, name, or phone">
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= esc($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= esc($statusLabels[$s] ?? $s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-dark">Apply</button>
            <a href="/admin/orders" class="btn btn-outline">Reset</a>
        </form>

        <div class="admin-table-shell">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Placed</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if ($orders === []): ?>
                    <tr><td colspan="7">No orders found.</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= esc($order['order_number']) ?></td>
                        <td><?= esc($order['shipping_name']) ?><div class="admin-row-meta"><?= esc($order['shipping_phone']) ?></div></td>
                        <td><span class="admin-badge"><?= esc($statusLabels[$order['status']] ?? $order['status']) ?></span></td>
                        <td><?= esc(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?></td>
                        <td><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?></td>
                        <td><?= esc((string) $order['created_at']) ?></td>
                        <td><a href="/admin/orders/<?= esc($order['order_number']) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $this->endSection() ?>
