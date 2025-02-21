<?php

declare(strict_types=1);

namespace Psl;

/**
 * @param int|float|string ...$args
 *
 * @throws Exception\InvariantViolationException
 *
 * @pure
 */
function invariant_violation(string $message, mixed ...$args): never
{
    throw new Exception\InvariantViolationException(Str\format($message, ...$args));
}
