<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection;
use Psl\Collection\Map;
use Psl\Collection\MutableVector;
use Psl\Collection\Vector;

final class MutableVectorTest extends AbstractVectorTest
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<MutableVector>
     */
    protected string $vectorClass = MutableVector::class;

    public function testClear(): void
    {
        $vector  = $this->create(['foo', 'bar']);
        $cleared = $vector->clear();

        static::assertSame($cleared, $vector);
        static::assertCount(0, $vector);
    }

    public function testSet(): void
    {
        $vector = $this->create([
            'bar',
            'baz',
            'qux',
        ]);

        $modified = $vector
            ->set(0, 'foo')
            ->set(1, 'bar')
            ->set(2, 'baz');

        static::assertSame($modified, $vector);

        static::assertSame('foo', $vector->at(0));
        static::assertSame('bar', $vector->at(1));
        static::assertSame('baz', $vector->at(2));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (3) was out-of-bounds.');

        $vector->set(3, 'qux');
    }

    public function testSetAll(): void
    {
        $vector = $this->create([
            'bar',
            'baz',
            'qux',
        ]);

        $modified = $vector->setAll(new Map([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
        ]));

        static::assertSame($modified, $vector);

        static::assertSame('foo', $vector->at(0));
        static::assertSame('bar', $vector->at(1));
        static::assertSame('baz', $vector->at(2));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (3) was out-of-bounds.');

        $vector->setAll([3 => 'qux']);
    }

    public function testAdd(): void
    {
        $vector = $this->create([
            'foo', 'bar',
            'baz', 'qux',
        ]);

        $modified = $vector
            ->add('hello')
            ->add('world');

        static::assertSame($modified, $vector);

        static::assertSame('foo', $vector->at(0));
        static::assertSame('bar', $vector->at(1));
        static::assertSame('baz', $vector->at(2));
        static::assertSame('qux', $vector->at(3));
        static::assertSame('hello', $vector->at(4));
        static::assertSame('world', $vector->at(5));
    }

    public function testAddAll(): void
    {
        $vector = $this->create([
            'foo', 'bar',
            'baz', 'qux',
        ]);

        $modified = $vector
            ->addAll(new Vector([
                'hello',
                'world',
            ]));

        static::assertSame($modified, $vector);

        static::assertSame('foo', $vector->at(0));
        static::assertSame('bar', $vector->at(1));
        static::assertSame('baz', $vector->at(2));
        static::assertSame('qux', $vector->at(3));
        static::assertSame('hello', $vector->at(4));
        static::assertSame('world', $vector->at(5));
    }

    public function testRemove(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);

        $modified = $vector
            ->remove(0)
            ->remove(0);

        static::assertSame($modified, $vector);
        static::assertCount(1, $vector);
        static::assertSame('baz', $vector->get(0));
    }

    /**
     * @template     T
     *
     * @param iterable<T> $items
     *
     * @return MutableVector<T>
     */
    protected function create(iterable $items): MutableVector
    {
        return new MutableVector($items);
    }
}
