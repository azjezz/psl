<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<string>
 */
function string(): TypeInterface
{
    return new Internal\StringType();
}
