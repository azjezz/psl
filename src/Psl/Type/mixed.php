<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<mixed>
 */
function mixed(): TypeInterface
{
    /** @var Internal\MixedType $instance */
    static $instance = new Internal\MixedType();

    return $instance;
}
