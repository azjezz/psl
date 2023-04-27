<?php

declare(strict_types=1);

namespace Psl\Type;

use Closure;

/**
 * @template I
 * @template O
 *
 * @param TypeInterface<I> $from
 * @param TypeInterface<O> $into
 * @param (Closure(I): O) $converter
 *
 * @ara-return TypeInterface<O>
 *
 * @return TypeInterface<O>
 */
function converted(TypeInterface $from, TypeInterface $into, Closure $converter): TypeInterface
{
    return new Internal\ConvertedType($from, $into, $converter);
}
