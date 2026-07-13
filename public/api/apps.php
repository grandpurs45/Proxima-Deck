<?php

declare(strict_types=1);

use ProximaDeck\ApplicationPresenter;
use ProximaDeck\Config\ApplicationConfigValidator;
use ProximaDeck\Config\ApplicationRepository;
use ProximaDeck\Config\ConfigValidationException;
use ProximaDeck\IconResolver;
use ProximaDeck\Network\NetworkContextDetector;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $configPath = env_value('PROXIMADECK_CONFIG', dirname(__DIR__, 2) . '/config/applications.yaml');
    $repository = new ApplicationRepository($configPath);
    $rawApplications = $repository->raw();
    $validator = new ApplicationConfigValidator(dirname(__DIR__) . '/assets/icons');
    $validation = $validator->validate($rawApplications);

    if ($validation->hasErrors()) {
        throw new ConfigValidationException($validation);
    }

    $context = (new NetworkContextDetector())->detect($_SERVER);
    $iconResolver = new IconResolver(dirname(__DIR__) . '/assets/icons');
    $applications = (new ApplicationPresenter($iconResolver))->visibleApplications($repository->all(), $context);
    $versionPath = dirname(__DIR__, 2) . '/VERSION';
    $version = is_file($versionPath) ? trim((string) file_get_contents($versionPath)) : 'dev';

    echo json_encode(
        [
            'version' => $version,
            'network' => $context->toArray(),
            'validation' => [
                'warnings' => $validation->toArray(),
            ],
            'applications' => $applications,
        ],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (ConfigValidationException $exception) {
    http_response_code(422);

    echo json_encode(
        [
            'error' => 'configuration_invalid',
            'message' => $exception->getMessage(),
            'issues' => $exception->result()->toArray(),
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
