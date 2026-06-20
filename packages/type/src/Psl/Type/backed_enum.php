<?php

declare(strict_types=1);

namespace Psl\Type;

use BackedEnum;

/**
 * @pure
 *
 * @param class-string<T> $enum
 *
 * @return TypeInterface<T>
 *
 * @api
 */
function backed_enum<T : BackedEnum>(string $enum): TypeInterface<T>
{
    return new Internal\BackedEnumType($enum);
}
