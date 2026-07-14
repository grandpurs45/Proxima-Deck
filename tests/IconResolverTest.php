<?php

declare(strict_types=1);

use ProximaDeck\IconResolver;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$resolver = new IconResolver(dirname(__DIR__) . '/public/assets/icons');

$suite->test('configured icon has priority', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'unknown', 'category' => 'Unknown', 'icon' => 'umami.svg']);

    $suite->assertSame('umami.svg', $result['icon']);
    $suite->assertSame('configured', $result['icon_source']);
});

$suite->test('known service icon is resolved automatically', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'uptime-kuma', 'category' => 'Unknown', 'icon' => '']);

    $suite->assertSame('uptime-kuma.svg', $result['icon']);
    $suite->assertSame('service', $result['icon_source']);
});

$suite->test('category icon is used after service fallback', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'unknown', 'category' => 'Monitoring', 'icon' => '']);

    $suite->assertSame('category-monitoring.svg', $result['icon']);
    $suite->assertSame('category', $result['icon_source']);
});

$suite->test('unsafe configured icon falls back safely', function () use ($suite, $resolver): void {
    $result = $resolver->resolve(['id' => 'unknown', 'category' => 'Unknown', 'icon' => '../private.svg']);

    $suite->assertSame('default.svg', $result['icon']);
    $suite->assertSame('default', $result['icon_source']);
});
