<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int|float>
 */
function num(): TypeInterface
{
    /** @var Internal\NumType $instance */
    static $instance = new Internal\NumType();

    return $instance;
}
