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
