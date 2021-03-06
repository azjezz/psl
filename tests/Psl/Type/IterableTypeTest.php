<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

/**
 * @extends TypeTest<iterable<int, int>>
 */
final class IterableTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\iterable(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Vec\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Vec\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];

        yield [
            Dict\map(Vec\range(1, 10), static fn (int $value): string => (string) $value),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn (int $key): string => (string) $key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn (int $value): string => Str\format('00%d', $value)),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1.0];
        yield [1.23];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'iterable<int, int>'];
        yield [Type\iterable(Type\array_key(), Type\int()), 'iterable<array-key, int>'];
        yield [Type\iterable(Type\array_key(), Type\string()), 'iterable<array-key, string>'];
        yield [
            Type\iterable(Type\array_key(), Type\object(Iter\Iterator::class)),
            'iterable<array-key, Psl\Iter\Iterator>'
        ];
    }

    /**
     * @param iterable<int, int> $a
     * @param iterable<int, int> $b
     */
    protected function equals($a, $b): bool
    {
        $a = Dict\from_iterable($a);
        $b = Dict\from_iterable($b);

        return $a === $b;
    }
}
