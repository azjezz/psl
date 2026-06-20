<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;
use Generator;
use Psl\EitherOrBoth;

use function array_key_exists;

/**
 * Three-way diff of two iterables by a key-extraction function.
 *
 * Unlike {@see merge_join_by()}, inputs do NOT need to be sorted. The right
 * iterable is materialized once into a key-indexed lookup table; the left
 * iterable streams. For each element of the left input:
 * - if its key is present on the right, emits {@see EitherOrBoth\Both}
 * - otherwise, emits {@see EitherOrBoth\Left}
 *
 * Once the left input is exhausted, any remaining right-only entries are
 * emitted as {@see EitherOrBoth\Right}.
 *
 * Memory is O(|right|) by necessity: right-only emissions cannot be produced
 * before the left input is fully consumed, which requires random-access lookup.
 *
 * If multiple elements on either side share a key, last-write-wins semantics
 * apply when building the right-side lookup; on the left, each element is
 * still yielded individually but will all pair with the same right element
 * (and consume it on first match, so subsequent left duplicates emit
 * {@see EitherOrBoth\Left}).
 *
 * The returned {@see Iterator} is rewindable -- consuming it twice replays the
 * cached events without re-walking the inputs.
 *
 * @param iterable<T> $left
 * @param iterable<T> $right
 * @param (Closure(T): I) $key_by
 *
 * @api
 */
function merge_join_by_key<T, I: string|int>(iterable $left, iterable $right, Closure $key_by): Iterator<int, EitherOrBoth\EitherOrBoth<T, T>>
{
    return Iterator::<int, EitherOrBoth\EitherOrBoth<T, T>>::from(
        /**
         * @return Generator<int, EitherOrBoth\EitherOrBoth<T, T>, mixed, void>
         */
        static function () use ($left, $right, $key_by): Generator {
            $lookup = [];
            foreach ($right as $record) {
                $lookup[$key_by($record)] = $record;
            }

            foreach ($left as $record) {
                $id = $key_by($record);
                if (array_key_exists($id, $lookup)) {
                    yield new EitherOrBoth\Both::<T, T>($record, $lookup[$id]);
                    unset($lookup[$id]);
                    continue;
                }

                yield new EitherOrBoth\Left::<T>($record);
            }

            foreach ($lookup as $leftover) {
                yield new EitherOrBoth\Right::<T>($leftover);
            }
        },
    );
}
