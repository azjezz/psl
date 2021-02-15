<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

/**
 * @extends TypeTest<Collection\MutableVectorInterface<mixed>>
 */
final class MutableVectorTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\mutable_vector(Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => (string)$value),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string)$key),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
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
        yield [$this->getType(), 'Psl\Collection\MutableVectorInterface<int>'];
        yield [Type\mutable_vector(Type\string()), 'Psl\Collection\MutableVectorInterface<string>'];
        yield [
            Type\mutable_vector(Type\object(Iter\Iterator::class)),
            'Psl\Collection\MutableVectorInterface<Psl\Iter\Iterator>'
        ];
    }

    /**
     * @psalm-param Collection\MutableVectorInterface<mixed>|mixed $a
     * @psalm-param Collection\MutableVectorInterface<mixed>|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\is_object($a) && Type\is_instanceof($a, Collection\MutableVectorInterface::class)) {
            $a = $a->toArray();
        }

        if (Type\is_object($b) && Type\is_instanceof($b, Collection\MutableVectorInterface::class)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }
}
