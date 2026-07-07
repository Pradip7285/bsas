<?php
/** @var int $currentStep 1 = Address, 2 = Review, 3 = Confirmation */
$steps = ['Shipping Address', 'Review Order', 'Confirmation'];
?>
<div style="display:flex;align-items:center;max-width:520px;margin:0 auto 28px">
    <?php foreach ($steps as $i => $label): ?>
        <?php $stepNum = $i + 1; $done = $stepNum <= $currentStep; ?>
        <div style="flex:1;text-align:center;position:relative">
            <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;
                background:<?= $done ? '#f59b23' : '#e5e7eb' ?>;color:<?= $done ? '#fff' : '#9ca3af' ?>">
                <?= $stepNum < $currentStep ? '&#10003;' : esc((string) $stepNum) ?>
            </div>
            <span style="font-size:11px;font-weight:700;color:<?= $done ? '#111' : '#9ca3af' ?>"><?= esc($label) ?></span>
            <?php if ($i < count($steps) - 1): ?>
                <div style="position:absolute;top:15px;left:50%;width:100%;height:2px;background:<?= $stepNum < $currentStep ? '#f59b23' : '#e5e7eb' ?>;z-index:-1"></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
