<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use Psl\Collection\Vector;

final class VectorTest extends AbstractVectorTest
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    /**
     * @template     T
     *
     * @param iterable<T> $items
     *
     * @return Vector<T>
     */
    protected function create(iterable $items): Vector
    {
        return new Vector($items);
    }
}
