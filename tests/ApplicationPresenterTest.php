<?php

declare(strict_types=1);

use ProximaDeck\ApplicationPresenter;
use ProximaDeck\IconResolver;
use ProximaDeck\Network\NetworkContext;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$applications = [
    [
        'id' => 'lan-only',
        'name' => 'LAN only',
        'category' => 'Tools',
        'visibility' => 'internal',
        'internal_url' => 'http://192.168.1.10',
        'external_url' => '',
        'icon' => '',
    ],
    [
        'id' => 'public-only',
        'name' => 'Public only',
        'category' => 'Web',
        'visibility' => 'external',
        'internal_url' => '',
        'external_url' => 'https://public.example.com',
        'icon' => '',
    ],
    [
        'id' => 'both',
        'name' => 'Both',
        'category' => 'Web',
        'visibility' => 'both',
        'internal_url' => 'http://both.lan',
        'external_url' => 'https://both.example.com',
        'icon' => '',
    ],
];

$presenter = new ApplicationPresenter(new IconResolver(dirname(__DIR__) . '/public/assets/icons'));

$suite->test('internal view contains LAN and both applications', function () use ($suite, $presenter, $applications): void {
    $visible = $presenter->visibleApplications($applications, new NetworkContext('internal', '192.168.1.20', 'private_ip'));

    $suite->assertSame(['lan-only', 'both'], array_column($visible, 'id'));
    $suite->assertSame('http://both.lan', $visible[1]['resolved_url']);
    $suite->assertSame('internal', $visible[1]['resolved_target']);
});

$suite->test('external view contains public and both applications', function () use ($suite, $presenter, $applications): void {
    $visible = $presenter->visibleApplications($applications, new NetworkContext('external', '8.8.8.8', 'private_ip'));

    $suite->assertSame(['public-only', 'both'], array_column($visible, 'id'));
    $suite->assertSame('https://both.example.com', $visible[1]['resolved_url']);
    $suite->assertSame('external', $visible[1]['resolved_target']);
});

$suite->test('external view never falls back to an internal URL', function () use ($suite, $presenter): void {
    $visible = $presenter->visibleApplications([[
        'id' => 'unsafe-fallback',
        'name' => 'Unsafe fallback',
        'category' => 'Tools',
        'visibility' => 'both',
        'internal_url' => 'http://192.168.1.99',
        'external_url' => '',
        'icon' => '',
    ]], new NetworkContext('external', '8.8.8.8', 'private_ip'));

    $suite->assertSame([], $visible, 'An application without external_url must be hidden externally.');
});

$suite->test('internal view can fall back to a public URL', function () use ($suite, $presenter): void {
    $visible = $presenter->visibleApplications([[
        'id' => 'public-fallback',
        'name' => 'Public fallback',
        'category' => 'Web',
        'visibility' => 'both',
        'internal_url' => '',
        'external_url' => 'https://fallback.example.com',
        'icon' => '',
    ]], new NetworkContext('internal', '192.168.1.20', 'private_ip'));

    $suite->assertSame('https://fallback.example.com', $visible[0]['resolved_url']);
    $suite->assertSame('fallback', $visible[0]['resolved_target']);
});
