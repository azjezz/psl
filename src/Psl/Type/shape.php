<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, TypeInterface<Tv>> $elements
 *
 * @psalm-return TypeInterface<array<Tk, Tv>>
 */
function shape(array $elements): TypeInterface
{
    return new Internal\ShapeType($elements);
}
