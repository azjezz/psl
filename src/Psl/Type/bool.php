<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<bool>
 */
function bool(): TypeInterface
{
    return new Internal\BoolType();
}
