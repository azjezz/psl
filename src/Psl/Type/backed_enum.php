<?php

declare(strict_types=1);

namespace Psl\Type;

use BackedEnum;

/**
 * @template T of BackedEnum
 *
 * @param class-string<T> $enum
 *
 * @return TypeInterface<T>
 */
function backed_enum(string $enum): TypeInterface
{
    return new Internal\BackedEnumType($enum);
}
