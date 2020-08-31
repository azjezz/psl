<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is a boolean.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true bool $var
 *
 * @psalm-pure
 */
function is_bool($var): bool
{
    return \is_bool($var);
}
