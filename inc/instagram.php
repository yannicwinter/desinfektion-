<?php
/**
 * Instagram-Feed über Behold.so: Behold verbindet das Instagram-Konto und liefert einen
 * JSON-Feed-Link (kein Token, keine Meta-App nötig). Die Website lädt den Feed stündlich
 * serverseitig und speichert die Bilder lokal in uploads/instagram/ – Besucher laden nichts
 * von Instagram oder Behold (DSGVO). Link im Admin unter „Allgemein“ eintragen.
 */
declare(strict_types=1);

const IG_DIR = UPLOAD_DIR . '/instagram'; // öffentlich erreichbar (Bilder); feed.json ist per .htaccess gesperrt

/** @return array<int, array{img:string, link:string, caption:string, date:string}> */
function instagram_posts(int $limit = 6): array
{
    $feedUrl = trim(site('instagram_feed_url'));
    if ($feedUrl === '') {
        return [];
    }
    if (!is_dir(IG_DIR)) {
        @mkdir(IG_DIR, 0755, true);
    }
    $cacheFile = IG_DIR . '/feed.json';
    $cached = is_file($cacheFile) ? (json_decode((string) file_get_contents($cacheFile), true) ?: []) : [];
    if ($cached && filemtime($cacheFile) > time() - 3600) {
        return array_slice($cached, 0, $limit);
    }

    $items = [];
    $json = json_decode(instagram_get($feedUrl), true);
    foreach (($json['posts'] ?? (isset($json[0]) ? $json : [])) as $m) {
        $items[] = [
            'id' => (string) ($m['id'] ?? ''),
            'src' => (string) ($m['sizes']['medium']['mediaUrl'] ?? $m['thumbnailUrl'] ?? $m['mediaUrl'] ?? ''),
            'link' => (string) ($m['permalink'] ?? ''),
            'caption' => (string) (($m['altText'] ?? '') ?: ($m['prunedCaption'] ?? $m['caption'] ?? '')),
            'date' => (string) ($m['timestamp'] ?? ''),
        ];
    }
    if (!$items) {
        @touch($cacheFile); // Fehler: alten Stand behalten, in 1 Std. erneut versuchen
        return array_slice($cached, 0, $limit);
    }

    $posts = [];
    foreach ($items as $m) {
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', $m['id']) ?: md5($m['src']);
        if ($m['src'] === '') {
            continue;
        }
        $file = IG_DIR . '/' . $id . '.jpg';
        if (!is_file($file) && !instagram_store_image($m['src'], $file)) {
            continue;
        }
        $posts[] = [
            'img' => url('uploads/instagram/' . $id . '.jpg'),
            'link' => $m['link'] ?: site('instagram'),
            'caption' => mb_substr(trim(preg_replace('/\s+/u', ' ', $m['caption'])), 0, 140),
            'date' => substr($m['date'], 0, 10),
        ];
        if (count($posts) >= 12) {
            break;
        }
    }
    file_put_contents($cacheFile, json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    $keep = array_map(fn($p) => basename($p['img']), $posts);
    foreach (glob(IG_DIR . '/*.jpg') ?: [] as $f) {
        if (!in_array(basename($f), $keep, true)) {
            @unlink($f);
        }
    }
    return array_slice($posts, 0, $limit);
}

function instagram_get(string $url): string
{
    if (!function_exists('curl_init')) {
        return (string) @file_get_contents($url);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_FOLLOWLOCATION => true,
        // Referer = eigene Adresse, falls im Behold-Feed eine Domain-Sperre aktiv ist
        CURLOPT_REFERER => abs_url('/'), CURLOPT_HTTPHEADER => ['Origin: ' . rtrim(abs_url('/'), '/')]]);
    $r = (string) curl_exec($ch);
    curl_close($ch);
    return $r;
}

/** Bild laden, quadratisch zuschneiden (600 px) und als JPG speichern. */
function instagram_store_image(string $src, string $file): bool
{
    $raw = instagram_get($src);
    if ($raw === '' || !function_exists('imagecreatefromstring') || !($im = @imagecreatefromstring($raw))) {
        return false;
    }
    $w = imagesx($im);
    $h = imagesy($im);
    $s = min($w, $h);
    $out = imagecreatetruecolor(600, 600);
    imagecopyresampled($out, $im, 0, 0, (int) (($w - $s) / 2), (int) (($h - $s) / 2), 600, 600, $s, $s);
    imageinterlace($out, true);
    return imagejpeg($out, $file, 78);
}
