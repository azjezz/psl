<?php

declare(strict_types=1);

namespace Psl\Collection\Tests\Unit;

use Override;
use Psl\Collection\Set;

final class SetTest extends AbstractSetTestCase
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
    #[Override]
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

    #[Override]
    public function testJsonSerialize(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz', 'qux']);

        static::assertSame(['foo', 'bar', 'baz', 'qux'], $set->jsonSerialize());
    }
}
