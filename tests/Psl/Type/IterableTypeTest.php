<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Gen;
use Psl\Iter;
use Psl\Str;
use Psl\Type;

/**
 * @extends TypeTest<iterable<int, int>>
 */
class IterableTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\iterable(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Iter\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Gen\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];

        yield [
            Iter\map(Iter\range(1, 10), fn (int $value): string => (string) $value),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Iter\map_keys(Iter\range(1, 10), fn (int $key): string => (string) $key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Iter\map(Iter\range(1, 10), fn (int $value): string => Str\format('00%d', $value)),
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
        yield [Type\iterable(Type\array_key(), Type\object(Iter\Iterator::class)), 'iterable<array-key, Psl\Iter\Iterator>'];
    }

    /**
     * @param iterable<int, int> $a
     * @param iterable<int, int> $b
     * @return bool
     */
    protected function equals($a, $b): bool
    {
        $a = Iter\to_array_with_keys($a);
        $b = Iter\to_array_with_keys($b);

        return $a === $b;
    }
}
