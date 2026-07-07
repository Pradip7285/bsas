<div class="sp-items-panel-head" style="border-bottom:none;padding-bottom:0">
    <nav style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="/account" class="btn <?= ($activeNav ?? '') === 'dashboard' ? 'btn-dark' : 'btn-outline' ?>">Overview</a>
        <a href="/account/orders" class="btn <?= ($activeNav ?? '') === 'orders' ? 'btn-dark' : 'btn-outline' ?>">My Orders</a>
        <a href="/account/addresses" class="btn <?= ($activeNav ?? '') === 'addresses' ? 'btn-dark' : 'btn-outline' ?>">Addresses</a>
        <a href="/account/profile" class="btn <?= ($activeNav ?? '') === 'profile' ? 'btn-dark' : 'btn-outline' ?>">Profile</a>
        <a href="/logout" class="btn btn-outline">Sign Out</a>
    </nav>
</div>
