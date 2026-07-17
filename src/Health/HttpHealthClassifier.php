<?php

declare(strict_types=1);

namespace ProximaDeck\Health;

final class HttpHealthClassifier
{
    public function classify(int $curlError, int $httpCode): string
    {
        if ($curlError !== 0 || $httpCode <= 0) {
            return 'down';
        }

        // 501 means the service answered but does not implement our HEAD probe.
        return $httpCode < 500 || $httpCode === 501 ? 'up' : 'down';
    }
}
