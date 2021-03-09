<?php

declare(strict_types=1);

namespace Psl;

use Psl\Exception\InvariantViolationException;

/**
 * @param int|float|string ...$args
 *
 * @psalm-assert true $fact
 *
 * @pure
 *
 * @throws InvariantViolationException
 */
function invariant(bool $fact, string $message, ...$args): void
{
    if (!$fact) {
        invariant_violation($message, ...$args);
    }
}
