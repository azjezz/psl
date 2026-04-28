<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Order;
use Psl\EitherOrBoth;
use Psl\Iter;
use Psl\Vec;

final class MergeJoinByTest extends TestCase
{
    /**
     * @param list<int> $left
     * @param list<int> $right
     * @param list<array{0: string, 1: int, 2?: int}> $expected
     */
    private static function assertStream(array $left, array $right, array $expected): void
    {
        $ordering = static fn(int $a, int $b): Order => Order::from($a <=> $b);

        $collected = [];
        foreach (Iter\merge_join_by($left, $right, $ordering) as $event) {
            $collected[] = match (true) {
                $event instanceof EitherOrBoth\Both => ['both', $event->getLeft(), $event->getRight()],
                $event instanceof EitherOrBoth\Left => ['left', $event->getLeft()],
                $event instanceof EitherOrBoth\Right => ['right', $event->getRight()],
            };
        }

        static::assertSame($expected, $collected);
    }

    public function testBothEmptyYieldsNothing(): void
    {
        self::assertStream([], [], []);
    }

    public function testLeftEmptyYieldsOnlyRights(): void
    {
        self::assertStream(
            [],
            [1, 2, 3],
            [
                ['right', 1],
                ['right', 2],
                ['right', 3],
            ],
        );
    }

    public function testRightEmptyYieldsOnlyLefts(): void
    {
        self::assertStream(
            [1, 2, 3],
            [],
            [
                ['left', 1],
                ['left', 2],
                ['left', 3],
            ],
        );
    }

    public function testIdenticalSortedListsYieldOnlyBoths(): void
    {
        self::assertStream(
            [1, 2, 3],
            [1, 2, 3],
            [
                ['both', 1, 1],
                ['both', 2, 2],
                ['both', 3, 3],
            ],
        );
    }

    public function testFullyDisjointInterleaves(): void
    {
        self::assertStream(
            [1, 3, 5],
            [2, 4, 6],
            [
                ['left', 1],
                ['right', 2],
                ['left', 3],
                ['right', 4],
                ['left', 5],
                ['right', 6],
            ],
        );
    }

    public function testPartialOverlap(): void
    {
        self::assertStream(
            [1, 2, 3, 4],
            [2, 4, 5],
            [
                ['left', 1],
                ['both', 2, 2],
                ['left', 3],
                ['both', 4, 4],
                ['right', 5],
            ],
        );
    }

    public function testLeftLongerThanRightDrainsLefts(): void
    {
        self::assertStream(
            [1, 2, 3, 4, 5],
            [2, 3],
            [
                ['left', 1],
                ['both', 2, 2],
                ['both', 3, 3],
                ['left', 4],
                ['left', 5],
            ],
        );
    }

    public function testRightLongerThanLeftDrainsRights(): void
    {
        self::assertStream(
            [2, 3],
            [1, 2, 3, 4, 5],
            [
                ['right', 1],
                ['both', 2, 2],
                ['both', 3, 3],
                ['right', 4],
                ['right', 5],
            ],
        );
    }

    public function testGeneratorInputsAreSupported(): void
    {
        $gen = static function (array $values): Generator {
            yield from $values;
        };

        $result = Vec\map(
            Iter\merge_join_by($gen([1, 2]), $gen([2, 3]), static fn(int $a, int $b): Order => Order::from($a <=> $b)),
            static fn(EitherOrBoth\EitherOrBoth $e): string => $e->proceed(
                left: static fn(int $v): string => "L{$v}",
                right: static fn(int $v): string => "R{$v}",
                both: static fn(int $l, int $r): string => "B{$l}{$r}",
            ),
        );

        static::assertSame(['L1', 'B22', 'R3'], $result);
    }

    public function testCustomComparatorReverseOrder(): void
    {
        $reverse = static fn(int $a, int $b): Order => Order::from($b <=> $a);

        $collected = [];
        foreach (Iter\merge_join_by([3, 2, 1], [4, 2, 0], $reverse) as $event) {
            $collected[] = $event->proceed(
                left: static fn(int $v): string => "L{$v}",
                right: static fn(int $v): string => "R{$v}",
                both: static fn(int $l, int $r): string => "B{$l}{$r}",
            );
        }

        static::assertSame(['R4', 'L3', 'B22', 'L1', 'R0'], $collected);
    }

    public function testDuplicateKeysOnLeftEmitExtrasAsLefts(): void
    {
        // On Equal, both cursors advance. So with `[1, 2, 2, 3]` vs `[2, 3]`, the first
        // left-side 2 pairs with the right-side 2 as Both, and the second left-side 2
        // is then compared against the next right element (3), emitting Left(2).
        self::assertStream(
            [1, 2, 2, 3],
            [2, 3],
            [
                ['left', 1],
                ['both', 2, 2],
                ['left', 2],
                ['both', 3, 3],
            ],
        );
    }

    public function testDuplicateKeysOnRightEmitExtrasAsRights(): void
    {
        // Symmetric to the previous test: duplicate on the right becomes a Right extra.
        self::assertStream(
            [2, 3],
            [1, 2, 2, 3],
            [
                ['right', 1],
                ['both', 2, 2],
                ['right', 2],
                ['both', 3, 3],
            ],
        );
    }

    public function testReturnedIteratorIsRewindable(): void
    {
        $stream = Iter\merge_join_by([1, 2, 3], [2, 3, 4], static fn(int $a, int $b): Order => Order::from($a <=> $b));

        $first = Vec\map($stream, static fn(EitherOrBoth\EitherOrBoth $e): string => $e->proceed(
            left: static fn(int $v): string => "L{$v}",
            right: static fn(int $v): string => "R{$v}",
            both: static fn(int $l, int $r): string => "B{$l}",
        ));

        $second = Vec\map($stream, static fn(EitherOrBoth\EitherOrBoth $e): string => $e->proceed(
            left: static fn(int $v): string => "L{$v}",
            right: static fn(int $v): string => "R{$v}",
            both: static fn(int $l, int $r): string => "B{$l}",
        ));

        static::assertSame(['L1', 'B2', 'B3', 'R4'], $first);
        static::assertSame($first, $second);
    }

    public function testShortCircuitDoesNotExhaustRightInput(): void
    {
        $rightPulled = 0;
        $right = (static function () use (&$rightPulled): Generator {
            foreach ([2, 4, 6, 8, 10] as $v) {
                $rightPulled++;
                yield $v;
            }
        })();

        $first = Iter\first(Iter\merge_join_by([1], $right, static fn(int $a, int $b): Order => Order::from($a
        <=> $b)));

        static::assertInstanceOf(EitherOrBoth\Left::class, $first);
        static::assertSame(1, $first->getLeft());
        // The right generator only had to advance once (to 2) to determine ordering.
        static::assertSame(1, $rightPulled);
    }
}
