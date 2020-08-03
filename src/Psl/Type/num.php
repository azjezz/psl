<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<int|float>
 */
function num(): Type
{
    return new Internal\NumType();
}
