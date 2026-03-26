<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Message\FieldMap;

final class FieldMapTest extends TestCase
{
    public function testDefaultIsEmpty(): void
    {
        $map = FieldMap::default();

        static::assertTrue($map->isEmpty());
        static::assertSame([], $map->toArray());
    }

    public function testConstructorCreatesEmptyFieldMap(): void
    {
        $map = new FieldMap();

        static::assertTrue($map->isEmpty());
    }

    public function testFromCreatesFieldMap(): void
    {
        $map = FieldMap::from([
            ['content-type', 'text/plain'],
            ['accept',       'application/json'],
        ]);

        static::assertFalse($map->isEmpty());
        static::assertSame('text/plain', $map->get('content-type'));
        static::assertSame('application/json', $map->get('accept'));
    }

    public function testFromEmptyArrayReturnsEmpty(): void
    {
        $map = FieldMap::from([]);

        static::assertTrue($map->isEmpty());
    }

    public function testGetReturnsCaseInsensitive(): void
    {
        $map = FieldMap::from([['Content-Type', 'text/html']]);

        static::assertSame('text/html', $map->get('content-type'));
        static::assertSame('text/html', $map->get('Content-Type'));
        static::assertSame('text/html', $map->get('CONTENT-TYPE'));
    }

    public function testGetReturnsFirstValue(): void
    {
        $map = FieldMap::from([
            ['set-cookie', 'a=1'],
            ['set-cookie', 'b=2'],
        ]);

        static::assertSame('a=1', $map->get('set-cookie'));
    }

    public function testGetReturnsNullWhenMissing(): void
    {
        $map = FieldMap::from([['content-type', 'text/plain']]);

        static::assertNull($map->get('accept'));
    }

    public function testGetAllReturnsAllValues(): void
    {
        $map = FieldMap::from([
            ['set-cookie',   'a=1'],
            ['content-type', 'text/plain'],
            ['set-cookie',   'b=2'],
        ]);

        static::assertSame(['a=1', 'b=2'], $map->getAll('set-cookie'));
    }

    public function testGetAllReturnsEmptyWhenMissing(): void
    {
        $map = FieldMap::from([['content-type', 'text/plain']]);

        static::assertSame([], $map->getAll('accept'));
    }

    public function testHasIsCaseInsensitive(): void
    {
        $map = FieldMap::from([['Content-Type', 'text/html']]);

        static::assertTrue($map->has('content-type'));
        static::assertTrue($map->has('Content-Type'));
        static::assertFalse($map->has('accept'));
    }

    public function testWithReplacesExisting(): void
    {
        $map = FieldMap::from([
            ['content-type', 'text/html'],
            ['accept',       'text/plain'],
        ]);

        $modified = $map->with('content-type', 'application/json');

        static::assertSame('text/html', $map->get('content-type'));
        static::assertSame('application/json', $modified->get('content-type'));
        static::assertSame('text/plain', $modified->get('accept'));
    }

    public function testWithIsCaseInsensitive(): void
    {
        $map = FieldMap::from([['Content-Type', 'text/html']]);

        $modified = $map->with('content-type', 'text/plain');

        static::assertSame([['content-type', 'text/plain']], $modified->toArray());
    }

    public function testWithAppendsWhenNotFound(): void
    {
        $map = FieldMap::from([['content-type', 'text/html']]);

        $modified = $map->with('accept', 'application/json');

        static::assertSame(
            [
                ['content-type', 'text/html'],
                ['accept',       'application/json'],
            ],
            $modified->toArray(),
        );
    }

    public function testWithRemovesDuplicates(): void
    {
        $map = FieldMap::from([
            ['set-cookie',   'a=1'],
            ['set-cookie',   'b=2'],
            ['content-type', 'text/html'],
        ]);

        $modified = $map->with('set-cookie', 'c=3');

        static::assertSame(
            [
                ['set-cookie',   'c=3'],
                ['content-type', 'text/html'],
            ],
            $modified->toArray(),
        );
    }

    public function testWithAddedAppends(): void
    {
        $map = FieldMap::from([['accept', 'text/html']]);

        $modified = $map->withAdded('accept', 'application/json');

        static::assertSame(
            [
                ['accept', 'text/html'],
                ['accept', 'application/json'],
            ],
            $modified->toArray(),
        );
    }

    public function testWithoutRemovesAll(): void
    {
        $map = FieldMap::from([
            ['set-cookie',   'a=1'],
            ['content-type', 'text/html'],
            ['set-cookie',   'b=2'],
        ]);

        $modified = $map->without('set-cookie');

        static::assertSame([['content-type', 'text/html']], $modified->toArray());
    }

    public function testWithoutIsCaseInsensitive(): void
    {
        $map = FieldMap::from([['Content-Type', 'text/html']]);

        $modified = $map->without('content-type');

        static::assertTrue($modified->isEmpty());
    }

    public function testToArrayPreservesOriginalCasing(): void
    {
        $fields = [
            ['Content-Type',    'text/html'],
            ['X-Custom-Header', 'value'],
        ];

        $map = FieldMap::from($fields);

        static::assertSame($fields, $map->toArray());
    }

    public function testIteratorYieldsAllPairs(): void
    {
        $fields = [
            ['content-type', 'text/html'],
            ['accept',       'application/json'],
        ];

        $map = FieldMap::from($fields);
        $result = [];
        foreach ($map as $pair) {
            $result[] = $pair;
        }

        static::assertSame($fields, $result);
    }

    public function testImmutability(): void
    {
        $map = FieldMap::from([['content-type', 'text/html']]);

        $map->with('content-type', 'text/plain');
        $map->withAdded('accept', 'text/html');
        $map->without('content-type');

        static::assertSame([['content-type', 'text/html']], $map->toArray());
    }

    public function testGetAllIsCaseInsensitive(): void
    {
        $map = FieldMap::from([
            ['Set-Cookie', 'a=1'],
            ['set-cookie', 'b=2'],
        ]);

        static::assertSame(['a=1', 'b=2'], $map->getAll('SET-COOKIE'));
    }

    public function testWithUppercaseNameReplacesCaseInsensitively(): void
    {
        $map = FieldMap::from([['content-type', 'text/html']]);

        $modified = $map->with('Content-Type', 'text/plain');

        static::assertSame([['Content-Type', 'text/plain']], $modified->toArray());
    }

    public function testFromEmptyReturnsDefaultInstance(): void
    {
        $a = FieldMap::from([]);
        $b = FieldMap::default();

        static::assertSame($a, $b);
    }

    public function testDefaultReturnsSameInstance(): void
    {
        $a = FieldMap::default();
        $b = FieldMap::default();

        static::assertSame($a, $b);
    }

    public function testCountReturnsNumberOfEntries(): void
    {
        $map = FieldMap::from([
            ['content-type', 'text/html'],
            ['accept',       'application/json'],
            ['set-cookie',   'a=1'],
        ]);

        static::assertCount(3, $map);
    }

    public function testCountReturnsZeroForEmptyFieldMap(): void
    {
        $map = FieldMap::default();

        static::assertCount(0, $map);
    }

    public function testCountIncludesDuplicateNames(): void
    {
        $map = FieldMap::from([
            ['set-cookie', 'a=1'],
            ['set-cookie', 'b=2'],
            ['set-cookie', 'c=3'],
        ]);

        static::assertCount(3, $map);
    }

    public function testWithoutCaseInsensitiveNameParameter(): void
    {
        $map = FieldMap::from([
            ['content-type', 'text/html'],
            ['accept',       'text/plain'],
        ]);

        $modified = $map->without('Content-Type');

        static::assertNull($modified->get('content-type'));
        static::assertSame('text/plain', $modified->get('accept'));
    }

    public function testWithoutUpperCaseNameRemovesLowerCaseField(): void
    {
        $map = FieldMap::from([
            ['X-Custom', 'value'],
            ['Accept',   'text/html'],
        ]);

        $modified = $map->without('X-CUSTOM');

        static::assertNull($modified->get('x-custom'));
        static::assertSame('text/html', $modified->get('accept'));
    }
}
