<?php

declare(strict_types=1);

namespace Psl\Type;

use Closure;

/**
 * @pure
 *
 * @param TypeInterface<I> $from
 * @param TypeInterface<O> $into
 * @param (Closure(I): O) $converter
 *
 * @return TypeInterface<O>
 *
 * @api
 */
function converted<I = mixed, O = mixed>(TypeInterface<I> $from, TypeInterface<O> $into, Closure $converter): TypeInterface<O>
{
    return new Internal\ConvertedType($from, $into, $converter);
}
