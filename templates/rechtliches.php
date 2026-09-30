<?php
$P = fn($k) => page($legalPage, $k);
layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => $legalPage,
    'active' => $legalPage,
]);
page_head('', $P('title'), '', [[$P('title'), $legalPage]]);
?>
<section class="section section--tight"><div class="wrap"><div class="prose prose--legal"><?= rich($P('body')) ?></div></div></section>
<?php layout_end(); ?>
