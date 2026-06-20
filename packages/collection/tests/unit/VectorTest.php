<?php

declare(strict_types=1);

namespace Psl\Collection\Tests\Unit;

use Override;
use Psl\Collection\Vector;

final class VectorTest extends AbstractVectorTestCase
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    public function testFromItems(): void
    {
        $vector = Vector::<int>::fromItems([1, 2, 3]);
        static::assertSame([1, 2, 3], $vector->toArray());
    }

    /**
     * @param array<T> $items
     */
    #[Override]
    protected function create<T>(array $items): Vector<T>
    {
        return new Vector::<mixed>($items);
    }
}
