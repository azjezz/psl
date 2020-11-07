<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection;
use Psl\Iter;
use Psl\Str;
use Psl\Type;

/**
 * @extends TypeTest<Collection\MapInterface<array-key, mixed>>
 */
final class MapTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\map(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Iter\range(1, 10),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Iter\range(1, 10),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Iter\map(Iter\range(1, 10), static fn(int $value): string => (string)$value),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Iter\map_keys(Iter\range(1, 10), static fn(int $key): string => (string)$key),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Iter\map(Iter\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
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
        yield [$this->getType(), 'Psl\Collection\MapInterface<int, int>'];
        yield [Type\map(Type\array_key(), Type\int()), 'Psl\Collection\MapInterface<array-key, int>'];
        yield [Type\map(Type\array_key(), Type\string()), 'Psl\Collection\MapInterface<array-key, string>'];
        yield [
            Type\map(Type\array_key(), Type\object(Iter\Iterator::class)),
            'Psl\Collection\MapInterface<array-key, Psl\Iter\Iterator>'
        ];
    }

    /**
     * @psalm-param Collection\MapInterface<array-key, mixed>|mixed $a
     * @psalm-param Collection\MapInterface<array-key, mixed>|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\is_object($a) && Type\is_instanceof($a, Collection\MapInterface::class)) {
            $a = $a->toArray();
        }

        if (Type\is_object($b) && Type\is_instanceof($b, Collection\MapInterface::class)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }
}
