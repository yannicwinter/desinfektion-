<?php
declare(strict_types=1);

function illus_extinguisher(string $class = ''): string
{
    return '<svg class="' . $class . '" viewBox="0 0 120 200" aria-hidden="true">'
        . '<path d="M58 30c-22 0-36 6-44 22" fill="none" stroke="#15171C" stroke-width="7" stroke-linecap="round"/>'
        . '<path d="M14 52c-6 10-8 22-6 34" fill="none" stroke="#15171C" stroke-width="7" stroke-linecap="round"/>'
        . '<rect x="4" y="84" width="12" height="16" rx="3" fill="#15171C"/>'
        . '<rect x="48" y="14" width="30" height="18" rx="4" fill="#15171C"/>'
        . '<path d="M78 18h26l6 8H78z" fill="#15171C"/>'
        . '<rect x="54" y="30" width="18" height="12" fill="#3A3D45"/>'
        . '<rect x="30" y="40" width="66" height="152" rx="26" fill="#E60005"/>'
        . '<rect x="30" y="40" width="22" height="152" rx="11" fill="#fff" opacity=".14"/>'
        . '<rect x="38" y="92" width="50" height="46" rx="8" fill="#fff"/>'
        . '<path d="M50 108h26M50 118h26M50 128h16" stroke="#E60005" stroke-width="4" stroke-linecap="round"/>'
        . '</svg>';
}

function illus_kit(string $class = ''): string
{
    return '<svg class="' . $class . '" viewBox="0 0 200 150" aria-hidden="true">'
        . '<path d="M70 30V20a10 10 0 0 1 10-10h40a10 10 0 0 1 10 10v10" fill="none" stroke="#15171C" stroke-width="9"/>'
        . '<rect x="10" y="30" width="180" height="112" rx="18" fill="#fff"/>'
        . '<rect x="10" y="30" width="180" height="26" rx="13" fill="#15171C" opacity=".08"/>'
        . '<path fill="#E60005" d="M86 60h28v22h22v28h-22v22H86v-22H64V82h22z"/>'
        . '</svg>';
}

function illus_heart(string $class = '', string $fill = '#fff', string $line = '#E60005'): string
{
    return '<svg class="' . $class . '" viewBox="0 0 200 170" aria-hidden="true">'
        . '<path fill="' . $fill . '" d="M100 164S8 108 8 52A44 44 0 0 1 100 30a44 44 0 0 1 92 22c0 56-92 112-92 112z"/>'
        . '<path d="M24 84h40l12-22 16 46 16-60 14 36h54" fill="none" stroke="' . $line . '" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>'
        . '</svg>';
}

function illus_safety(): string
{
    ob_start(); ?>
<div class="illus-safety" role="img" aria-label="Illustration: Brandschutzordnung, Schutzschild und Feuerlöscher">
  <svg class="illus-safety__doc" viewBox="0 0 300 380" aria-hidden="true">
    <rect x="0" y="0" width="300" height="380" rx="22" fill="#fff"/>
    <rect x="28" y="30" width="150" height="16" rx="8" fill="#15171C"/>
    <rect x="28" y="58" width="100" height="10" rx="5" fill="#C9CCD2"/>
    <?php foreach ([110, 170, 230, 290] as $i => $y): ?>
    <rect x="28" y="<?= $y ?>" width="36" height="36" rx="10" fill="<?= $i < 3 ? '#FDEEEE' : '#F1F2F4' ?>"/>
    <?php if ($i < 3): ?><path d="M37 <?= $y + 18 ?>l7 7 13-14" fill="none" stroke="#E60005" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><?php endif; ?>
    <rect x="80" y="<?= $y + 6 ?>" width="<?= [170, 140, 160, 120][$i] ?>" height="10" rx="5" fill="#C9CCD2"/>
    <rect x="80" y="<?= $y + 22 ?>" width="<?= [110, 90, 120, 70][$i] ?>" height="8" rx="4" fill="#E3E5E8"/>
    <?php endforeach; ?>
  </svg>
  <svg class="illus-safety__shield" viewBox="0 0 120 140" aria-hidden="true">
    <path d="M60 4 112 22v44c0 36-24 58-52 70C32 124 8 102 8 66V22z" fill="#E60005"/>
    <path d="M36 70l16 16 32-34" fill="none" stroke="#fff" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <?= illus_extinguisher('illus-safety__ext') ?>
  <div class="illus-safety__badge"><strong><?= e(page('home', 'stat_value')) ?></strong><span><?= e(page('home', 'stat_text')) ?></span></div>
</div>
<?php
    return (string) ob_get_clean();
}

function illus_course(array $c): string
{
    $slug = $c['slug'];
    $svg = fn(string $inner) => '<svg class="card__art-icon" viewBox="0 0 120 120" aria-hidden="true">' . $inner . '</svg>';
    $r = '#E60005';
    $map = [
        'erste-hilfe-fortbildung' => ['soft', illus_kit('card__art-kit')],
        'erste-hilfe-im-betrieb' => ['soft', illus_kit('card__art-kit')],
        'erste-hilfe-am-kind' => ['rose', $svg('<circle cx="44" cy="26" r="15" fill="' . $r . '"/><path d="M18 112V74c0-17 12-30 26-30s26 13 26 30v38z" fill="' . $r . '"/><circle cx="86" cy="58" r="11" fill="' . $r . '" opacity=".75"/><path d="M68 112V94c0-12 8-21 18-21s18 9 18 21v18z" fill="' . $r . '" opacity=".75"/>')],
        'kinder-helfen-kindern' => ['rose', $svg('<circle cx="34" cy="40" r="12" fill="' . $r . '"/><path d="M14 104V80c0-13 9-22 20-22s20 9 20 22v24z" fill="' . $r . '"/><circle cx="86" cy="40" r="12" fill="' . $r . '" opacity=".75"/><path d="M66 104V80c0-13 9-22 20-22s20 9 20 22v24z" fill="' . $r . '" opacity=".75"/><path fill="#fff" d="M54 70h12v-12h8v12h12v8H74v12h-8V78H54z" transform="translate(-10 -6) scale(1)"/>')],
        'forstehjelp-schulen' => ['soft', $svg('<circle cx="24" cy="70" r="15" fill="#C98A4B"/><circle cx="50" cy="56" r="15" fill="#AEB4BD"/><circle cx="76" cy="70" r="15" fill="#E3B341"/><circle cx="100" cy="52" r="15" fill="#7C8594"/><path d="M60 14v18M52 22h16" stroke="' . $r . '" stroke-width="7" stroke-linecap="round"/>')],
        'erste-hilfe-party' => ['rose', $svg('<path d="M60 18 14 56h12v46h68V56h12z" fill="' . $r . '"/><path fill="#fff" d="M54 64h12v-12h8v12h12v8H74v12h-8V72H54z" transform="translate(-10 4)"/>')],
        'erste-hilfe-am-hund' => ['rose', $svg('<g fill="' . $r . '"><ellipse cx="60" cy="80" rx="26" ry="22"/><ellipse cx="26" cy="54" rx="11" ry="14"/><ellipse cx="46" cy="30" rx="11" ry="14"/><ellipse cx="74" cy="30" rx="11" ry="14"/><ellipse cx="94" cy="54" rx="11" ry="14"/></g>')],
        'erste-hilfe-am-welpen' => ['rose', $svg('<g fill="' . $r . '" opacity=".85" transform="translate(12 12) scale(.8)"><ellipse cx="60" cy="80" rx="26" ry="22"/><ellipse cx="26" cy="54" rx="11" ry="14"/><ellipse cx="46" cy="30" rx="11" ry="14"/><ellipse cx="74" cy="30" rx="11" ry="14"/><ellipse cx="94" cy="54" rx="11" ry="14"/></g>')],
        'fresh-up-arztpraxen' => ['soft', illus_kit('card__art-kit')],
        'aed-reanimationstraining' => ['red', $svg('<rect x="18" y="16" width="84" height="88" rx="16" fill="#fff"/><path d="M60 92S30 74 30 52a15 15 0 0 1 30-5 15 15 0 0 1 30 5c0 22-30 40-30 40z" fill="' . $r . '"/><path d="M64 44 52 64h12l-6 16 16-24H62z" fill="#fff"/>')],
        'feuerloeschertraining' => ['fire', illus_extinguisher('card__art-ext')],
        'brandschutzhelfer' => ['fire', illus_extinguisher('card__art-ext')],
        'evakuierungsuebung' => ['green', $svg('<rect x="10" y="22" width="100" height="76" rx="10" fill="#fff"/><circle cx="44" cy="36" r="8" fill="#12703B"/><path d="M40 48 30 66l12 2 6 22M40 48l14 10 10-4M48 68l14 10" fill="none" stroke="#12703B" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/><path d="M74 38h24v44H74" fill="none" stroke="#12703B" stroke-width="6"/><path d="M84 60h-14m6-6-6 6 6 6" fill="none" stroke="#12703B" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>')],
        'brandschutzordnung-rettungsplaene' => ['soft', $svg('<rect x="24" y="10" width="72" height="100" rx="10" fill="#fff"/><rect x="36" y="24" width="40" height="8" rx="4" fill="#15171C"/><path d="M38 50l5 5 9-9M38 72l5 5 9-9" fill="none" stroke="' . $r . '" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><rect x="58" y="48" width="26" height="6" rx="3" fill="#C9CCD2"/><rect x="58" y="70" width="22" height="6" rx="3" fill="#C9CCD2"/><rect x="38" y="90" width="44" height="6" rx="3" fill="#E3E5E8"/>')],
        'brandschutzbeauftragter' => ['rose', $svg('<path d="M60 8 104 24v36c0 30-20 48-44 58C36 108 16 90 16 60V24z" fill="' . $r . '"/><path d="M40 62l14 14 28-30" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>')],
    ];
    [$tone, $art] = $map[$slug] ?? ($c['category'] === 'brandschutz' ? ['fire', illus_extinguisher('card__art-ext')] : ['red', illus_heart('card__art-heart')]);
    return '<div class="card__art card__art--' . $tone . '">' . $art . '</div>';
}

function illus_page(string $page): string
{
    if ($page === 'brandschutz') {
        return '<div class="illus-page illus-page--fire" role="img" aria-label="Illustration Brandschutz">' . illus_extinguisher('illus-page__ext') . '<svg class="illus-page__flame" viewBox="0 0 24 24" aria-hidden="true"><path fill="#F2141A" d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1.1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.3 1-3.3.3 1.5 1.3 2.8 2.5 2.8z"/></svg></div>';
    }
    if ($page === 'unternehmen') {
        return illus_safety();
    }
    return '<div class="illus-page illus-page--eh" role="img" aria-label="Illustration Erste Hilfe">' . illus_heart('illus-page__heart', 'rgba(255,255,255,.16)', '#fff') . illus_kit('illus-page__kit') . '</div>';
}
