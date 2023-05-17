<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<numeric-string>
 */
function numeric_string(): TypeInterface
{
    return new Internal\NumericStringType();
}
