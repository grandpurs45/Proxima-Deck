<?php

declare(strict_types=1);

namespace ProximaDeck\Network;

final class NetworkContext
{
    public function __construct(
        public readonly string $scope,
        public readonly string $clientIp,
        public readonly string $method
    ) {
    }

    public function isInternal(): bool
    {
        return $this->scope === 'internal';
    }

    public function toArray(): array
    {
        return [
            'scope' => $this->scope,
            'client_ip' => $this->clientIp,
            'method' => $this->method,
            'diagnostic_enabled' => $this->diagnosticEnabled(),
        ];
    }

    private function diagnosticEnabled(): bool
    {
        return filter_var(\env_value('PROXIMADECK_DIAGNOSTIC_MODE', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
