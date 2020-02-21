<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use Psl\Collection\Map;
use Psl\Collection\Vector;
use Psl\Collection\MutableVector;
use Psl\Exception\InvariantViolationException;

final class MutableVectorTest extends AbstractVectorTest
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @psalm-var class-string<MutableVector>
     */
    protected string $vectorClass = MutableVector::class;

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

    public function testClear(): void
    {
        $vector = $this->create(['foo', 'bar']);
        $cleared = $vector->clear();

        $this->assertSame($cleared, $vector);
        $this->assertCount(0, $vector);
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

        $this->assertSame($modified, $vector);

        $this->assertSame('foo', $vector->at(0));
        $this->assertSame('bar', $vector->at(1));
        $this->assertSame('baz', $vector->at(2));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (3) is out-of-bound.');

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

        $this->assertSame($modified, $vector);

        $this->assertSame('foo', $vector->at(0));
        $this->assertSame('bar', $vector->at(1));
        $this->assertSame('baz', $vector->at(2));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (3) is out-of-bound.');

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

        $this->assertSame($modified, $vector);

        $this->assertSame('foo', $vector->at(0));
        $this->assertSame('bar', $vector->at(1));
        $this->assertSame('baz', $vector->at(2));
        $this->assertSame('qux', $vector->at(3));
        $this->assertSame('hello', $vector->at(4));
        $this->assertSame('world', $vector->at(5));
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

        $this->assertSame($modified, $vector);

        $this->assertSame('foo', $vector->at(0));
        $this->assertSame('bar', $vector->at(1));
        $this->assertSame('baz', $vector->at(2));
        $this->assertSame('qux', $vector->at(3));
        $this->assertSame('hello', $vector->at(4));
        $this->assertSame('world', $vector->at(5));
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

        $this->assertSame($modified, $vector);
        $this->assertCount(1, $vector);
        $this->assertSame('baz', $vector->get(0));
    }
}

