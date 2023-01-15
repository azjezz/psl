<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<uint>
 *
 * @return TypeInterface<int<0, max>>
 */
function uint(): TypeInterface
{
    return new Internal\UIntType();
}
