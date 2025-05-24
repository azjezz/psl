<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Exception;
use Psl\Collection\Set;

final class SetTest extends AbstractSetTest
{
    /**
     * The Set class used for values, keys .. etc.
     *
     * @var class-string<Set>
     */
    protected string $setClass = Set::class;

    public function testFromItems(): void
    {
        $set = Set::fromItems(['foo', 'bar', 'baz']);

        static::assertTrue($set->contains('foo'));
        static::assertTrue($set->contains('bar'));
        static::assertTrue($set->contains('baz'));
    }

    /**
     * @template T of array-key
     *
     * @param array<T, mixed> $items
     *
     * @return Set<T>
     */
    #[\Override]
    protected function createFromList(array $items): Set
    {
        return Set::fromArray($items);
    }

    public function testFromArrayKeysConstructor(): void
    {
        $set = Set::fromArrayKeys(['foo' => 1, 'bar' => 1, 'baz' => 1]);

        static::assertCount(3, $set);
        static::assertTrue($set->contains('foo'));
        static::assertTrue($set->contains('bar'));
        static::assertTrue($set->contains('baz'));
    }

    #[\Override]
    public function testJsonSerialize(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz', 'qux']);

        static::assertSame(['foo', 'bar', 'baz', 'qux'], $set->jsonSerialize());
    }

    public function testArrayAccess(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        static::assertTrue(isset($set['foo']));
        static::assertSame('foo', $set['foo']);
    }

    public function testOffsetSetThrows(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Set as array');

        $set['qux'] = 'qux';
    }

    public function testOffsetIssetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set read offset type, expected a string or an integer.');

        isset($set[false]);
    }

    public function testOffsetGetThrowsForInvalidOffsetType(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid set read offset type, expected a string or an integer.');

        $set[false];
    }

    public function testOffsetUnsetThrows(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Set as array');

        unset($set['foo']);
    }
}
