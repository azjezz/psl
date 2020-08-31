<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is a callable.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true callable $var
 *
 * @psalm-pure
 */
function is_callable($var): bool
{
    return \is_callable($var);
}
