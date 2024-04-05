<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<float>
 */
function float(): TypeInterface
{
    /** @var Internal\FloatType $instance */
    static $instance = new Internal\FloatType();

    return $instance;
}
