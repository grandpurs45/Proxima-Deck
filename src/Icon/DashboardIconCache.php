<?php

declare(strict_types=1);

namespace ProximaDeck\Icon;

use Closure;

final class DashboardIconCache
{
    private const CDN_BASE_URL = 'https://cdn.jsdelivr.net/gh/homarr-labs/dashboard-icons/webp/';
    private const MAX_ICON_BYTES = 2_097_152;
    private const MISSING_TTL_SECONDS = 3600;

    private readonly Closure $fetch;

    public function __construct(
        private readonly string $cacheDirectory,
        private readonly int $cacheTtlSeconds = 2_592_000,
        private readonly int $timeoutMs = 3000,
        ?Closure $fetch = null
    ) {
        $this->fetch = $fetch ?? Closure::fromCallable([$this, 'fetchWithCurl']);
    }

    public function get(string $name): ?array
    {
        $name = $this->normalizeName($name);

        if ($name === null) {
            return null;
        }

        $cachePath = $this->cacheDirectory . DIRECTORY_SEPARATOR . $name . '.webp';
        $missingPath = $cachePath . '.missing';
        $cached = $this->read($cachePath, true);

        if ($cached !== null) {
            return $this->result($cached);
        }

        if ($this->isFreshMarker($missingPath)) {
            return null;
        }

        $fresh = ($this->fetch)(self::CDN_BASE_URL . rawurlencode($name) . '.webp', $this->timeoutMs);

        if (is_string($fresh) && $this->isValidWebp($fresh)) {
            $this->write($cachePath, $fresh);
            @unlink($missingPath);

            return $this->result($fresh);
        }

        $stale = $this->read($cachePath, false);

        if ($stale !== null) {
            return $this->result($stale);
        }

        $this->writeMarker($missingPath);

        return null;
    }

    private function normalizeName(string $name): ?string
    {
        $name = strtolower(trim($name));

        if (preg_match('/^[a-z0-9][a-z0-9._-]*$/', $name) !== 1) {
            return null;
        }

        return $name;
    }

    private function read(string $path, bool $freshOnly): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        if ($freshOnly && (int) filemtime($path) + $this->cacheTtlSeconds < time()) {
            return null;
        }

        $contents = file_get_contents($path);

        return is_string($contents) && $this->isValidWebp($contents) ? $contents : null;
    }

    private function write(string $path, string $contents): void
    {
        if (!$this->ensureCacheDirectory()) {
            return;
        }

        @file_put_contents($path, $contents, LOCK_EX);
    }

    private function writeMarker(string $path): void
    {
        if ($this->ensureCacheDirectory()) {
            @touch($path);
        }
    }

    private function isFreshMarker(string $path): bool
    {
        return is_file($path) && (int) filemtime($path) + self::MISSING_TTL_SECONDS >= time();
    }

    private function ensureCacheDirectory(): bool
    {
        return is_dir($this->cacheDirectory)
            || @mkdir($this->cacheDirectory, 0775, true)
            || is_dir($this->cacheDirectory);
    }

    private function result(string $contents): array
    {
        return [
            'contents' => $contents,
            'etag' => '"' . sha1($contents) . '"',
        ];
    }

    private function isValidWebp(string $contents): bool
    {
        $length = strlen($contents);

        return $length >= 12
            && $length <= self::MAX_ICON_BYTES
            && substr($contents, 0, 4) === 'RIFF'
            && substr($contents, 8, 4) === 'WEBP';
    }

    private function fetchWithCurl(string $url, int $timeoutMs): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_CONNECTTIMEOUT_MS => min(1500, $timeoutMs),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => $timeoutMs,
            CURLOPT_USERAGENT => 'ProximaDeck-Icons/1.0',
        ]);

        $contents = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($handle);
        curl_close($handle);

        return $error === 0 && $status === 200 && is_string($contents) ? $contents : null;
    }
}
