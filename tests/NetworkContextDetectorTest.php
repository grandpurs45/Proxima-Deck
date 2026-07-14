<?php

declare(strict_types=1);

use ProximaDeck\Network\NetworkContextDetector;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$suite->test('private IP is detected as internal', function () use ($suite): void {
    $context = $suite->withEnvironment([
        'PROXIMADECK_DIAGNOSTIC_MODE' => null,
        'PROXIMADECK_NETWORK_CONTEXT' => null,
    ], static fn () => (new NetworkContextDetector())->detect(['REMOTE_ADDR' => '192.168.1.42']));

    $suite->assertSame('internal', $context->scope);
    $suite->assertSame('private_ip', $context->method);
});

$suite->test('public IP is detected as external', function () use ($suite): void {
    $context = $suite->withEnvironment([
        'PROXIMADECK_DIAGNOSTIC_MODE' => null,
        'PROXIMADECK_NETWORK_CONTEXT' => null,
    ], static fn () => (new NetworkContextDetector())->detect(['REMOTE_ADDR' => '8.8.8.8']));

    $suite->assertSame('external', $context->scope);
});

$suite->test('diagnostic query is ignored when diagnostic mode is disabled', function () use ($suite): void {
    $context = $suite->withEnvironment([
        'PROXIMADECK_DIAGNOSTIC_MODE' => 'false',
        'PROXIMADECK_NETWORK_CONTEXT' => null,
    ], static fn () => (new NetworkContextDetector())->detect(
        ['REMOTE_ADDR' => '8.8.8.8'],
        ['context' => 'internal']
    ));

    $suite->assertSame('external', $context->scope);
    $suite->assertSame('private_ip', $context->method);
});

$suite->test('diagnostic query overrides detection only when explicitly enabled', function () use ($suite): void {
    $context = $suite->withEnvironment([
        'PROXIMADECK_DIAGNOSTIC_MODE' => 'true',
        'PROXIMADECK_NETWORK_CONTEXT' => 'internal',
    ], static fn () => (new NetworkContextDetector())->detect(
        ['REMOTE_ADDR' => '192.168.1.42'],
        ['context' => 'external']
    ));

    $suite->assertSame('external', $context->scope);
    $suite->assertSame('diagnostic_query', $context->method);
});

$suite->test('environment override is applied outside diagnostic mode', function () use ($suite): void {
    $context = $suite->withEnvironment([
        'PROXIMADECK_DIAGNOSTIC_MODE' => null,
        'PROXIMADECK_NETWORK_CONTEXT' => 'external',
    ], static fn () => (new NetworkContextDetector())->detect(['REMOTE_ADDR' => '192.168.1.42']));

    $suite->assertSame('external', $context->scope);
    $suite->assertSame('environment', $context->method);
});
