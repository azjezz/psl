<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param class-string $classname
 *
 * @return TypeInterface<non-empty-string>
 */
function private_property_name_of(string $classname): TypeInterface
{
    return new Internal\PrivatePropertyNameOfType($classname);
}
