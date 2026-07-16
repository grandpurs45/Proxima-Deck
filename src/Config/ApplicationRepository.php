<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class ApplicationRepository
{
    public function __construct(
        private readonly string $configPath,
        private readonly SimpleYamlParser $parser = new SimpleYamlParser()
    ) {
    }

    public function all(): array
    {
        $applications = $this->raw();

        $normalized = array_map(
            static fn (array $application): array => self::normalize($application),
            $applications
        );

        usort($normalized, static function (array $left, array $right): int {
            return [$left['order'], $left['name']] <=> [$right['order'], $right['name']];
        });

        return $normalized;
    }

    public function raw(): array
    {
        return $this->parser->parseApplicationsFile($this->configPath);
    }

    private static function normalize(array $application): array
    {
        $visibility = strtolower((string) ($application['visibility'] ?? 'both'));

        if (!in_array($visibility, ['internal', 'external', 'both'], true)) {
            $visibility = 'both';
        }

        return [
            'id' => (string) ($application['id'] ?? self::slug((string) ($application['name'] ?? 'application'))),
            'name' => (string) ($application['name'] ?? 'Application'),
            'description' => (string) ($application['description'] ?? ''),
            'category' => (string) ($application['category'] ?? 'Outils'),
            'icon' => (string) ($application['icon'] ?? ''),
            'internal_url' => $application['internal_url'] ?? null,
            'external_url' => $application['external_url'] ?? null,
            'visibility' => $visibility,
            'order' => (int) ($application['order'] ?? 100),
        ];
    }

    private static function slug(string $value): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '', '-'));

        return $slug !== '' ? $slug : 'application';
    }
}
