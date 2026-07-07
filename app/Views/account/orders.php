<div class="sp-cart-hero">
    <div class="sp-cart-hero-inner">
        <div class="sp-cart-hero-text">
            <div class="sp-cart-hero-eyebrow">My Account</div>
            <h1>Order History</h1>
            <p>Track every order you've placed with BSAS, from confirmation to delivery.</p>
        </div>
        <div class="sp-cart-hero-stat">
            <strong><?= esc((string) count($orders)) ?></strong>
            <span><?= $statusFilter !== '' ? esc(ucfirst($statusFilter)) . ' Orders' : 'Orders Shown' ?></span>
            <a href="/e-shop" class="btn">Continue Shopping</a>
        </div>
    </div>
</div>

<section class="sp-cart-section">
    <div class="sp-items-panel" style="max-width:960px;margin:0 auto">
        <?= view('account/_nav', ['activeNav' => 'orders']) ?>

        <div style="padding:20px 24px">
            <form method="get" action="/account/orders" class="admin-filters" style="margin-bottom:16px">
                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                        <option value="<?= esc($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-dark">Filter</button>
                <?php if ($statusFilter !== ''): ?>
                    <a href="/account/orders" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </form>

            <?php if ($orders === []): ?>
                <div class="sp-cart-empty">
                    <div class="sp-cart-empty-icon">&#128230;</div>
                    <h3>No orders found.</h3>
                    <p><?= $statusFilter !== '' ? 'No orders match that status.' : "You haven't placed any orders yet." ?></p>
                    <a href="/e-shop" class="btn">Browse Catalogue</a>
                </div>
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
            <?php endif; ?>
        </div>
    </div>
</section>
