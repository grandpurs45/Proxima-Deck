<?php

declare(strict_types=1);

use ProximaDeck\Icon\DashboardIconCache;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

$cacheDirectory = env_value(
    'PROXIMADECK_ICON_CACHE',
    sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proximadeck' . DIRECTORY_SEPARATOR . 'icons'
);
$cacheTtl = max(3600, (int) env_value('PROXIMADECK_ICON_CACHE_TTL', '2592000'));
$timeoutMs = max(500, (int) env_value('PROXIMADECK_ICON_TIMEOUT_MS', '3000'));
$icon = (new DashboardIconCache($cacheDirectory, $cacheTtl, $timeoutMs))->get((string) ($_GET['name'] ?? ''));

if ($icon === null) {
    http_response_code(404);
    header('Cache-Control: public, max-age=300');
    exit;
}

header('Cache-Control: public, max-age=604800, immutable');
header('ETag: ' . $icon['etag']);
header('X-Content-Type-Options: nosniff');

if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $icon['etag']) {
    http_response_code(304);
    exit;
}

header('Content-Type: image/webp');
header('Content-Length: ' . strlen($icon['contents']));

echo $icon['contents'];
