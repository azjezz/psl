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
 * @param string $format
 * @param array $args
 * @throws Exception\InvariantViolationException
 */
function invariant_violation(string $format, ...$args): void
{
    throw new Exception\InvariantViolationException(Str\format($format, ...$args));
}
