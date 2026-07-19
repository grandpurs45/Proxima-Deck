<?php

declare(strict_types=1);

namespace ProximaDeck;

final class IconResolver
{
    private const SERVICE_ICONS = [
        'confluence' => 'confluence.svg',
        'homeassistant' => 'homeassistant.svg',
        'proxmox' => 'proxmox.svg',
        'umami' => 'umami.svg',
        'uptime-kuma' => 'uptime-kuma.svg',
        'vaultwarden' => 'vaultwarden.svg',
    ];

    private const MONOGRAM_TONES = ['cyan', 'green', 'amber', 'rose', 'blue'];

    public function __construct(private readonly string $iconDirectory)
    {
    }

    public function resolve(array $application): array
    {
        $configuredIcon = $this->safeIconReference((string) ($application['icon'] ?? ''));

        if ($configuredIcon !== null) {
            if ($this->hasExtension($configuredIcon) && $this->exists($configuredIcon)) {
                return $this->result(
                    $configuredIcon,
                    '/assets/icons/' . rawurlencode($configuredIcon),
                    'configured',
                    'Icone locale',
                    $application
                );
            }

            $dashboardName = $this->dashboardName($configuredIcon);

            return $this->result(
                $dashboardName . '.webp',
                '/api/icon.php?name=' . rawurlencode($dashboardName),
                'dashboard',
                'Dashboard Icons',
                $application
            );
        }

        $serviceIcon = self::SERVICE_ICONS[$this->slug((string) ($application['id'] ?? ''))] ?? null;

        if ($serviceIcon !== null && $this->exists($serviceIcon)) {
            return $this->result(
                $serviceIcon,
                '/assets/icons/' . rawurlencode($serviceIcon),
                'service',
                'Icone service',
                $application
            );
        }

        return $this->result('', '', 'monogram', 'Monogramme automatique', $application);
    }

    private function safeIconReference(string $icon): ?string
    {
        $icon = trim($icon);

        if ($icon === '' || $icon !== basename($icon)) {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*(?:\.(?:svg|png|webp))?$/i', $icon) !== 1) {
            return null;
        }

        return $icon;
    }

    private function hasExtension(string $icon): bool
    {
        return pathinfo($icon, PATHINFO_EXTENSION) !== '';
    }

    private function dashboardName(string $icon): string
    {
        return strtolower((string) pathinfo($icon, PATHINFO_FILENAME));
    }

    private function exists(string $icon): bool
    {
        return is_file($this->iconDirectory . DIRECTORY_SEPARATOR . $icon);
    }

    private function result(
        string $icon,
        string $iconUrl,
        string $source,
        string $label,
        array $application
    ): array
    {
        $identity = trim((string) ($application['name'] ?? $application['id'] ?? 'Application'));
        $toneIdentity = trim((string) ($application['id'] ?? $identity));

        return [
            'icon' => $icon,
            'icon_url' => $iconUrl,
            'icon_source' => $source,
            'icon_label' => $label,
            'icon_initials' => $this->initials($identity),
            'icon_tone' => $this->tone($toneIdentity),
        ];
    }

    private function initials(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $normalized = $normalized === false ? $value : $normalized;
        $words = preg_split('/[^a-zA-Z0-9]+/', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        $initials = strtoupper(substr($words[0] ?? 'AP', 0, 2));

        return $initials !== '' ? $initials : 'AP';
    }

    private function tone(string $value): string
    {
        $hash = (int) sprintf('%u', crc32(strtolower($value)));

        return self::MONOGRAM_TONES[$hash % count(self::MONOGRAM_TONES)];
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
