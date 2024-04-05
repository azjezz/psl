<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @return TypeInterface<array<array-key, mixed>>
 */
function mixed_dict(): TypeInterface
{
    return new Internal\MixedDictType();
}
