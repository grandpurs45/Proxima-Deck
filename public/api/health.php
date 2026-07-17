<?php

declare(strict_types=1);

use ProximaDeck\ApplicationPresenter;
use ProximaDeck\Config\ApplicationConfigValidator;
use ProximaDeck\Config\ApplicationRepository;
use ProximaDeck\Health\ServiceHealthChecker;
use ProximaDeck\IconResolver;
use ProximaDeck\Network\NetworkContextDetector;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $configPath = env_value('PROXIMADECK_CONFIG', dirname(__DIR__, 2) . '/config/applications.yaml');
    $repository = new ApplicationRepository($configPath);
    $validation = (new ApplicationConfigValidator(dirname(__DIR__) . '/assets/icons'))->validate($repository->raw());

    if ($validation->hasErrors()) {
        http_response_code(422);
        echo json_encode(['error' => 'configuration_invalid'], JSON_THROW_ON_ERROR);
        exit;
    }

    $context = (new NetworkContextDetector())->detect($_SERVER, $_GET);
    $applications = (new ApplicationPresenter(new IconResolver(dirname(__DIR__) . '/assets/icons')))
        ->visibleApplications($repository->all(), $context);
    $checker = new ServiceHealthChecker(
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proximadeck-health',
        max(5, min(3600, (int) env_value('PROXIMADECK_HEALTH_CACHE_TTL', '60'))),
        max(250, min(5000, (int) env_value('PROXIMADECK_HEALTH_TIMEOUT_MS', '1500')))
    );
    $applications = $checker->withHealth($applications);

    echo json_encode(
        ['statuses' => array_column($applications, 'health_status', 'id')],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Throwable) {
    http_response_code(500);
    echo json_encode(['error' => 'health_unavailable'], JSON_THROW_ON_ERROR);
}
