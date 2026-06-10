<!-- HERO -->
<section class="hero">
    <div class="hero-bg bg-gallery"></div>
    <div class="hero-content">
        <h1>We respond fast.<br>We fix <span class="accent">faster.</span></h1>
    </div>
</section>

<!-- INTRO -->
<section class="section">
    <p class="support-intro" data-aos="fade-up">Should you have any questions or should you require assistance, or should you wish to order free catalogues or the newsletter, please fill in the contact form and send us your message. We will answer your request as quickly as possible. Thank you!</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="success-banner"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (! empty($errors)): ?>
        <div class="error-banner"><?= esc(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <div class="contact-form-card" data-aos="fade-up">
        <form method="post" action="/support/quote">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= esc(old('name')) ?>" placeholder="Your Name here" required>
                </div>
                <div class="form-group">
                    <label>Company</label>
                    <input type="text" name="company" value="<?= esc(old('company')) ?>" placeholder="Organisation">
                </div>
                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" name="designation" value="<?= esc(old('designation')) ?>" placeholder="Title">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Organisation">
                </div>
                <div class="form-group">
                    <label>Phone No</label>
                    <input type="tel" name="phone" value="<?= esc(old('phone')) ?>" placeholder="Organisation" required>
                </div>
                <div class="form-group">
                    <label>Concern</label>
                    <select name="concerns">
                        <option value="" <?= old('concerns') === '' ? 'selected' : '' ?>>Spare Parts, MPR, Services</option>
                        <option value="spare-parts" <?= old('concerns') === 'spare-parts' ? 'selected' : '' ?>>Spare Parts</option>
                        <option value="mpr" <?= old('concerns') === 'mpr' ? 'selected' : '' ?>>MPR / Equipment</option>
                        <option value="services" <?= old('concerns') === 'services' ? 'selected' : '' ?>>Services</option>
                        <option value="3r" <?= old('concerns') === '3r' ? 'selected' : '' ?>>3R Programme</option>
                        <option value="other" <?= old('concerns') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group form-full">
                    <label>Message</label>
                    <textarea name="message" placeholder="Short description about your demand..."><?= esc(old('message')) ?></textarea>
                </div>
            </div>
            <div class="text-center mt-24">
                <button class="btn" type="submit">Submit</button>
            </div>
        </form>
    </div>
</section>

<!-- FAQ MINI -->
<section class="section section-grey faq-mini" data-aos="fade-up">
    <h2>FAQ</h2>
    <div class="faq-list">
        <details class="faq-item">
            <summary>How do I get a quote from BSAS?</summary>
            <div class="faq-ans">Share your machine make, model, component details, or a sample/drawing with our team. We will assess the requirement and revert with an engineered recommendation, not just a price.</div>
        </details>
        <details class="faq-item">
            <summary>What information should I provide to get the most accurate quote?</summary>
            <div class="faq-ans">Machine model, component part number or description, quantity needed, application details, and if available — OEM specifications or samples. The more detail, the more precise our engineering assessment.</div>
        </details>
        <details class="faq-item">
            <summary>Does BSAS work with companies of all sizes, or only large mines?</summary>
            <div class="faq-ans">BSAS works with operators of all scales — from single-machine contractors to large fleet operators. Our engineering approach adapts to the requirement, not the company size.</div>
        </details>
    </div>
    <p style="text-align:center;margin-top:24px;"><a href="/faq" style="color:var(--text-muted);font-size:14px;">still got questions? ⟶</a></p>
</section>

<!-- CONTACT INFO -->
<section class="section" data-aos="fade-up">
    <div class="contact-info-card">
        <div class="contact-info-labels">
            <p>Telephone :</p>
            <p>Email :</p>
            <p>Corporate Office :</p>
            <p>Workshop :</p>
        </div>
        <div class="contact-info-values">
            <p>+91 0341 4057522</p>
            <p>salessupport@bsasindia.com<br>equip@bsasindia.com</p>
            <p>21 CLM Lane, Raniganj, WB, 713247.</p>
            <p>Punjabi More, Searsole, Raniganj, WB 713358.</p>
        </div>
    </div>
</section>
