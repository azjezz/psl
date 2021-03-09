<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Applies a function to all values of an iterable.
 *
 * The function is passed the current iterator value. The reason why apply
 * exists additionally to map is that map is lazy, whereas apply is not (i.e.
 * you do not need to consume a resulting iterator for the function calls to
 * actually happen.)
 *
 * Examples:
 *
 *      Iter\apply(
 *          $iterators,
 *          fn($iterator) => $iterator->rewind(),
 *      );
 *
 * @template  T
 *
 * @param iterable<T> $iterable Iterable to apply on
 * @param (callable(T): void) $function Apply function
 */
function apply(iterable $iterable, callable $function): void
{
    foreach ($iterable as $value) {
        $function($value);
    }
}
