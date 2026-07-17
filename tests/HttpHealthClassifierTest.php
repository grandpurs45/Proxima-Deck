<?php

declare(strict_types=1);

use ProximaDeck\Health\HttpHealthClassifier;
use ProximaDeck\Tests\TestRunner;

/** @var TestRunner $suite */

$classifier = new HttpHealthClassifier();

$suite->test('health classifier accepts successful protected redirected and unsupported HEAD responses', function () use ($suite, $classifier): void {
    $suite->assertSame('up', $classifier->classify(0, 200));
    $suite->assertSame('up', $classifier->classify(0, 401));
    $suite->assertSame('up', $classifier->classify(0, 302));
    $suite->assertSame('up', $classifier->classify(0, 501));
});

$suite->test('health classifier rejects network and server failures', function () use ($suite, $classifier): void {
    $suite->assertSame('down', $classifier->classify(7, 0));
    $suite->assertSame('down', $classifier->classify(0, 500));
    $suite->assertSame('down', $classifier->classify(0, 503));
});
