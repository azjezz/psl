<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Type<Tv>> $elements
 *
 * @psalm-return Type<array<Tk, Tv>>
 */
function shape(array $elements): Type
{
    return new Internal\ShapeType($elements);
}
