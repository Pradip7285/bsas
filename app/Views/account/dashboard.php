<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">My Account</div>
            <h1>Welcome, <?= esc($customer['name'] ?? 'Customer') ?></h1>
            <p>Manage your orders, addresses, and account details.</p>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:960px;margin:0 auto">
        <?= view('account/_nav', ['activeNav' => 'dashboard']) ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div style="padding:16px 24px 0"><div class="sp-flash-ok"><?= esc(session()->getFlashdata('success')) ?></div></div>
        <?php endif; ?>

        <div class="admin-summary-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));padding:20px 24px 0">
            <article class="admin-summary-card admin-summary-card--accent">
                <span>Total Orders</span>
                <strong><?= esc((string) $orderCount) ?></strong>
                <p><a href="/account/orders">View order history &rarr;</a></p>
            </article>
            <article class="admin-summary-card">
                <span>In Progress</span>
                <strong><?= esc((string) $pendingCount) ?></strong>
                <p>Pending, confirmed, or processing.</p>
            </article>
            <article class="admin-summary-card">
                <span>Saved Addresses</span>
                <strong><?= esc((string) $addressCount) ?></strong>
                <p><a href="/account/addresses">Manage addresses &rarr;</a></p>
            </article>
        </div>

        <div style="padding:20px 24px">
            <h3>Recent Orders</h3>
            <?php if ($orders === []): ?>
                <p>You haven't placed any orders yet. <a href="/e-shop">Browse the catalogue</a>.</p>
            <?php else: ?>
                <div class="admin-table-shell">
                    <table class="admin-table">
                        <thead><tr><th>Order #</th><th>Status</th><th>Total</th><th>Placed</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= esc($order['order_number']) ?></td>
                                <td><?= view('account/_status_badge', ['status' => $order['status']]) ?></td>
                                <td><?= esc($order['currency']) ?> <?= esc(number_format((float) $order['grand_total'], 2)) ?></td>
                                <td><?= esc((string) $order['created_at']) ?></td>
                                <td><a href="/account/orders/<?= esc($order['order_number']) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top:12px"><a href="/account/orders">View all orders &rarr;</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
