<?php

declare(strict_types=1);

namespace Psl\Collection\Tests\Unit;

use Override;
use Psl\Collection\Map;
use Psl\Collection\Vector;

final class MapTest extends AbstractMapTestCase
{
    /**
     * @var class-string<Map>
     */
    protected string $mapClass = Map::class;

    /**
     * @var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    public function testFromItems(): void
    {
        $map = Map::<string, string>::fromItems([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        static::assertSame('bar', $map->at('foo'));
        static::assertSame('baz', $map->at('bar'));
        static::assertSame('qux', $map->at('baz'));
    }

    /**
     * @param iterable<Tk, Tv> $items
     */
    #[Override]
    protected function create<Tk: string|int, Tv>(iterable $items): Map<Tk, Tv>
    {
        return Map::<string|int, mixed>::fromArray($items);
    }
}
