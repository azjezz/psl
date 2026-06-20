<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<array<array-key, mixed>>
 *
 * @api
 */
function mixed_dict(): TypeInterface<array>
{
    return new Internal\MixedDictType();
}
