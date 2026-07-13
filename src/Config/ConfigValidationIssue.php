<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class ConfigValidationIssue
{
    public function __construct(
        public readonly string $level,
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $applicationId = null,
        public readonly ?string $field = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'code' => $this->code,
            'message' => $this->message,
            'application_id' => $this->applicationId,
            'field' => $this->field,
        ];
    }
}
