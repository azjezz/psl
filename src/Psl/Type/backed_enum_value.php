<?php

declare(strict_types=1);

namespace Psl\Type;

use BackedEnum;
use Psl\Exception\InvariantViolationException;
use Psl\Exception\RuntimeException;

/**
 * @psalm-pure
 *
 * @template T of BackedEnum
 *
 * @param class-string<T> $enum
 *
 * @throws RuntimeException If reflection fails.
 * @throws InvariantViolationException If the given value is not class-string<BackedEnum>.
 *
 * @return TypeInterface<value-of<T>>
 */
function backed_enum_value(string $enum): TypeInterface
{
    return new Internal\BackedEnumValueType($enum);
}
