<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<array-key>
 */
function array_key(): Type
{
    return new Internal\ArrayKeyType();
}
