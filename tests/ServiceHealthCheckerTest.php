<?php

declare(strict_types=1);

use ProximaDeck\Health\ServiceHealthChecker;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$suite->test('health checker exposes only up down or unknown', function () use ($suite): void {
    $cacheDirectory = sys_get_temp_dir() . '/proximadeck-test-' . bin2hex(random_bytes(6));
    $probe = static fn (array $urls): array => [
        $urls[0] => 'up',
        $urls[1] => 'down',
    ];
    $checker = new ServiceHealthChecker($cacheDirectory, 60, 500, $probe(...));

    try {
        $applications = $checker->withHealth([
            ['id' => 'up', 'resolved_url' => 'https://up.example.com', 'healthcheck' => true],
            ['id' => 'down', 'resolved_url' => 'https://down.example.com', 'healthcheck' => true],
            ['id' => 'disabled', 'resolved_url' => 'https://disabled.example.com', 'healthcheck' => false],
            ['id' => 'unsupported', 'resolved_url' => 'ftp://files.example.com', 'healthcheck' => true],
        ]);

        $suite->assertSame(['up', 'down', 'unknown', 'unknown'], array_column($applications, 'health_status'));
    } finally {
        removeHealthCache($cacheDirectory);
    }
});

$suite->test('health checker reuses its cache', function () use ($suite): void {
    $cacheDirectory = sys_get_temp_dir() . '/proximadeck-test-' . bin2hex(random_bytes(6));
    $calls = 0;
    $probe = static function (array $urls) use (&$calls): array {
        $calls++;

        return array_fill_keys($urls, 'up');
    };
    $checker = new ServiceHealthChecker($cacheDirectory, 60, 500, $probe(...));
    $application = [['id' => 'cached', 'resolved_url' => 'https://cached.example.com/#first', 'healthcheck' => true]];

    try {
        $checker->withHealth($application);
        $application[0]['resolved_url'] = 'https://cached.example.com/#another-view';
        $result = $checker->withHealth($application);

        $suite->assertSame(1, $calls);
        $suite->assertSame('up', $result[0]['health_status']);
    } finally {
        removeHealthCache($cacheDirectory);
    }
});

function removeHealthCache(string $directory): void
{
    foreach (glob($directory . '/*') ?: [] as $file) {
        unlink($file);
    }

    if (is_dir($directory)) {
        rmdir($directory);
    }
}
