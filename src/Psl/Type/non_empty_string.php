<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<non-empty-string>
 */
function non_empty_string(): TypeInterface
{
    return new Internal\NonEmptyStringType();
}
