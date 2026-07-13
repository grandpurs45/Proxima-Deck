<?php

declare(strict_types=1);

namespace ProximaDeck;

final class IconResolver
{
    private const DEFAULT_ICON = 'default.svg';

    private const SERVICE_ICONS = [
        'homeassistant' => 'homeassistant.svg',
        'proxmox' => 'proxmox.svg',
        'umami' => 'umami.svg',
        'uptime-kuma' => 'uptime-kuma.svg',
        'vaultwarden' => 'vaultwarden.svg',
    ];

    private const CATEGORY_ICONS = [
        'developpement' => 'category-development.svg',
        'domotique' => 'category-home.svg',
        'infrastructure' => 'category-infrastructure.svg',
        'monitoring' => 'category-monitoring.svg',
        'multimedia' => 'category-media.svg',
        'outils' => 'category-tools.svg',
        'reseau' => 'category-network.svg',
        'stockage' => 'category-storage.svg',
        'web' => 'category-web.svg',
    ];

    public function __construct(private readonly string $iconDirectory)
    {
    }

    public function resolve(array $application): array
    {
        $configuredIcon = $this->safeIconName((string) ($application['icon'] ?? ''));

        if ($configuredIcon !== null && $this->exists($configuredIcon)) {
            return $this->result($configuredIcon, 'configured', 'Icone configuree');
        }

        $serviceIcon = self::SERVICE_ICONS[$this->slug((string) ($application['id'] ?? ''))] ?? null;

        if ($serviceIcon !== null && $this->exists($serviceIcon)) {
            return $this->result($serviceIcon, 'service', 'Icone service');
        }

        $categoryIcon = self::CATEGORY_ICONS[$this->slug((string) ($application['category'] ?? ''))] ?? null;

        if ($categoryIcon !== null && $this->exists($categoryIcon)) {
            return $this->result($categoryIcon, 'category', 'Icone categorie');
        }

        return $this->result(self::DEFAULT_ICON, 'default', 'Icone par defaut');
    }

    private function safeIconName(string $icon): ?string
    {
        $icon = trim($icon);

        if ($icon === '' || $icon !== basename($icon)) {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9._-]+\.svg$/', $icon) !== 1) {
            return null;
        }

        return $icon;
    }

    private function exists(string $icon): bool
    {
        return is_file($this->iconDirectory . DIRECTORY_SEPARATOR . $icon);
    }

    private function result(string $icon, string $source, string $label): array
    {
        return [
            'icon' => $icon,
            'icon_source' => $source,
            'icon_label' => $label,
        ];
    }

    private function slug(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($normalized === false) {
            $normalized = $value;
        }

        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $normalized) ?? '', '-'));

        return $slug !== '' ? $slug : 'default';
    }
}
