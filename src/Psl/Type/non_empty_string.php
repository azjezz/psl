<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<non-empty-string>
 */
function non_empty_string(): Type
{
    return new Internal\NonEmptyStringType();
}
