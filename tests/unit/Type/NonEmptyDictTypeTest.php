<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

/**
 * @extends TypeTest<non-empty-array<array-key, mixed>>
 */
final class NonEmptyDictTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\non_empty_dict(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            new Collection\Vector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Vector(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Map(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string)$key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map(
                Vec\range(1, 10),
                static fn(int $value): string => Str\format('00%d', $value)
            ),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map_keys(
                Dict\map(
                    Vec\range(1, 10),
                    static fn(int $value): string => Str\format('00%d', $value)
                ),
                static fn(int $key): string => Str\format('00%d', $key)
            ),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [[]];
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
        yield [$this->getType(), 'non-empty-array<int, int>'];
        yield [Type\non_empty_dict(Type\array_key(), Type\int()), 'non-empty-array<array-key, int>'];
        yield [Type\non_empty_dict(Type\array_key(), Type\string()), 'non-empty-array<array-key, string>'];
        yield [
            Type\non_empty_dict(Type\array_key(), Type\object(Iter\Iterator::class)),
            'non-empty-array<array-key, Psl\Iter\Iterator>'
        ];
    }
}
