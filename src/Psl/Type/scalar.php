<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<string|bool|int|float>
 */
function scalar(): TypeInterface
{
    /** @var Internal\ScalarType $instance */
    static $instance = new Internal\ScalarType();

    return $instance;
}
