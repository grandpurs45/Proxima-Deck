<?php

declare(strict_types=1);

namespace ProximaDeck\Config;

final class ConfigValidationException extends \RuntimeException
{
    public function __construct(private readonly ConfigValidationResult $result)
    {
        parent::__construct('Configuration invalide. Corrigez config/applications.yaml puis rechargez ProximaDeck.');
    }

    public function result(): ConfigValidationResult
    {
        return $this->result;
    }
}
