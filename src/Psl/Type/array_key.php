<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<array-key>
 */
function array_key(): TypeInterface
{
    /** @var Internal\ArrayKeyType $instance */
    static $instance = new Internal\ArrayKeyType();

    return $instance;
}
