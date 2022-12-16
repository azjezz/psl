<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @template T of int|float
 *
 * @return FullRange<T>
 *
 * @psalm-mutation-free
 */
function full(): FullRange
{
    /** @var FullRange<T> */
    return new FullRange();
}
