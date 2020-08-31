<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is a resource.
 *
 * To verify the resource type, use `Type\resource($type)->assert($var)` instead.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true resource $var
 *
 * @psalm-pure
 */
function is_resource($var): bool
{
    return \is_resource($var);
}
