<section class="section section--tight">
  <div class="wrap">
    <div class="cta reveal">
      <?= cross_svg('cta__cross', 'currentColor') ?>
      <div class="cta__text">
        <h2 class="h3"><?= e(page('home', 'cta_title')) ?></h2>
        <p><?= e(page('home', 'cta_text')) ?></p>
      </div>
      <div class="btn-row">
        <a class="btn btn--white" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a>
        <a class="btn btn--outline-white" href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?> E-Mail</a>
      </div>
    </div>
  </div>
</section>
