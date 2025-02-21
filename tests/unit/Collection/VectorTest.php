<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Vector;

final class VectorTest extends AbstractVectorTest
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    public function testFromItems(): void
    {
        $vector = Vector::fromItems([1, 2, 3]);
        static::assertSame([1, 2, 3], $vector->toArray());
    }

    /**
     * @template T
     *
     * @param array<T> $items
     *
     * @return Vector<T>
     */
    #[\Override]
    protected function create(array $items): Vector
    {
        return new Vector($items);
    }
}
