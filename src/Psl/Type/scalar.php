<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<string|bool|int|float>
 */
function scalar(): Type
{
    return new Internal\ScalarType();
}
