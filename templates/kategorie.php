<?php
/** Kursübersicht als Akkordeon: /erste-hilfe und /brandschutz */
$P = fn($k) => page($category, $k);
$list = courses($category);
$groups = [];
foreach ($list as $c) {
    $groups[$c['group'] ?: 'Kurse'][] = $c;
}
$isFire = $category === 'brandschutz';

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => $category,
    'active' => $category,
    'breadcrumb' => [[$P('title'), $category]],
    'schema' => array_map(fn($c) => course_schema($c), $list),
]);
page_head($P('eyebrow'), $P('title'), $P('lead'), [[$P('title'), $category]]);
?>
<section class="section section--tight">
  <div class="wrap layout-aside">
    <div class="layout-aside__main">
      <?php if (lines($P('vergleich'))): ?>
      <div class="compare reveal">
        <h2 class="group-title">Welcher Kurs passt?</h2>
        <div class="compare__scroll"><table>
          <thead><tr><?php foreach (array_map('trim', explode('|', lines($P('vergleich'))[0])) as $th): ?><th scope="col"><?= e($th) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
          <?php foreach (array_slice(lines($P('vergleich')), 1) as $line): $cells = array_map('trim', explode('|', $line)); ?>
            <tr><th scope="row"><?= e(array_shift($cells)) ?></th><?php foreach ($cells as $cell): $k = mb_strtolower($cell); ?><td class="<?= $k === 'ja' ? 'yes' : ($k === 'nein' ? 'no' : 'part') ?>"><?= $k === 'ja' ? icon('check') . '<span class="sr-only">ja</span>' : ($k === 'nein' ? '<span aria-hidden="true">–</span><span class="sr-only">nein</span>' : e($cell)) ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <?php endif; ?>
      <?php foreach ($groups as $g => $items): ?>
      <h2 class="group-title reveal"><?= e($g) ?></h2>
      <div class="acc reveal">
        <?php foreach ($items as $c):
            $facts = pairs($c['facts']);
            $learn = lines($c['learn']);
        ?>
        <details class="acc__item" id="<?= e($c['slug']) ?>">
          <summary class="acc__sum">
            <span class="acc__head">
              <span class="acc__title"><?= e($c['title']) ?></span>
              <span class="acc__teaser"><?= e($c['teaser']) ?></span>
            </span>
            <span class="acc__meta">
              <?php if ($c['price']): ?><span class="pill pill--red"><?= e($c['price']) ?></span><?php endif; ?>
              <?php if (!empty($c['hiorg_id'])): ?><span class="pill"><?= icon('calendar') ?>Online buchbar</span><?php else: ?><span class="pill">Auf Anfrage</span><?php endif; ?>
            </span>
            <?= icon('chevron', 'i acc__chev') ?>
          </summary>
          <div class="acc__body">
            <div class="acc__grid">
              <div class="prose">
                <?= rich($c['text']) ?>
                <?php if ($learn): ?>
                <h3 class="h6">Inhalte</h3>
                <ul class="checks checks--sm"><?php foreach ($learn as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
              </div>
              <div class="acc__side">
                <?php if ($img = slot_image('kurs-' . $c['slug'])): ?><img class="acc__img" src="<?= e($img) ?>" alt="<?= e($c['title']) ?>" loading="lazy" decoding="async" width="1400" height="788"><?php endif; ?>
                <?php if ($facts): ?>
                <dl class="facts">
                  <?php foreach ($facts as [$k, $v]): ?><div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div><?php endforeach; ?>
                </dl>
                <?php endif; ?>
              </div>
            </div>
            <?php if (!empty($c['hiorg_id'])): ?>
            <div class="acc__dates" data-dates="<?= url('api/termine/' . $c['slug']) ?>?limit=3">
              <h3 class="h6">Nächste Termine</h3>
              <div class="acc__dates-list"><p class="muted small">Termine werden geladen …</p></div>
            </div>
            <?php endif; ?>
            <div class="btn-row">
              <?php if (!empty($c['hiorg_id'])): ?>
              <a class="btn btn--red" href="<?= url('termine/' . $c['slug']) ?>"><?= icon('calendar') ?> Alle Termine &amp; buchen</a>
              <a class="btn btn--ghost" href="<?= url('kontakt?thema=' . $c['slug']) ?>">Inhouse anfragen</a>
              <?php else: ?>
              <a class="btn btn--red" href="<?= url('kontakt?thema=' . $c['slug']) ?>"><?= icon('mail') ?> Jetzt anfragen</a>
              <a class="btn btn--ghost" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> Anrufen</a>
              <?php endif; ?>
            </div>
          </div>
        </details>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <aside class="layout-aside__side">
      <?= image_slot($category, $isFire ? 'Löschübung mit dem Feuerlöscher' : 'Erste-Hilfe-Übung: Verband anlegen', $isFire ? 'flame' : 'heart', 'reveal') ?>
      <div class="box reveal">
        <?php if ($isFire): ?>
        <h2 class="h5"><?= e($P('contact_title')) ?></h2>
        <p><?= e($P('contact_text')) ?></p>
        <?php else: ?>
        <h2 class="h5"><?= e($P('outro_title')) ?></h2>
        <p><?= e($P('outro_text')) ?></p>
        <?php endif; ?>
        <ul class="contact-list">
          <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
          <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
        </ul>
        <a class="btn btn--red btn--block" href="<?= url('termine') ?>"><?= icon('calendar') ?> Alle Kurstermine</a>
      </div>
    </aside>
  </div>
</section>
<?php include __DIR__ . '/partials/cta.php'; ?>
<?php layout_end(); ?>
