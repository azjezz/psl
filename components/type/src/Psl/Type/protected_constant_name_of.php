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
function protected_constant_name_of(string $classname): TypeInterface
{
    return new Internal\ProtectedConstantNameOfType($classname);
}
