<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<u32>
 *
 * @return TypeInterface<int<0, 4294967295>>
 */
function u32(): TypeInterface
{
    return new Internal\U32Type();
}
