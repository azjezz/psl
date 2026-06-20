<?php

declare(strict_types=1);

namespace Psl\Type;

use Closure;

/**
 * @pure
 *
 * @param (Closure(I): O) $converter
 *
 * @api
 */
function converted<I, O>(TypeInterface<I> $from, TypeInterface<O> $into, Closure $converter): TypeInterface<O>
{
    return new Internal\ConvertedType::<I, O>($from, $into, $converter);
}
