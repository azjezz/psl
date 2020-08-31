<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is a string.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true string $var
 *
 * @psalm-pure
 */
function is_string($var): bool
{
    return \is_string($var);
}
