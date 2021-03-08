<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns `true` if `$string` is null or the empty string.
 * Returns `false` otherwise.
 *
 * Example:
 *
 *      Str\is_empty('')
 *      => Bool(true)
 *
 *      Str\is_empty(' ')
 *      => Bool(false)
 *
 *      Str\is_empty(null)
 *      => Bool(true)
 *
 *      Str\is_empty('hello')
 *      => Bool(false)
 *
 * @psalm-assert-if-false non-empty-string $string
 *
 * @pure
 */
function is_empty(?string $string): bool
{
    return null === $string || '' === $string;
}
