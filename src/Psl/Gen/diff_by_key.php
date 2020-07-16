<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

use Psl\Arr;
use Psl\Iter;

/**
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>            $first
 * @psalm-param iterable<Tk, mixed>         $second
 * @psalm-param list<iterable<Tk, mixed>>   $rest
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): Generator
{
    if (Iter\is_empty($first)) {
        return;
    }

    if (
        Iter\is_empty($second) &&
        Iter\all(
            $rest,
            /**
             * @psalm-param iterable<Tk, mixed> $iter
             */
            fn (iterable $iter): bool => Iter\is_empty($iter)
        )
    ) {
        yield from $first;
    }

    // We don't use arrays here to ensure we allow the usage of non-arraykey indexes.
    /** @psalm-var Generator<Tk, mixed, mixed, void> $second */
    $second = ((fn (iterable $iterable): Generator => yield from $iterable)($second));
    /** @psalm-var Generator<iterable<Tk, mixed>, mixed, mixed, void> $generator */
    $generator = ((static function (Generator $second, iterable ...$rest): Generator {
        yield from $second;
        foreach ($rest as $iterable) {
            yield from $iterable;
        }
    })($second, ...$rest));
    $rewindable = rewindable($generator);

    foreach ($first as $k => $v) {
        if (!Iter\contains_key($rewindable, $k)) {
            yield $k => $v;
        }
    }
}
