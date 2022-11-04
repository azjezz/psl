<?php

declare(strict_types=1);

namespace Psl\Type;

use UnitEnum;

/**
 * @template T of UnitEnum
 *
 * @param class-string<T> $enum
 *
 * @return TypeInterface<T>
 */
function unit_enum(string $enum): TypeInterface
{
    return new Internal\UnitEnumType($enum);
}
