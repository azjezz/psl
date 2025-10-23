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
function public_constant_name_of(string $classname): TypeInterface
{
    return new Internal\PublicConstantNameOfType($classname);
}
