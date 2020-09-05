<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use Psl\Collection\Map;
use Psl\Collection\MutableVector;
use Psl\Collection\Vector;
use Psl\Exception\InvariantViolationException;

final class MutableVectorTest extends AbstractVectorTest
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @psalm-var class-string<MutableVector>
     */
    protected string $vectorClass = MutableVector::class;

    public function testClear(): void
    {
        $vector  = $this->create(['foo', 'bar']);
        $cleared = $vector->clear();

        self::assertSame($cleared, $vector);
        self::assertCount(0, $vector);
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

        self::assertSame($modified, $vector);

        self::assertSame('foo', $vector->at(0));
        self::assertSame('bar', $vector->at(1));
        self::assertSame('baz', $vector->at(2));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (3) is out-of-bounds.');

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

        self::assertSame($modified, $vector);

        self::assertSame('foo', $vector->at(0));
        self::assertSame('bar', $vector->at(1));
        self::assertSame('baz', $vector->at(2));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (3) is out-of-bounds.');

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

        self::assertSame($modified, $vector);

        self::assertSame('foo', $vector->at(0));
        self::assertSame('bar', $vector->at(1));
        self::assertSame('baz', $vector->at(2));
        self::assertSame('qux', $vector->at(3));
        self::assertSame('hello', $vector->at(4));
        self::assertSame('world', $vector->at(5));
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

        self::assertSame($modified, $vector);

        self::assertSame('foo', $vector->at(0));
        self::assertSame('bar', $vector->at(1));
        self::assertSame('baz', $vector->at(2));
        self::assertSame('qux', $vector->at(3));
        self::assertSame('hello', $vector->at(4));
        self::assertSame('world', $vector->at(5));
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

        self::assertSame($modified, $vector);
        self::assertCount(1, $vector);
        self::assertSame('baz', $vector->get(0));
    }

    /**
     * @template     T
     *
     * @psalm-param  iterable<T> $items
     *
     * @psalm-return MutableVector<T>
     */
    protected function create(iterable $items): MutableVector
    {
        return new MutableVector($items);
    }
}
