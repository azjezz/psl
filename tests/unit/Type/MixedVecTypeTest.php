<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

final class MixedVecTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\mixed_vec();
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [],
            [],
        ];
        yield [
            ['foo' => 'bar'],
            ['bar'],
        ];

        yield [
            [1, 2, 3],
            [1, 2, 3],
        ];

        yield [
            [16 => ['arr'], 'foo', 'bar', 45 => 1, 44 => 2],
            [['arr'], 'foo', 'bar', 1, 2],
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
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
        ];

        yield [
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string) $key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            ['001', '002', '003', '004', '005', '006', '007', '008', '009', '0010'],
        ];

        yield [
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
            [1, 2, 3, 4, 5],
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
        yield [$this->getType(), 'vec<mixed>'];
    }
}
