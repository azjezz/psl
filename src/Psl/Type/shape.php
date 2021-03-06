<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, TypeInterface<Tv>> $elements
 *
 * @return TypeInterface<array<Tk, Tv>>
 */
function shape(array $elements, bool $allow_unknown_fields = false): TypeInterface
{
    return new Internal\ShapeType($elements, $allow_unknown_fields);
}
