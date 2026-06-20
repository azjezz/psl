<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<list<mixed>>
 *
 * @api
 */
function mixed_vec(): TypeInterface<array>
{
    return new Internal\MixedVecType();
}
