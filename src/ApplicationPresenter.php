<?php

declare(strict_types=1);

namespace ProximaDeck;

use ProximaDeck\Network\NetworkContext;

final class ApplicationPresenter
{
    public function visibleApplications(array $applications, NetworkContext $context): array
    {
        $visible = array_filter($applications, static function (array $application) use ($context): bool {
            return match ($application['visibility']) {
                'internal' => $context->isInternal(),
                'external' => !$context->isInternal(),
                default => true,
            };
        });

        return array_values(array_map(
            static fn (array $application): array => self::withResolvedUrl($application, $context),
            $visible
        ));
    }

    private static function withResolvedUrl(array $application, NetworkContext $context): array
    {
        $primary = $context->isInternal() ? $application['internal_url'] : $application['external_url'];
        $fallback = $context->isInternal() ? $application['external_url'] : $application['internal_url'];

        $application['resolved_url'] = $primary ?: $fallback;
        $application['resolved_target'] = $primary
            ? ($context->isInternal() ? 'internal' : 'external')
            : 'fallback';

        return $application;
    }
}
