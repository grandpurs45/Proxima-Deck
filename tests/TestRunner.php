<?php

declare(strict_types=1);

namespace ProximaDeck\Tests;

use RuntimeException;
use Throwable;

final class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function test(string $name, callable $test): void
    {
        try {
            $test();
            $this->passed++;
            fwrite(STDOUT, sprintf("[PASS] %s\n", $name));
        } catch (Throwable $exception) {
            $this->failed++;
            fwrite(STDERR, sprintf("[FAIL] %s\n       %s\n", $name, $exception->getMessage()));
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected === $actual) {
            return;
        }

        throw new RuntimeException($message !== '' ? $message : sprintf(
            'Expected %s, got %s.',
            var_export($expected, true),
            var_export($actual, true)
        ));
    }

    public function assertTrue(bool $condition, string $message = 'Expected condition to be true.'): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    public function withEnvironment(array $variables, callable $callback): mixed
    {
        $previous = [];

        foreach ($variables as $key => $value) {
            $previous[$key] = getenv($key);
            $this->setEnvironment($key, $value);
        }

        try {
            return $callback();
        } finally {
            foreach ($previous as $key => $value) {
                $this->setEnvironment($key, $value === false ? null : $value);
            }
        }
    }

    public function finish(): int
    {
        fwrite(STDOUT, sprintf("\n%d passed, %d failed.\n", $this->passed, $this->failed));

        return $this->failed === 0 ? 0 : 1;
    }

    private function setEnvironment(string $key, ?string $value): void
    {
        putenv($value === null ? $key : sprintf('%s=%s', $key, $value));
    }
}
