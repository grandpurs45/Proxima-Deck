<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class SimpleYamlParser
{
    /**
     * Parses the limited YAML shape used by ProximaDeck V1:
     *
     * applications:
     *   - id: example
     *     name: Example
     */
    public function parseApplicationsFile(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Configuration file not found: %s', $path));
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException(sprintf('Unable to read configuration file: %s', $path));
        }

        $applications = [];
        $current = null;
        $insideApplications = false;

        foreach ($lines as $line) {
            $line = preg_replace('/\s+#.*$/', '', rtrim($line)) ?? '';

            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            if (trim($line) === 'applications:') {
                $insideApplications = true;
                continue;
            }

            if (!$insideApplications) {
                continue;
            }

            if (preg_match('/^\s*-\s+([A-Za-z0-9_]+):\s*(.*)$/', $line, $match) === 1) {
                if ($current !== null) {
                    $applications[] = $current;
                }

                $current = [
                    $match[1] => $this->parseScalar($match[2]),
                ];
                continue;
            }

            if (preg_match('/^\s+([A-Za-z0-9_]+):\s*(.*)$/', $line, $match) === 1 && $current !== null) {
                $current[$match[1]] = $this->parseScalar($match[2]);
            }
        }

        if ($current !== null) {
            $applications[] = $current;
        }

        return $applications;
    }

    private function parseScalar(string $value): ?string
    {
        $value = trim($value);

        if ($value === '' || strtolower($value) === 'null') {
            return null;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
