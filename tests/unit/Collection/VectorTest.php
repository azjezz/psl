<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Exception;
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

    public function testArrayAccess(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        static::assertTrue(isset($vector[0]));
        static::assertSame('foo', $vector[0]);

        $this->expectException(Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (3) was out-of-bounds.');

        $vector[3];
    }

    public function testOffsetSetThrows(): void
    {
        $map = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Vector as array');

        $map[0] = 'qux';
    }

    public function testOffsetUnsetThrows(): void
    {
        $map = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Vector as array');

        unset($map[0]);
    }

    public function testOffsetIssetThrowsForInvalidOffsetType(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid vector read offset type, expected a positive integer.');

        isset($vector[false]);
    }

    public function testOffsetGetThrowsForInvalidOffsetType(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid vector read offset type, expected a positive integer.');

        $vector[false];
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
