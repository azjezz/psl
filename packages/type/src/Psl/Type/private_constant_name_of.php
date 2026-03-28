<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param class-string $classname
 *
 * @return TypeInterface<non-empty-string>
 *
 * @api
 */
function private_constant_name_of(string $classname): TypeInterface
{
    return new Internal\PrivateConstantNameOfType($classname);
}
