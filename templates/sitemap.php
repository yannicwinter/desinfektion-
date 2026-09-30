<?php
header('Content-Type: application/xml; charset=utf-8');
$urls = [['/', '1.0'], ['erste-hilfe', '0.9'], ['brandschutz', '0.9'], ['arbeitssicherheit', '0.8'], ['termine', '0.9']];
foreach (bookable_courses() as $c) {
    $urls[] = ['termine/' . $c['slug'], '0.8'];
}
array_push($urls, ['faq', '0.6'], ['kontakt', '0.6'], ['impressum', '0.2'], ['datenschutz', '0.2']);
$mod = date('Y-m-d', is_file(CONTENT_FILE) ? filemtime(CONTENT_FILE) : time());
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$u, $prio]) {
    echo '  <url><loc>' . e(abs_url($u === '/' ? '' : $u)) . '</loc><lastmod>' . $mod . '</lastmod><priority>' . $prio . "</priority></url>\n";
}
echo '</urlset>' . "\n";
