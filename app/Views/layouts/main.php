<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | <?= esc($brand) ?></title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <?php if ($active === 'shop'): ?><link rel="stylesheet" href="/assets/css/shop-premium.css"><?php endif; ?>
    <?php foreach (($extraStyles ?? []) as $style): ?>
        <link rel="stylesheet" href="<?= esc($style) ?>">
    <?php endforeach; ?>
    <?php if (! empty($needsAos)): ?>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <?php endif; ?>
</head>
<?php $isShopExperience = $active === 'shop'; ?>
<?php $currentPath = trim(uri_string(), '/'); ?>
<body class="<?= esc(trim(($isShopExperience ? 'shop-body ' : '') . ($bodyClass ?? ''))) ?>">

<?php if (! $isShopExperience): ?>
    <header class="site-header">
        <a class="logo" href="/">
            <div class="logo-icon">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="38" height="38">
                    <rect width="40" height="40" rx="8" fill="#f59b23"/>
                    <text x="20" y="28" text-anchor="middle" font-size="22" fill="#111" font-weight="900" font-family="Arial">B</text>
                </svg>
            </div>
            <div class="logo-text">
                <span class="logo-name">BSAS</span>
                <span class="logo-sub">Engineered for performance</span>
            </div>
        </a>

        <button class="nav-toggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <nav id="main-nav">
            <a href="/about-us" class="<?= $active === 'about' ? 'active' : '' ?>">About Us</a>
            <a href="/spare-parts" class="<?= $active === 'spare-parts' ? 'active' : '' ?>">Spare Parts</a>
            <a href="/equipments" class="<?= $active === 'equipment' ? 'active' : '' ?>">Equipments</a>
            <a href="/services" class="<?= $active === 'services' ? 'active' : '' ?>">Services</a>
            <a href="/e-shop" class="<?= $active === 'shop' ? 'active' : '' ?>">E-Shop</a>
            <a href="/gallery" class="<?= $active === 'gallery' ? 'active' : '' ?>">Gallery</a>
            <a href="/support" class="<?= $active === 'support' ? 'active' : '' ?>">Support</a>
        </nav>
    </header>
<?php else: ?>
    <header class="shop-header">
        <a class="logo" href="/e-shop">
            <div class="logo-icon">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="38" height="38">
                    <rect width="40" height="40" rx="8" fill="#f59b23"/>
                    <text x="20" y="28" text-anchor="middle" font-size="22" fill="#111" font-weight="900" font-family="Arial">B</text>
                </svg>
            </div>
            <div class="logo-text">
                <span class="logo-name">BSAS E-Shop</span>
                <span class="logo-sub">Search, Cart, Quote</span>
            </div>
        </a>

        <nav class="shop-nav">
            <a href="/e-shop" class="<?= $currentPath === 'e-shop' || str_starts_with($currentPath, 'e-shop/product/') ? 'active' : '' ?>">Catalogue</a>
            <a href="/cart" class="<?= $currentPath === 'cart' ? 'active' : '' ?>">Quote Basket<?= isset($cartCount) && $cartCount > 0 ? ' (' . esc((string) $cartCount) . ')' : '' ?></a>
            <a href="/support" class="<?= $currentPath === 'support' ? 'active' : '' ?>">Support</a>
            <?php if (session()->get('is_admin_authenticated') === true): ?>
                <a href="/admin">Backend</a>
                <a href="/admin/logout">Sign Out</a>
            <?php endif; ?>
            <a href="/">Main Site</a>
        </nav>
    </header>
<?php endif; ?>

<main class="<?= $isShopExperience ? 'shop-main' : '' ?>"><?= view($view, get_defined_vars()) ?></main>

<footer class="site-footer">
    <div class="footer-cta">
        <div class="footer-cta-text">
            <span class="footer-cta-kicker">Ready to Start?</span>
            <h3>Get Your Custom<br><span class="footer-cta-accent">Quote Today</span></h3>
            <p>Tell us your application &mdash; we'll engineer the solution.</p>
        </div>
        <div class="footer-cta-actions">
            <a href="/support" class="btn">Contact Us &rarr;</a>
            <button type="button" class="btn btn-outline btn-outline-light" data-brochure-open>Request Brochure</button>
        </div>
    </div>

    <div class="footer-mid">
        <div class="footer-brand">
            <div class="footer-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="48" height="48">
                        <rect width="40" height="40" rx="8" fill="#f59b23"/>
                        <text x="20" y="28" text-anchor="middle" font-size="22" fill="#111" font-weight="900" font-family="Arial">B</text>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-name">BSAS</span>
                    <span class="logo-sub">Engineered for performance</span>
                </div>
            </div>
        </div>
        <div class="footer-newsletter footer-procurement-card">
            <p class="newsletter-label">Procurement desk</p>
            <p class="footer-procurement-copy">Need pricing, interchange validation, or fleet support? Contact the BSAS team directly.</p>
            <div class="footer-procurement-actions">
                <a href="tel:<?= esc($phone) ?>" class="btn btn-outline btn-outline-light">Call Sales</a>
                <a href="mailto:<?= esc($email) ?>" class="btn btn-dark">Email Procurement</a>
            </div>
        </div>
    </div>

    <div class="footer-grid">
        <div class="footer-col footer-col--info">
            <p class="footer-desc">Manufacturers of advanced drill rigs, delivering reliable spare parts and refurbishment services for mining and construction machinery.</p>
            <p class="footer-contact-line"><?= esc($phone) ?></p>
            <p class="footer-contact-line"><?= esc($email) ?></p>
            <p class="footer-contact-line"><?= esc($address) ?></p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="/about-us">About BSAS</a>
            <a href="/spare-parts">Spare Parts</a>
            <a href="/equipments">Equipment</a>
            <a href="/services">Services</a>
            <a href="/e-shop">E Shop</a>
            <a href="/support">Support</a>
        </div>
        <div class="footer-col">
            <h4>Legal &amp; Policies</h4>
            <a href="/privacy-policy">Privacy Policy</a>
            <a href="#">Terms &amp; Conditions</a>
            <a href="#">Cookie Policy</a>
        </div>
        <div class="footer-col">
            <h4>Socials</h4>
            <div class="footer-socials">
                <a href="#" class="social-btn" aria-label="LinkedIn">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8A1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5a5 5 0 0 1-5 5a5 5 0 0 1-5-5a5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3z"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="YouTube">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9c.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83c-.25.9-.83 1.48-1.73 1.73c-.47.13-1.33.22-2.65.28c-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44c-.9-.25-1.48-.83-1.73-1.73c-.13-.47-.22-1.1-.28-1.9c-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83c.25-.9.83-1.48 1.73-1.73c.47-.13 1.33-.22 2.65-.28c1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44c.9.25 1.48.83 1.73 1.73z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <small>&copy; 2026 Bharat Spares &amp; Services. All rights reserved.</small>
        <small>Engineered for performance.</small>
    </div>
</footer>

<div class="modal-backdrop" data-brochure-modal hidden>
    <div class="modal-card">
        <button type="button" class="modal-close" data-brochure-close aria-label="Close brochure form">&times;</button>
        <span class="section-label">Brochure Download</span>
        <h3>Enter Mobile Number</h3>
        <p>The brochure download starts immediately after submission, and the lead is stored in the backend.</p>
        <form class="brochure-form" data-brochure-form>
            <?= csrf_field() ?>
            <label for="brochure-mobile">Mobile Number</label>
            <input id="brochure-mobile" type="tel" name="mobile" placeholder="+91 98XXXXXXXX" required>
            <p class="brochure-feedback" data-brochure-feedback hidden></p>
            <button type="submit" class="btn">Submit & Download</button>
        </form>
    </div>
</div>

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
<script defer src="/assets/js/site.js"></script>
<?php if (! empty($needsAos)): ?>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
<?php endif; ?>
</body>
</html>
