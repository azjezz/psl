<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<list<mixed>>
 */
function mixed_vec(): TypeInterface
{
    return new Internal\MixedVecType();
}
