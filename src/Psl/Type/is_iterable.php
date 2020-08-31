<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is an iterable.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true iterable $var
 *
 * @psalm-pure
 */
function is_iterable($var): bool
{
    return \is_iterable($var);
}
