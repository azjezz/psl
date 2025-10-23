<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param class-string $enumname
 *
 * @return TypeInterface<non-empty-string>
 */
function enum_case_of(string $enumname): TypeInterface
{
    return new Internal\EnumCaseOfType($enumname);
}
