<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<array-key>
 */
function array_key(): TypeInterface
{
    return new Internal\ArrayKeyType();
}
