<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\EitherOrBoth;
use Psl\Iter;
use Psl\Vec;

final class MergeJoinByKeyTest extends TestCase
{
    /**
     * @param list<array{id: int, v: string}> $left
     * @param list<array{id: int, v: string}> $right
     * @param list<array{0: string, 1: string, 2?: string}> $expected
     */
    private static function assertStream(array $left, array $right, array $expected): void
    {
        $collected = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, array>(
            Iter\merge_join_by_key::<array, int>($left, $right, static fn(array $r): int => $r['id']),
            static fn(EitherOrBoth\EitherOrBoth<array, array> $e): array => $e->proceed::<array>(
                left: static fn(array $r): array => ['left', $r['v']],
                right: static fn(array $r): array => ['right', $r['v']],
                both: static fn(array $l, array $r): array => ['both', $l['v'], $r['v']],
            ),
        );

        static::assertSame($expected, $collected);
    }

    public function testBothEmpty(): void
    {
        self::assertStream([], [], []);
    }

    public function testLeftEmptyDrainsRight(): void
    {
        self::assertStream(
            [],
            [['id' => 1, 'v' => 'a'], ['id' => 2, 'v' => 'b']],
            [
                ['right', 'a'],
                ['right', 'b'],
            ],
        );
    }

    public function testRightEmptyYieldsOnlyLefts(): void
    {
        self::assertStream(
            [['id' => 1, 'v' => 'a'], ['id' => 2, 'v' => 'b']],
            [],
            [
                ['left', 'a'],
                ['left', 'b'],
            ],
        );
    }

    public function testOverlappingKeysEmitBoth(): void
    {
        self::assertStream(
            [['id' => 1, 'v' => 'new1'], ['id' => 2, 'v' => 'new2']],
            [['id' => 1, 'v' => 'old1'], ['id' => 2, 'v' => 'old2']],
            [
                ['both', 'new1', 'old1'],
                ['both', 'new2', 'old2'],
            ],
        );
    }

    public function testPartialOverlap(): void
    {
        self::assertStream(
            [['id' => 1, 'v' => 'A'], ['id' => 2, 'v' => 'B'], ['id' => 3, 'v' => 'C']],
            [['id' => 2, 'v' => 'b'], ['id' => 4, 'v' => 'd']],
            [
                ['left', 'A'],
                ['both', 'B', 'b'],
                ['left', 'C'],
                ['right', 'd'],
            ],
        );
    }

    public function testUnsortedInputsWorkAndLeftOrderIsPreserved(): void
    {
        self::assertStream(
            [['id' => 3, 'v' => 'C'], ['id' => 1, 'v' => 'A'], ['id' => 2, 'v' => 'B']],
            [['id' => 2, 'v' => 'b'], ['id' => 4, 'v' => 'd']],
            [
                ['left', 'C'],
                ['left', 'A'],
                ['both', 'B', 'b'],
                ['right', 'd'],
            ],
        );
    }

    public function testStringKeys(): void
    {
        $left = [['id' => 'a', 'v' => 'A'], ['id' => 'b', 'v' => 'B']];
        $right = [['id' => 'b', 'v' => 'b'], ['id' => 'c', 'v' => 'c']];

        $collected = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>(
            Iter\merge_join_by_key::<array, string>($left, $right, static fn(array $r): string => $r['id']),
            static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
                left: static fn(array $r): string => "L:{$r['v']}",
                right: static fn(array $r): string => "R:{$r['v']}",
                both: static fn(array $l, array $r): string => "B:{$l['v']}/{$r['v']}",
            ),
        );

        static::assertSame(['L:A', 'B:B/b', 'R:c'], $collected);
    }

    public function testGeneratorInputs(): void
    {
        $gen = static function (array $values): Generator {
            yield from $values;
        };

        $collected = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>(
            Iter\merge_join_by_key::<array, int>(
                $gen([['id' => 1, 'v' => 'L1'], ['id' => 2, 'v' => 'L2']]),
                $gen([['id' => 2, 'v' => 'R2'], ['id' => 3, 'v' => 'R3']]),
                static fn(array $r): int => $r['id'],
            ),
            static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
                left: static fn(array $r): string => $r['v'] . '-left',
                right: static fn(array $r): string => $r['v'] . '-right',
                both: static fn(array $l, array $r): string => $l['v'] . '+' . $r['v'],
            ),
        );

        static::assertSame(['L1-left', 'L2+R2', 'R3-right'], $collected);
    }

    public function testRightDuplicateKeyLastWriteWins(): void
    {
        // Two right-side records with the same key: last one wins when building the lookup.
        $left = [['id' => 1, 'v' => 'L']];
        $right = [['id' => 1, 'v' => 'first'], ['id' => 1, 'v' => 'last']];

        $collected = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>(
            Iter\merge_join_by_key::<array, int>($left, $right, static fn(array $r): int => $r['id']),
            static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
                left: static fn(array $r): string => 'L:' . $r['v'],
                right: static fn(array $r): string => 'R:' . $r['v'],
                both: static fn(array $l, array $r): string => 'B:' . $l['v'] . '/' . $r['v'],
            ),
        );

        static::assertSame(['B:L/last'], $collected);
    }

    public function testReturnedIteratorIsRewindable(): void
    {
        $left = [['id' => 1, 'v' => 'A'], ['id' => 2, 'v' => 'B']];
        $right = [['id' => 2, 'v' => 'b'], ['id' => 3, 'v' => 'c']];

        $stream = Iter\merge_join_by_key::<array, int>($left, $right, static fn(array $r): int => $r['id']);

        $first = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>($stream, static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
            left: static fn(array $r): string => "L:{$r['v']}",
            right: static fn(array $r): string => "R:{$r['v']}",
            both: static fn(array $l, array $r): string => "B:{$l['v']}/{$r['v']}",
        ));

        $second = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>($stream, static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
            left: static fn(array $r): string => "L:{$r['v']}",
            right: static fn(array $r): string => "R:{$r['v']}",
            both: static fn(array $l, array $r): string => "B:{$l['v']}/{$r['v']}",
        ));

        static::assertSame(['L:A', 'B:B/b', 'R:c'], $first);
        static::assertSame($first, $second);
    }

    public function testLeftDuplicateKeyFirstConsumesRightMatch(): void
    {
        // Two left-side records with the same key: first consumes the right match,
        // second sees no right-side record and emits Left.
        $left = [['id' => 1, 'v' => 'first'], ['id' => 1, 'v' => 'second']];
        $right = [['id' => 1, 'v' => 'R']];

        $collected = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>(
            Iter\merge_join_by_key::<array, int>($left, $right, static fn(array $r): int => $r['id']),
            static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
                left: static fn(array $r): string => 'L:' . $r['v'],
                right: static fn(array $r): string => 'R:' . $r['v'],
                both: static fn(array $l, array $r): string => 'B:' . $l['v'] . '/' . $r['v'],
            ),
        );

        static::assertSame(['B:first/R', 'L:second'], $collected);
    }
}
