<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<int|float>
 */
function num(): TypeInterface
{
    return new Internal\NumType();
}
