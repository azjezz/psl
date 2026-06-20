<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;
use Generator;
use Psl\Comparison\Order;
use Psl\EitherOrBoth;

/**
 * Merge two sorted iterables using a comparator, yielding a stream of
 * {@see EitherOrBoth\EitherOrBoth} values that distinguish elements only in the
 * left input, only in the right input, or present in both.
 *
 * The comparator returns a {@see Order}:
 * - {@see Order::Less}    -- the left element sorts before the right (emits {@see EitherOrBoth\Left})
 * - {@see Order::Equal}   -- the left and right elements match        (emits {@see EitherOrBoth\Both})
 * - {@see Order::Greater} -- the left element sorts after the right   (emits {@see EitherOrBoth\Right})
 *
 * Both inputs MUST be sorted according to `$compare`. The result is undefined otherwise.
 *
 * On an equal pair, both cursors advance. Consequently, if one side has N consecutive
 * elements that compare equal to the same element on the other side, only the first
 * forms a {@see EitherOrBoth\Both}; the remaining N-1 are emitted as {@see EitherOrBoth\Left}
 * or {@see EitherOrBoth\Right} once the other cursor has moved on. This matches Rust's
 * `itertools::merge_join_by`.
 *
 * The returned {@see Iterator} is rewindable -- consuming it twice replays the
 * cached events without re-walking the inputs. Memory while iterating the first
 * pass is O(1); subsequent passes additionally retain the cache of yielded
 * events.
 *
 * @param iterable<L> $left
 * @param iterable<R> $right
 * @param (Closure(L, R): Order) $compare
 *
 * @return Iterator<int, EitherOrBoth\EitherOrBoth<L, R>>
 *
 * @api
 */
function merge_join_by<L, R>(iterable $left, iterable $right, Closure $compare): Iterator<int, EitherOrBoth\EitherOrBoth<L, R>>
{
    return Iterator::from(
        /**
         * @return Generator<int, EitherOrBoth\EitherOrBoth<L, R>, mixed, void>
         */
        static function () use ($left, $right, $compare): Generator {
            $l = Iterator::create($left);
            $r = Iterator::create($right);

            $l->rewind();
            $r->rewind();

            while ($l->valid() && $r->valid()) {
                $lv = $l->current();
                $rv = $r->current();

                switch ($compare($lv, $rv)) {
                    case Order::Less:
                        yield new EitherOrBoth\Left($lv);
                        $l->next();
                        break;
                    case Order::Greater:
                        yield new EitherOrBoth\Right($rv);
                        $r->next();
                        break;
                    case Order::Equal:
                        yield new EitherOrBoth\Both($lv, $rv);
                        $l->next();
                        $r->next();
                        break;
                }
            }

            while ($l->valid()) {
                yield new EitherOrBoth\Left($l->current());
                $l->next();
            }

            while ($r->valid()) {
                yield new EitherOrBoth\Right($r->current());
                $r->next();
            }
        },
    );
}
