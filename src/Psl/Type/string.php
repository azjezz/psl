<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<string>
 */
function string(): TypeInterface
{
    /** @var Internal\StringType $instance */
    static $instance = new Internal\StringType();

    return $instance;
}
