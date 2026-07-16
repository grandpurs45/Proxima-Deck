<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require __DIR__ . '/TestRunner.php';

use ProximaDeck\Tests\TestRunner;

$suite = new TestRunner();

require __DIR__ . '/NetworkContextDetectorTest.php';
require __DIR__ . '/ApplicationPresenterTest.php';
require __DIR__ . '/ApplicationRepositoryTest.php';
require __DIR__ . '/ApplicationConfigValidatorTest.php';
require __DIR__ . '/IconResolverTest.php';

exit($suite->finish());
