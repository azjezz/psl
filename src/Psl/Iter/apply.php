<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Applies a function to all values of an iterable.
 *
 * @template  T
 *
 * @param iterable<T> $iterable Iterable to apply on
 * @param (Closure(T): void) $function Apply function
 */
function apply(iterable $iterable, Closure $function): void
{
    foreach ($iterable as $value) {
        $function($value);
    }
}
