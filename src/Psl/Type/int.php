<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<int>
 */
function int(): Type
{
    return new Internal\IntType();
}
