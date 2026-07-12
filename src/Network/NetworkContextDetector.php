<?php

declare(strict_types=1);

namespace ProximaDeck\Network;

final class NetworkContextDetector
{
    public function detect(array $server): NetworkContext
    {
        $forcedContext = strtolower((string) \env_value('PROXIMADECK_NETWORK_CONTEXT', ''));
        $clientIp = $this->clientIp($server);

        if (in_array($forcedContext, ['internal', 'external'], true)) {
            return new NetworkContext($forcedContext, $clientIp, 'environment');
        }

        $proxyContext = strtolower((string) ($server['HTTP_X_PROXIMADECK_CONTEXT'] ?? ''));

        if (in_array($proxyContext, ['internal', 'external'], true)) {
            return new NetworkContext($proxyContext, $clientIp, 'reverse_proxy');
        }

        return new NetworkContext(
            $this->isPrivateIp($clientIp) ? 'internal' : 'external',
            $clientIp,
            'private_ip'
        );
    }

    private function clientIp(array $server): string
    {
        $forwardedFor = (string) ($server['HTTP_X_FORWARDED_FOR'] ?? '');

        if ($forwardedFor !== '') {
            $ips = array_map('trim', explode(',', $forwardedFor));

            if ($ips[0] !== '') {
                return $ips[0];
            }
        }

        return (string) ($server['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    private function isPrivateIp(string $ip): bool
    {
        if ($ip === '::1') {
            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
