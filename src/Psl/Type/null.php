<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<null>
 */
function null(): Type
{
    return new Internal\NullType();
}
