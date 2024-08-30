<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Exception;
use Psl\Collection\MutableSet;

final class MutableSetTest extends AbstractSetTest
{
    /**
     * The Set class used for values, keys .. etc.
     *
     * @var class-string<MutableSet>
     */
    protected string $setClass = MutableSet::class;


    public function testClear(): void
    {
        $set  = $this->createFromList(['foo', 'bar']);
        $cleared = $set->clear();

        static::assertSame($cleared, $set);
        static::assertCount(0, $set);
    }

    public function testAdd(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $modified = $set
            ->add('foo')
            ->add('bar')
            ->add('baz')
            ->add('qux')
            ;

        static::assertSame($modified, $set);

        static::assertSame('foo', $set->at('foo'));
        static::assertSame('bar', $set->at('bar'));
        static::assertSame('baz', $set->at('baz'));
        static::assertSame('qux', $set->at('qux'));
        static::assertCount(4, $set);

        $set->add('quux');

        static::assertSame('quux', $set->at('quux'));
        static::assertCount(5, $set);
    }

    public function testAddAll(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $modified = $set->addAll(['foo', 'bar', 'baz', 'qux']);

        static::assertSame($modified, $set);

        static::assertSame('foo', $set->at('foo'));
        static::assertSame('bar', $set->at('bar'));
        static::assertSame('baz', $set->at('baz'));
        static::assertSame('qux', $set->at('qux'));
        static::assertCount(4, $set);

        $set->addAll(['quux', 'corge']);

        static::assertSame('quux', $set->at('quux'));
        static::assertSame('corge', $set->at('corge'));
        static::assertCount(6, $set);
    }

    public function testRemove(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $modified = $set
            ->remove('foo')
            ->remove('bar');

        static::assertSame($modified, $set);
        static::assertCount(1, $set);
        static::assertSame('baz', $set->values()->at(0));
    }

    public function testArrayAccess(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        static::assertTrue(isset($set['foo']));
        static::assertSame('foo', $set['foo']);

        unset($set['foo']);
        static::assertFalse(isset($set['foo']));

        $set['foo'] = 'foo';
        static::assertTrue(isset($set['foo']));

        $set[] = 'qux';
        static::assertTrue(isset($set['qux']));
        static::assertCount(4, $set);
    }

    public function testOffsetSetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set write offset type, expected null or the same as the value.');

        $set[false] = 'qux';
    }

    public function testOffsetSetThrowsForInvalidOffsetValue(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set write offset type, expected null or the same as the value.');

        $set[0] = 'qux';
    }

    public function testOffsetIssetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set read offset type, expected a string or an integer.');

        isset($set[false]);
    }

    public function testOffsetUnsetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set read offset type, expected a string or an integer.');

        unset($set[false]);
    }

    public function testOffsetGetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set read offset type, expected a string or an integer.');

        $set[false];
    }

    public function testFromItems(): void
    {
        $set = MutableSet::fromItems(['a', 'b', 'b', 'c']);
        static::assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $set->toArray());
    }

    /**
     * @template T of array-key
     *
     * @param array<T, mixed> $items
     *
     * @return MutableSet<T>
     */
    protected function createFromList(array $items): MutableSet
    {
        return MutableSet::fromArray($items);
    }
}
