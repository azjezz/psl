<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Internal\StaticTable;

final class StaticTableTest extends TestCase
{
    public function testFirstEntry(): void
    {
        $entry = StaticTable::get(1);

        static::assertSame([':authority', ''], $entry);
    }

    public function testLastEntry(): void
    {
        $entry = StaticTable::get(61);

        static::assertSame(['www-authenticate', ''], $entry);
    }

    public function testMethodGet(): void
    {
        $entry = StaticTable::get(2);

        static::assertSame([':method', 'GET'], $entry);
    }

    public function testMethodPost(): void
    {
        $entry = StaticTable::get(3);

        static::assertSame([':method', 'POST'], $entry);
    }

    public function testPath(): void
    {
        static::assertSame([':path', '/'], StaticTable::get(4));
        static::assertSame([':path', '/index.html'], StaticTable::get(5));
    }

    public function testScheme(): void
    {
        static::assertSame([':scheme', 'http'], StaticTable::get(6));
        static::assertSame([':scheme', 'https'], StaticTable::get(7));
    }

    public function testStatus200(): void
    {
        static::assertSame([':status', '200'], StaticTable::get(8));
    }

    public function testAcceptEncoding(): void
    {
        static::assertSame(['accept-encoding', 'gzip, deflate'], StaticTable::get(16));
    }

    public function testIndex62ReturnsNull(): void
    {
        static::assertNull(StaticTable::get(62));
    }

    public function testSearchFullMatch(): void
    {
        $result = StaticTable::search(':method', 'GET');

        static::assertSame([2, true], $result);
    }

    public function testSearchNameOnly(): void
    {
        $result = StaticTable::search(':method', 'PUT');

        static::assertSame([2, false], $result);
    }

    public function testSearchNoMatch(): void
    {
        $result = StaticTable::search('x-custom', 'foo');

        static::assertNull($result);
    }

    public function testSearchStatusFullMatch(): void
    {
        static::assertSame([8, true], StaticTable::search(':status', '200'));
        static::assertSame([9, true], StaticTable::search(':status', '204'));
        static::assertSame([10, true], StaticTable::search(':status', '206'));
        static::assertSame([11, true], StaticTable::search(':status', '304'));
        static::assertSame([12, true], StaticTable::search(':status', '400'));
        static::assertSame([13, true], StaticTable::search(':status', '404'));
        static::assertSame([14, true], StaticTable::search(':status', '500'));
    }

    public function testSearchStatusNameOnly(): void
    {
        $result = StaticTable::search(':status', '201');

        static::assertSame([8, false], $result);
    }

    public function testSearchAcceptEncodingFullMatch(): void
    {
        $result = StaticTable::search('accept-encoding', 'gzip, deflate');

        static::assertSame([16, true], $result);
    }

    public function testAll61EntriesExist(): void
    {
        for ($i = 1; $i <= 61; $i++) {
            $entry = StaticTable::get($i);
            static::assertNotNull($entry);
            static::assertCount(2, $entry);
            static::assertIsString($entry[0]);
            static::assertIsString($entry[1]);
        }
    }
}
