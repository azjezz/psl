<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Find whether the type of a variable is string.
 *
 * Example:
 *
 *      Str\is_string(false)
 *      => Bool(false)
 *
 *      Str\is_string('hello')
 *      => Bool(true)
 *
 *      Str\is_string(55)
 *      => Bool(false)
 *
 * @param mixed $value
 *
 * @psalm-assert-if-true string $value
 */
function is_string($value): bool
{
    return \is_string($value);
}
