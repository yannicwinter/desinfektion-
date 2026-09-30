<?php
/**
 * Instagram-Feed (Instagram API mit Instagram-Login).
 * Die neuesten Beiträge werden serverseitig geladen und die Bilder lokal in uploads/instagram/
 * gespeichert – Besucher laden nichts von Instagram (DSGVO). Zugangs-Token im Admin unter „Allgemein“.
 * Das Token (60 Tage gültig) wird automatisch verlängert.
 */
declare(strict_types=1);

const IG_DIR = UPLOAD_DIR . '/instagram'; // öffentlich erreichbar (Bilder); feed.json ist per .htaccess gesperrt

/** @return array<int, array{img:string, link:string, caption:string, date:string}> */
function instagram_posts(int $limit = 6): array
{
    $token = trim(site('instagram_token'));
    if ($token === '') {
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

    $token = instagram_refresh_token($token);
    $res = instagram_get('https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&limit=12&access_token=' . rawurlencode($token));
    $data = json_decode($res, true)['data'] ?? null;
    if (!is_array($data)) {
        @touch($cacheFile); // Fehler: alten Stand behalten, in 1 Std. erneut versuchen
        return array_slice($cached, 0, $limit);
    }

    $posts = [];
    foreach ($data as $m) {
        $src = $m['media_type'] === 'VIDEO' ? ($m['thumbnail_url'] ?? '') : ($m['media_url'] ?? '');
        $id = preg_replace('/\D/', '', (string) $m['id']);
        if ($src === '' || $id === '') {
            continue;
        }
        $file = IG_DIR . '/' . $id . '.jpg';
        if (!is_file($file) && !instagram_store_image($src, $file)) {
            continue;
        }
        $posts[] = [
            'img' => url('uploads/instagram/' . $id . '.jpg'),
            'link' => (string) ($m['permalink'] ?? site('instagram')),
            'caption' => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($m['caption'] ?? ''))), 0, 140),
            'date' => substr((string) ($m['timestamp'] ?? ''), 0, 10),
        ];
        if (count($posts) >= 12) {
            break;
        }
    }
    file_put_contents($cacheFile, json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    // alte Bilder aufräumen
    $keep = array_map(fn($p) => basename($p['img']), $posts);
    foreach (glob(IG_DIR . '/*.jpg') ?: [] as $f) {
        if (!in_array(basename($f), $keep, true)) {
            @unlink($f);
        }
    }
    return array_slice($posts, 0, $limit);
}

/** Token alle ~50 Tage verlängern und im Inhalt speichern. */
function instagram_refresh_token(string $token): string
{
    $stamp = IG_DIR . '/token-refreshed';
    if (is_file($stamp) && filemtime($stamp) > time() - 50 * 86400) {
        return $token;
    }
    $res = json_decode(instagram_get('https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . rawurlencode($token)), true);
    @touch($stamp);
    if (!empty($res['access_token']) && $res['access_token'] !== $token) {
        $c = content();
        $c['site']['instagram_token'] = $res['access_token'];
        save_content($c);
        return $res['access_token'];
    }
    return $token;
}

function instagram_get(string $url): string
{
    if (!function_exists('curl_init')) {
        return (string) @file_get_contents($url);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_FOLLOWLOCATION => true]);
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
