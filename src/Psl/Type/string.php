<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<string>
 */
function string(): Type
{
    return new Internal\StringType();
}
