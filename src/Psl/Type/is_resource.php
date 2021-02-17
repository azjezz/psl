<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_resource as php_is_resource;

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
 *
 * @deprecated use `Type\resource($type)->matches($value)` instead.
 */
function is_resource($var): bool
{
    return php_is_resource($var);
}
