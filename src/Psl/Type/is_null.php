<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is null.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true null $var
 *
 * @psalm-pure
 */
function is_null($var): bool
{
    return null === $var;
}
