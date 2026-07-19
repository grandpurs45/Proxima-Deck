<?php

declare(strict_types=1);

use ProximaDeck\IconResolver;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$resolver = new IconResolver(dirname(__DIR__) . '/public/assets/icons');

$suite->test('configured icon has priority', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'unknown', 'name' => 'Unknown', 'category' => 'Unknown', 'icon' => 'umami.svg']);

    $suite->assertSame('umami.svg', $result['icon']);
    $suite->assertSame('configured', $result['icon_source']);
    $suite->assertSame('/assets/icons/umami.svg', $result['icon_url']);
});

$suite->test('known service icon is resolved automatically', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'uptime-kuma', 'name' => 'Uptime Kuma', 'category' => 'Unknown', 'icon' => '']);

    $suite->assertSame('uptime-kuma.svg', $result['icon']);
    $suite->assertSame('service', $result['icon_source']);
});

$suite->test('extensionless icon explicitly selects Dashboard Icons', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'uptime-kuma', 'name' => 'Uptime Kuma', 'category' => 'Monitoring', 'icon' => 'uptime-kuma']);

    $suite->assertSame('uptime-kuma.webp', $result['icon']);
    $suite->assertSame('dashboard', $result['icon_source']);
    $suite->assertSame('/api/icon.php?name=uptime-kuma', $result['icon_url']);
});

$suite->test('missing local icon uses Dashboard Icons through the local API', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'grafana', 'name' => 'Grafana', 'category' => 'Monitoring', 'icon' => 'grafana.svg']);

    $suite->assertSame('grafana.webp', $result['icon']);
    $suite->assertSame('dashboard', $result['icon_source']);
    $suite->assertSame('/api/icon.php?name=grafana', $result['icon_url']);
});

$suite->test('unknown service gets an automatic monogram', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'grafana-cloud', 'name' => 'Grafana Cloud', 'category' => 'Monitoring', 'icon' => '']);

    $suite->assertSame('', $result['icon']);
    $suite->assertSame('', $result['icon_url']);
    $suite->assertSame('monogram', $result['icon_source']);
    $suite->assertSame('GC', $result['icon_initials']);
    $suite->assertTrue(in_array($result['icon_tone'], ['cyan', 'green', 'amber', 'rose', 'blue'], true));
});

$suite->test('unsafe configured icon falls back safely', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'unknown', 'name' => 'Private Tool', 'category' => 'Unknown', 'icon' => '../private.svg']);

    $suite->assertSame('', $result['icon']);
    $suite->assertSame('monogram', $result['icon_source']);
    $suite->assertSame('PT', $result['icon_initials']);
});
