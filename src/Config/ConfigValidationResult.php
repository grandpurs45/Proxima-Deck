<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class ConfigValidationResult
{
    /**
     * @param ConfigValidationIssue[] $issues
     */
    public function __construct(private readonly array $issues)
    {
    }

    public function hasErrors(): bool
    {
        foreach ($this->issues as $issue) {
            if ($issue->level === 'error') {
                return true;
            }
        }

        return false;
    }

    public function hasWarnings(): bool
    {
        foreach ($this->issues as $issue) {
            if ($issue->level === 'warning') {
                return true;
            }
        }

        return false;
    }

    public function issues(): array
    {
        return $this->issues;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (ConfigValidationIssue $issue): array => $issue->toArray(),
            $this->issues
        );
    }
}
