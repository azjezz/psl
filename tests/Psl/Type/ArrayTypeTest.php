<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Iter;
use Psl\Str;
use Psl\Type;

final class ArrayTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\arr(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Iter\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Iter\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [
            Iter\map(Iter\range(1, 10), static fn (int $value): string => (string) $value),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];
        yield [
            Iter\map_keys(Iter\range(1, 10), static fn (int $key): string => (string) $key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];
        yield [
            Iter\map(Iter\range(1, 10), static fn (int $value): string => Str\format('00%d', $value)),
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
        yield [$this->getType(), 'array<int, int>'];
        yield [Type\arr(Type\array_key(), Type\int()), 'array<array-key, int>'];
        yield [Type\arr(Type\array_key(), Type\string()), 'array<array-key, string>'];
        yield [Type\arr(Type\array_key(), Type\object(Iter\Iterator::class)), 'array<array-key, Psl\Iter\Iterator>'];
    }
}
