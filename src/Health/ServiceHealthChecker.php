<?php

declare(strict_types=1);

namespace ProximaDeck\Health;

use Closure;

final class ServiceHealthChecker
{
    private const STATUSES = ['up', 'down', 'unknown'];

    private readonly Closure $probe;

    public function __construct(
        private readonly string $cacheDirectory,
        private readonly int $cacheTtlSeconds = 60,
        private readonly int $timeoutMs = 1500,
        ?Closure $probe = null,
        private readonly HttpHealthClassifier $classifier = new HttpHealthClassifier()
    ) {
        $this->probe = $probe ?? Closure::fromCallable([$this, 'probeWithCurl']);
    }

    public function withHealth(array $applications): array
    {
        $statuses = [];
        $pendingUrls = [];

        foreach ($applications as $application) {
            $id = (string) ($application['id'] ?? '');
            $url = trim((string) ($application['resolved_url'] ?? ''));
            $requestUrl = $this->requestUrl($url);

            if (($application['healthcheck'] ?? true) !== true || !$this->supports($requestUrl)) {
                $statuses[$id] = 'unknown';
                continue;
            }

            $cached = $this->readCache($requestUrl);

            if ($cached !== null) {
                $statuses[$id] = $cached;
                continue;
            }

            $pendingUrls[$requestUrl] = true;
        }

        if ($pendingUrls !== []) {
            $freshStatuses = ($this->probe)(array_keys($pendingUrls), $this->timeoutMs);

            foreach ($freshStatuses as $url => $status) {
                $status = $this->normalizeStatus($status);
                $this->writeCache((string) $url, $status);

                foreach ($applications as $application) {
                    if ($this->requestUrl((string) ($application['resolved_url'] ?? '')) === $url) {
                        $statuses[(string) ($application['id'] ?? '')] = $status;
                    }
                }
            }
        }

        return array_map(static function (array $application) use ($statuses): array {
            $application['health_status'] = $statuses[(string) ($application['id'] ?? '')] ?? 'unknown';

            return $application;
        }, $applications);
    }

    private function probeWithCurl(array $urls, int $timeoutMs): array
    {
        if (!function_exists('curl_multi_init')) {
            return array_fill_keys($urls, 'unknown');
        }

        $multiHandle = curl_multi_init();
        $handles = [];

        foreach ($urls as $url) {
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_CONNECTTIMEOUT_MS => min(750, $timeoutMs),
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_NOBODY => true,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT_MS => $timeoutMs,
                CURLOPT_USERAGENT => 'ProximaDeck-Health/1.0',
            ]);

            curl_multi_add_handle($multiHandle, $handle);
            $handles[$url] = $handle;
        }

        do {
            $result = curl_multi_exec($multiHandle, $running);

            if ($running > 0) {
                $selected = curl_multi_select($multiHandle, 0.2);

                if ($selected === -1) {
                    usleep(10000);
                }
            }
        } while ($running > 0 && $result === CURLM_OK);

        $statuses = [];

        foreach ($handles as $url => $handle) {
            $httpCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $statuses[$url] = $this->classifier->classify(curl_errno($handle), $httpCode);
            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }

        curl_multi_close($multiHandle);

        return $statuses;
    }

    private function supports(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function requestUrl(string $url): string
    {
        $fragmentPosition = strpos($url, '#');

        return $fragmentPosition === false ? $url : substr($url, 0, $fragmentPosition);
    }

    private function readCache(string $url): ?string
    {
        $path = $this->cachePath($url);

        if (!is_file($path) || filemtime($path) < time() - $this->cacheTtlSeconds) {
            return null;
        }

        $status = trim((string) file_get_contents($path));

        return in_array($status, self::STATUSES, true) ? $status : null;
    }

    private function writeCache(string $url, string $status): void
    {
        if (!is_dir($this->cacheDirectory) && !@mkdir($this->cacheDirectory, 0775, true) && !is_dir($this->cacheDirectory)) {
            return;
        }

        @file_put_contents($this->cachePath($url), $status, LOCK_EX);
    }

    private function cachePath(string $url): string
    {
        return rtrim($this->cacheDirectory, '/\\') . DIRECTORY_SEPARATOR . hash('sha256', $url) . '.status';
    }

    private function normalizeStatus(mixed $status): string
    {
        return in_array($status, self::STATUSES, true) ? $status : 'unknown';
    }
}
