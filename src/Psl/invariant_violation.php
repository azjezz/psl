<?php

declare(strict_types=1);

namespace Psl;

/**
 * @psalm-param  int|float|string  ...$args
 *
 * @psalm-return no-return
 *
 * @psalm-pure
 *
 * @throws Exception\InvariantViolationException
 */
function invariant_violation(string $message, ...$args): void
{
    throw new Exception\InvariantViolationException(Str\format($message, ...$args));
}
