<?php
$statusColors = [
    'pending'    => ['bg' => '#fef3c7', 'fg' => '#92400e'],
    'confirmed'  => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
    'processing' => ['bg' => '#e0e7ff', 'fg' => '#3730a3'],
    'shipped'    => ['bg' => '#fef3c7', 'fg' => '#92400e'],
    'delivered'  => ['bg' => '#d1fae5', 'fg' => '#065f46'],
    'cancelled'  => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
];
$colors = $statusColors[$status ?? ''] ?? ['bg' => '#f3f4f6', 'fg' => '#6b7280'];
?>
<span class="admin-badge" style="background:<?= esc($colors['bg']) ?>;color:<?= esc($colors['fg']) ?>;border-color:transparent">
    <?= esc(ucfirst($status ?? 'unknown')) ?>
</span>
