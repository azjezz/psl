<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<non-empty-string>
 */
function non_empty_string(): TypeInterface
{
    /** @var Internal\NonEmptyStringType $instance */
    static $instance = new Internal\NonEmptyStringType();

    return $instance;
}
