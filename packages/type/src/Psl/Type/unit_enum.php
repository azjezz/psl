<?php

declare(strict_types=1);

namespace Psl\Type;

use UnitEnum;

/**
 * @pure
 *
 * @param class-string<T> $enum
 *
 * @api
 */
function unit_enum<T: UnitEnum>(string $enum): TypeInterface<T>
{
    return new Internal\UnitEnumType::<T>($enum);
}
