<?php

declare(strict_types=1);

use ProximaDeck\Icon\DashboardIconCache;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$suite->test('Dashboard icon is fetched once and then served from cache', function () use ($suite): void {
    $cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proximadeck-test-' . bin2hex(random_bytes(6));
    $webp = 'RIFF' . pack('V', 4) . 'WEBP';
    $calls = 0;
    $cache = new DashboardIconCache(
        $cacheDirectory,
        3600,
        500,
        static function (string $url, int $timeoutMs) use (&$calls, $suite, $webp): string {
            $calls++;
            $suite->assertSame('https://cdn.jsdelivr.net/gh/homarr-labs/dashboard-icons/webp/proxmox.webp', $url);
            $suite->assertSame(500, $timeoutMs);

            return $webp;
        }
    );

    $first = $cache->get('proxmox');
    $second = $cache->get('proxmox');

    $suite->assertSame($webp, $first['contents'] ?? null);
    $suite->assertSame($first, $second);
    $suite->assertSame(1, $calls);

    @unlink($cacheDirectory . DIRECTORY_SEPARATOR . 'proxmox.webp');
    @rmdir($cacheDirectory);
});

$suite->test('Dashboard icon cache rejects paths without fetching', function () use ($suite): void {
    $calls = 0;
    $cache = new DashboardIconCache(
        sys_get_temp_dir(),
        3600,
        500,
        static function () use (&$calls): ?string {
            $calls++;

            return null;
        }
    );

    $suite->assertSame(null, $cache->get('../private'));
    $suite->assertSame(0, $calls);
});

$suite->test('Missing Dashboard icon is not fetched repeatedly', function () use ($suite): void {
    $cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proximadeck-test-' . bin2hex(random_bytes(6));
    $calls = 0;
    $cache = new DashboardIconCache(
        $cacheDirectory,
        3600,
        500,
        static function () use (&$calls): ?string {
            $calls++;

            return null;
        }
    );

    $suite->assertSame(null, $cache->get('missing-service'));
    $suite->assertSame(null, $cache->get('missing-service'));
    $suite->assertSame(1, $calls);

    @unlink($cacheDirectory . DIRECTORY_SEPARATOR . 'missing-service.webp.missing');
    @rmdir($cacheDirectory);
});
