<?php

declare(strict_types=1);

namespace Psl;

/**
 * @psalm-param int|float|string  ...$args
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
 * @psalm-param int|float|string  ...$args
 *
 * @psalm-return no-return
 *
 * @throws Exception\InvariantViolationException
 */
function invariant_violation(string $format, ...$args): void
{
    throw new Exception\InvariantViolationException(Str\format($format, ...$args));
}
