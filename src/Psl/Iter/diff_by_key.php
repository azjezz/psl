<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, mixed> $second
 * @param iterable<Tk, mixed> ...$rest
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\diff_by_key` instead.
 * @see Dict\diff_by_key()
 */
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): Iterator
{
    return Iterator::from(static function () use ($first, $second, $rest): Generator {
        if (is_empty($first)) {
            return;
        }

        if (is_empty($second) && all($rest, static fn (iterable $iter): bool => is_empty($iter))) {
            yield from $first;
        }

        // We don't use arrays here to ensure we allow the usage of non-arraykey indexes.
        /** @var Generator<Tk, mixed, mixed, void> $second */
        $second = ((static fn (iterable $iterable): Generator => yield from $iterable)($second));
        /** @var Generator<iterable<Tk, mixed>, mixed, mixed, void> $generator */
        $generator  = ((static function (Generator $second, iterable ...$rest): Generator {
            yield from $second;
            foreach ($rest as $iterable) {
                yield from $iterable;
            }
        })($second, ...$rest));
        $rewindable = rewindable($generator);

        foreach ($first as $k => $v) {
            if (!contains_key($rewindable, $k)) {
                yield $k => $v;
            }
        }
    });
}
