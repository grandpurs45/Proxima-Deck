<?php

declare(strict_types=1);

namespace ProximaDeck;

use ProximaDeck\Network\NetworkContext;

final class ApplicationPresenter
{
    public function __construct(private readonly IconResolver $iconResolver)
    {
    }

    public function visibleApplications(array $applications, NetworkContext $context): array
    {
        $visible = array_filter($applications, static function (array $application) use ($context): bool {
            $visibility = (string) ($application['visibility'] ?? 'both');

            if ($context->isInternal()) {
                return $visibility !== 'external';
            }

            if ($visibility === 'internal') {
                return false;
            }

            // Never expose an internal target as a fallback to an external client.
            return trim((string) ($application['external_url'] ?? '')) !== '';
        });

        return array_values(array_map(
            fn (array $application): array => $this->withPresentationData($application, $context),
            $visible
        ));
    }

    private function withPresentationData(array $application, NetworkContext $context): array
    {
        return array_merge(
            $this->withResolvedUrl($application, $context),
            $this->iconResolver->resolve($application)
        );
    }

    private function withResolvedUrl(array $application, NetworkContext $context): array
    {
        $primary = $context->isInternal() ? $application['internal_url'] : $application['external_url'];
        $fallback = $context->isInternal() ? $application['external_url'] : '';

        $application['resolved_url'] = $primary ?: $fallback;
        $application['resolved_target'] = $primary
            ? ($context->isInternal() ? 'internal' : 'external')
            : 'fallback';

        return $application;
    }
}
