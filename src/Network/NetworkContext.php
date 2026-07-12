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
        ];
    }
}
