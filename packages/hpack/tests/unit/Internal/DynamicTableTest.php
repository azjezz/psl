<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Internal\DynamicTable;

use function str_repeat;
use function strlen;

final class DynamicTableTest extends TestCase
{
    public function testInsertAndRetrieve(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 'custom-value');

        $entry = $table->get(0);

        static::assertSame(['custom-key', 'custom-value'], $entry);
        static::assertSame(1, $table->count());
    }

    public function testNewestEntryAtIndex0(): void
    {
        $table = new DynamicTable();
        $table->insert('first', 'one');
        $table->insert('second', 'two');

        static::assertSame(['second', 'two'], $table->get(0));
        static::assertSame(['first', 'one'], $table->get(1));
    }

    public function testEntrySize(): void
    {
        $table = new DynamicTable();
        $table->insert('name', 'value');

        static::assertSame(4 + 5 + 32, $table->size());
    }

    public function testFifoEviction(): void
    {
        $table = new DynamicTable(80);

        $table->insert('key1', 'val1');
        $table->insert('key2', 'val2');
        $table->insert('key3', 'val3');

        static::assertSame(['key3', 'val3'], $table->get(0));
        static::assertSame(['key2', 'val2'], $table->get(1));

        static::assertSame(2, $table->count());
    }

    public function testOversizedEntryEmptiesTable(): void
    {
        $table = new DynamicTable(64);
        $table->insert('a', 'b');

        static::assertSame(1, $table->count());

        $table->insert(str_repeat('x', 100), 'y');

        static::assertSame(0, $table->count());
        static::assertSame(0, $table->size());
    }

    public function testSetMaxSizeZeroClearsTable(): void
    {
        $table = new DynamicTable();
        $table->insert('key', 'value');

        static::assertSame(1, $table->count());

        $table->setMaxSize(0);

        static::assertSame(0, $table->count());
        static::assertSame(0, $table->size());
    }

    public function testSetMaxSizeEvictsOldest(): void
    {
        $table = new DynamicTable(4096);
        $table->insert('first', 'one');
        $table->insert('second', 'two');
        $table->insert('third', 'three');

        $table->setMaxSize(strlen('third') + strlen('three') + 32 + strlen('second') + strlen('two') + 32);

        static::assertSame(2, $table->count());
        static::assertSame(['third', 'three'], $table->get(0));
        static::assertSame(['second', 'two'], $table->get(1));
    }

    public function testSearchFullMatch(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 'custom-value');

        $result = $table->search('custom-key', 'custom-value');

        static::assertSame([0, true], $result);
    }

    public function testSearchNameOnly(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 'custom-value');

        $result = $table->search('custom-key', 'other-value');

        static::assertSame([0, false], $result);
    }

    public function testSearchNoMatch(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 'custom-value');

        $result = $table->search('unknown', 'value');

        static::assertNull($result);
    }

    public function testSearchEmptyTable(): void
    {
        $table = new DynamicTable();

        static::assertNull($table->search('key', 'value'));
    }

    public function testGetOutOfRange(): void
    {
        $table = new DynamicTable();

        static::assertNull($table->get(0));
        static::assertNull($table->get(1));
    }

    public function testMultipleInserts(): void
    {
        $table = new DynamicTable();

        for ($i = 0; $i < 10; $i++) {
            $table->insert('key' . $i, 'val' . $i);
        }

        static::assertSame(10, $table->count());
        static::assertSame(['key9', 'val9'], $table->get(0));
        static::assertSame(['key0', 'val0'], $table->get(9));
    }

    public function testInternalCompactionAfterManyEvictions(): void
    {
        $entrySize = 1 + 1 + 32;
        $table = new DynamicTable($entrySize);

        for ($i = 0; $i < 300; $i++) {
            $table->insert('k', 'v');
        }

        static::assertSame(1, $table->count());
        static::assertSame(['k', 'v'], $table->get(0));
    }
}
