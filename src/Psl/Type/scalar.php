<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<string|bool|int|float>
 */
function scalar(): TypeInterface
{
    return new Internal\ScalarType();
}
