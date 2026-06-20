<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param array<Tk, TypeInterface<Tv>> $elements
 *
 * @return TypeInterface<array<Tk, Tv>>
 *
 * @api
 */
function shape<Tk: string|int, Tv>(array $elements, bool $allowUnknownFields = false): TypeInterface<array>
{
    return new Internal\ShapeType::<Tk, Tv>($elements, $allowUnknownFields);
}
