<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Applies a function to all values of an iterable.
 *
 * @param iterable<T> $iterable Iterable to apply on
 * @param (Closure(T): mixed) $function Apply function
 *
 * @api
 */
function apply<T = mixed>(iterable $iterable, Closure $function): void
{
    foreach ($iterable as $value) {
        $function($value);
    }
}
