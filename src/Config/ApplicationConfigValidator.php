<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class ApplicationConfigValidator
{
    private const VISIBILITIES = ['internal', 'external', 'both'];

    public function __construct(private readonly string $iconDirectory)
    {
    }

    public function validate(array $applications): ConfigValidationResult
    {
        $issues = [];
        $seenIds = [];

        if ($applications === []) {
            $issues[] = new ConfigValidationIssue(
                'error',
                'no_applications',
                'Aucune application trouvee dans la configuration.'
            );
        }

        foreach ($applications as $index => $application) {
            $fallbackId = sprintf('application_%d', $index + 1);
            $id = trim((string) ($application['id'] ?? ''));
            $name = trim((string) ($application['name'] ?? ''));
            $visibility = strtolower(trim((string) ($application['visibility'] ?? 'both')));
            $applicationRef = $id !== '' ? $id : $fallbackId;

            if ($id === '') {
                $issues[] = $this->error('missing_id', 'Le champ id est obligatoire.', $applicationRef, 'id');
            } elseif (preg_match('/^[a-zA-Z0-9._-]+$/', $id) !== 1) {
                $issues[] = $this->error('invalid_id', 'Le champ id doit contenir uniquement lettres, chiffres, points, tirets ou underscores.', $applicationRef, 'id');
            } elseif (isset($seenIds[$id])) {
                $issues[] = $this->error('duplicate_id', sprintf('L id "%s" est declare plusieurs fois.', $id), $applicationRef, 'id');
            } else {
                $seenIds[$id] = true;
            }

            if ($name === '') {
                $issues[] = $this->error('missing_name', 'Le champ name est obligatoire.', $applicationRef, 'name');
            }

            if (!in_array($visibility, self::VISIBILITIES, true)) {
                $issues[] = $this->error('invalid_visibility', 'Le champ visibility doit valoir internal, external ou both.', $applicationRef, 'visibility');
            }

            foreach (['internal_url', 'external_url'] as $field) {
                $url = $application[$field] ?? null;

                if ($url === null || trim((string) $url) === '') {
                    continue;
                }

                if (filter_var((string) $url, FILTER_VALIDATE_URL) === false) {
                    $issues[] = $this->error('invalid_url', sprintf('Le champ %s doit etre une URL valide.', $field), $applicationRef, $field);
                }
            }

            if ($visibility === 'internal' && trim((string) ($application['internal_url'] ?? '')) === '') {
                $issues[] = $this->error('missing_internal_url', 'Une application internal doit avoir internal_url.', $applicationRef, 'internal_url');
            }

            if ($visibility === 'external' && trim((string) ($application['external_url'] ?? '')) === '') {
                $issues[] = $this->error('missing_external_url', 'Une application external doit avoir external_url.', $applicationRef, 'external_url');
            }

            if ($visibility === 'both' && trim((string) ($application['internal_url'] ?? '')) === '' && trim((string) ($application['external_url'] ?? '')) === '') {
                $issues[] = $this->error('missing_url', 'Une application both doit avoir au moins internal_url ou external_url.', $applicationRef, 'internal_url');
            }

            if (array_key_exists('healthcheck', $application) && filter_var($application['healthcheck'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
                $issues[] = $this->error('invalid_healthcheck', 'Le champ healthcheck doit valoir true ou false.', $applicationRef, 'healthcheck');
            }

            $icon = trim((string) ($application['icon'] ?? ''));

            if ($icon !== '') {
                if ($icon !== basename($icon) || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*(?:\.(?:svg|png|webp))?$/i', $icon) !== 1) {
                    $issues[] = $this->error('invalid_icon_name', 'Le champ icon doit etre un nom Dashboard Icons ou un fichier local sans chemin.', $applicationRef, 'icon');
                }
            }
        }

        return new ConfigValidationResult($issues);
    }

    private function error(string $code, string $message, string $applicationId, string $field): ConfigValidationIssue
    {
        return new ConfigValidationIssue('error', $code, $message, $applicationId, $field);
    }
}
