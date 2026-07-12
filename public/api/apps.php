<?php

declare(strict_types=1);

use ProximaDeck\ApplicationPresenter;
use ProximaDeck\Config\ApplicationRepository;
use ProximaDeck\Network\NetworkContextDetector;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $configPath = env_value('PROXIMADECK_CONFIG', dirname(__DIR__, 2) . '/config/applications.yaml');
    $repository = new ApplicationRepository($configPath);
    $context = (new NetworkContextDetector())->detect($_SERVER);
    $applications = (new ApplicationPresenter())->visibleApplications($repository->all(), $context);

    echo json_encode(
        [
            'network' => $context->toArray(),
            'applications' => $applications,
        ],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode(
        [
            'error' => 'configuration_error',
            'message' => $exception->getMessage(),
        ],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
}
