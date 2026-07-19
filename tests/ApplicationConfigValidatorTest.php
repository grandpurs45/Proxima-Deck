<?php

declare(strict_types=1);

use ProximaDeck\Config\ApplicationConfigValidator;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$validator = new ApplicationConfigValidator(dirname(__DIR__) . '/public/assets/icons');

$validApplication = [
    'id' => 'umami',
    'name' => 'Umami',
    'visibility' => 'both',
    'internal_url' => 'https://umami.lan/login',
    'external_url' => 'https://analytics.example.com',
    'icon' => 'umami.svg',
];

$suite->test('valid application configuration has no issues', function () use ($suite, $validator, $validApplication): void {
    $result = $validator->validate([$validApplication]);

    $suite->assertSame(false, $result->hasErrors());
    $suite->assertSame([], $result->toArray());
});

$suite->test('duplicate IDs and invalid URLs are rejected', function () use ($suite, $validator, $validApplication): void {
    $duplicate = $validApplication;
    $duplicate['internal_url'] = 'not-a-url';
    $result = $validator->validate([$validApplication, $duplicate]);
    $codes = array_column($result->toArray(), 'code');

    $suite->assertTrue(in_array('duplicate_id', $codes, true));
    $suite->assertTrue(in_array('invalid_url', $codes, true));
});

$suite->test('missing icon file is accepted for automatic fallback', function () use ($suite, $validator, $validApplication): void {
    $application = $validApplication;
    $application['icon'] = 'does-not-exist.svg';
    $result = $validator->validate([$application]);

    $suite->assertSame(false, $result->hasErrors());
    $suite->assertSame(false, $result->hasWarnings());
    $suite->assertSame([], $result->toArray());
});

$suite->test('Dashboard Icons names and supported extensions are accepted', function () use ($suite, $validator, $validApplication): void {
    foreach (['grafana', 'uptime-kuma.png', 'proxmox.webp'] as $icon) {
        $application = $validApplication;
        $application['icon'] = $icon;
        $result = $validator->validate([$application]);

        $suite->assertSame(false, $result->hasErrors());
    }
});

$suite->test('icon paths are rejected', function () use ($suite, $validator, $validApplication): void {
    $application = $validApplication;
    $application['icon'] = '../private.svg';
    $result = $validator->validate([$application]);

    $suite->assertSame(true, $result->hasErrors());
    $suite->assertSame('invalid_icon_name', $result->toArray()[0]['code']);
});

$suite->test('invalid healthcheck value is rejected', function () use ($suite, $validator, $validApplication): void {
    $application = $validApplication;
    $application['healthcheck'] = 'sometimes';
    $result = $validator->validate([$application]);

    $suite->assertSame(true, $result->hasErrors());
    $suite->assertSame('invalid_healthcheck', $result->toArray()[0]['code']);
});
