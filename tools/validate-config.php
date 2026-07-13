<?php

declare(strict_types=1);

use ProximaDeck\Config\ApplicationConfigValidator;
use ProximaDeck\Config\ApplicationRepository;

require dirname(__DIR__) . '/src/bootstrap.php';

$configPath = $argv[1] ?? env_value('PROXIMADECK_CONFIG', dirname(__DIR__) . '/config/applications.yaml');
$repository = new ApplicationRepository($configPath);
$validator = new ApplicationConfigValidator(dirname(__DIR__) . '/public/assets/icons');

try {
    $applications = $repository->raw();
    $result = $validator->validate($applications);
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf("ERROR configuration_read_failed: %s\n", $exception->getMessage()));
    exit(1);
}

if ($result->issues() === []) {
    echo sprintf("OK: %d application(s) valide(s).\n", count($applications));
    exit(0);
}

foreach ($result->issues() as $issue) {
    $target = $issue->applicationId !== null ? sprintf(' app=%s', $issue->applicationId) : '';
    $field = $issue->field !== null ? sprintf(' field=%s', $issue->field) : '';

    echo sprintf(
        "%s %s:%s%s %s\n",
        strtoupper($issue->level),
        $issue->code,
        $target,
        $field,
        $issue->message
    );
}

exit($result->hasErrors() ? 1 : 0);
