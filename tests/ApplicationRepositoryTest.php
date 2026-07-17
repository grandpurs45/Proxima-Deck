<?php

declare(strict_types=1);

use ProximaDeck\Config\ApplicationRepository;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$suite->test('repository keeps icon empty when it is omitted', function () use ($suite): void {
    $path = tempnam(sys_get_temp_dir(), 'proximadeck-');

    if ($path === false) {
        throw new RuntimeException('Unable to create a temporary configuration file.');
    }

    file_put_contents($path, <<<YAML
applications:
  - id: grafana-cloud
    name: Grafana Cloud
    category: Monitoring
    visibility: both
    external_url: https://grafana.example.com
  - id: manual-only
    name: Manual only
    category: Tools
    visibility: internal
    internal_url: http://manual.local
    healthcheck: false
YAML);

    try {
        $applications = (new ApplicationRepository($path))->all();
        $suite->assertSame('', $applications[0]['icon']);
        $suite->assertSame(true, $applications[0]['healthcheck']);
        $suite->assertSame(false, $applications[1]['healthcheck']);
    } finally {
        unlink($path);
    }
});
