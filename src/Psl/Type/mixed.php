<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<mixed>
 */
function mixed(): TypeInterface
{
    return new Internal\MixedType();
}
