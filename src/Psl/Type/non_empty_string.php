<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<non-empty-string>
 */
function non_empty_string(): TypeInterface
{
    /** @var Internal\NonEmptyStringType $instance */
    static $instance = new Internal\NonEmptyStringType();

    return $instance;
}
