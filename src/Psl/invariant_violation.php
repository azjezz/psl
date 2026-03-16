<?php

declare(strict_types=1);

namespace Psl;

use function sprintf;

/**
 * @param int|float|string ...$args
 *
 * @throws Exception\InvariantViolationException
 *
 * @pure
 */
function invariant_violation(string $message, mixed ...$args): never
{
    throw new Exception\InvariantViolationException(sprintf($message, ...$args));
}
