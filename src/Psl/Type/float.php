<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<float>
 */
function float(): TypeInterface
{
    return new Internal\FloatType();
}
