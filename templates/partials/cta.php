<section class="acta">
  <div class="wrap acta__in">
    <div>
      <h2 class="acta__title"><?= e(page('home', 'cta_title')) ?></h2>
      <p><?= e(page('home', 'cta_text')) ?></p>
    </div>
    <div class="acta__acts">
      <a class="btn btn--red" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a>
      <a class="btn btn--ghost" href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?> <?= e(site('email')) ?></a>
    </div>
  </div>
</section>
