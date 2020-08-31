<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is an object.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true object $var
 *
 * @psalm-pure
 */
function is_object($var): bool
{
    return \is_object($var);
}
