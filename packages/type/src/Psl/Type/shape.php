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
function shape<Tk : int|string, Tv>(array $elements, bool $allowUnknownFields = false): TypeInterface
{
    return new Internal\ShapeType($elements, $allowUnknownFields);
}
