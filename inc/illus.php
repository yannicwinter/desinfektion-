<?php
/**
 * Eigene Illustrationen (SVG) für die Startseite – ersetzen dort die Fotos.
 * Farben: DRK-Rot, Rosé, Anthrazit. Skalieren verlustfrei, wenige KB.
 */
declare(strict_types=1);

/** Feuerlöscher (Standard-Ansicht, viewBox 0 0 120 200). */
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

/** Erste-Hilfe-Koffer (viewBox 0 0 200 150). */
function illus_kit(string $class = ''): string
{
    return '<svg class="' . $class . '" viewBox="0 0 200 150" aria-hidden="true">'
        . '<path d="M70 30V20a10 10 0 0 1 10-10h40a10 10 0 0 1 10 10v10" fill="none" stroke="#15171C" stroke-width="9"/>'
        . '<rect x="10" y="30" width="180" height="112" rx="18" fill="#fff"/>'
        . '<rect x="10" y="30" width="180" height="26" rx="13" fill="#15171C" opacity=".08"/>'
        . '<path fill="#E60005" d="M86 60h28v22h22v28h-22v22H86v-22H64V82h22z"/>'
        . '</svg>';
}

/** Herz mit EKG-Linie (viewBox 0 0 200 170). */
function illus_heart(string $class = '', string $fill = '#fff', string $line = '#E60005'): string
{
    return '<svg class="' . $class . '" viewBox="0 0 200 170" aria-hidden="true">'
        . '<path fill="' . $fill . '" d="M100 164S8 108 8 52A44 44 0 0 1 100 30a44 44 0 0 1 92 22c0 56-92 112-92 112z"/>'
        . '<path d="M24 84h40l12-22 16 46 16-60 14 36h54" fill="none" stroke="' . $line . '" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>'
        . '</svg>';
}

/** Arbeitssicherheit: Dokument mit Checkliste, Schutzschild, Feuerlöscher. */
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

/** Symbol-Kopf für Kurskarten: passendes Motiv je Kurs. */
function illus_course(array $c): string
{
    $slug = $c['slug'];
    if ($c['category'] === 'brandschutz') {
        return '<div class="card__art card__art--fire">' . illus_extinguisher('card__art-ext') . '</div>';
    }
    if (str_contains($slug, 'kind')) {
        $svg = '<svg class="card__art-icon" viewBox="0 0 120 120" aria-hidden="true"><circle cx="44" cy="26" r="15" fill="#E60005"/><path d="M18 112V74c0-17 12-30 26-30s26 13 26 30v38z" fill="#E60005"/><circle cx="86" cy="58" r="11" fill="#E60005" opacity=".75"/><path d="M68 112V94c0-12 8-21 18-21s18 9 18 21v18z" fill="#E60005" opacity=".75"/></svg>';
        return '<div class="card__art card__art--rose">' . $svg . '</div>';
    }
    if (str_contains($slug, 'hund') || str_contains($slug, 'welpe')) {
        $svg = '<svg class="card__art-icon" viewBox="0 0 120 120" aria-hidden="true"><g fill="#E60005"><ellipse cx="60" cy="80" rx="26" ry="22"/><ellipse cx="26" cy="54" rx="11" ry="14"/><ellipse cx="46" cy="30" rx="11" ry="14"/><ellipse cx="74" cy="30" rx="11" ry="14"/><ellipse cx="94" cy="54" rx="11" ry="14"/></g></svg>';
        return '<div class="card__art card__art--rose">' . $svg . '</div>';
    }
    if (str_contains($slug, 'fortbildung')) {
        return '<div class="card__art card__art--soft">' . illus_kit('card__art-kit') . '</div>';
    }
    return '<div class="card__art card__art--red">' . illus_heart('card__art-heart') . '</div>';
}
