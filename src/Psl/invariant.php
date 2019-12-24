<?php

declare(strict_types=1);

namespace Psl;

/**
 * @psalm-param int|float|string|bool  ...$args
 *
 * @psalm-assert true $fact
 */
function invariant(bool $fact, string $message, ...$args): void
{
    if (!$fact) {
        invariant_violation($message, ...$args);
    }
}

/**
 * @psalm-param int|float|string|bool  ...$args
 *
 * @pslam-return no-return
 */
function invariant_violation(string $format, ...$args): void
{
    throw new Exception\InvariantViolationException(Str\format($format, ...$args));
}
