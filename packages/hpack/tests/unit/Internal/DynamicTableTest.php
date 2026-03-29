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
        $table->insert('custom-key', 10, 'custom-value', 12);

        $entry = $table->get(0);

        static::assertSame(['custom-key', 'custom-value'], $entry);
        static::assertSame(1, $table->count());
    }

    public function testNewestEntryAtIndex0(): void
    {
        $table = new DynamicTable();
        $table->insert('first', 5, 'one', 3);
        $table->insert('second', 6, 'two', 3);

        static::assertSame(['second', 'two'], $table->get(0));
        static::assertSame(['first', 'one'], $table->get(1));
    }

    public function testEntrySize(): void
    {
        $table = new DynamicTable();
        $table->insert('name', 4, 'value', 5);

        static::assertSame(4 + 5 + 32, $table->size());
    }

    public function testFifoEviction(): void
    {
        $table = new DynamicTable(80);

        $table->insert('key1', 4, 'val1', 4);
        $table->insert('key2', 4, 'val2', 4);
        $table->insert('key3', 4, 'val3', 4);

        static::assertSame(['key3', 'val3'], $table->get(0));
        static::assertSame(['key2', 'val2'], $table->get(1));

        static::assertSame(2, $table->count());
    }

    public function testOversizedEntryEmptiesTable(): void
    {
        $table = new DynamicTable(64);
        $table->insert('a', 1, 'b', 1);

        static::assertSame(1, $table->count());

        $table->insert(str_repeat('x', 100), 100, 'y', 1);

        static::assertSame(0, $table->count());
        static::assertSame(0, $table->size());
    }

    public function testSetMaxSizeZeroClearsTable(): void
    {
        $table = new DynamicTable();
        $table->insert('key', 3, 'value', 5);

        static::assertSame(1, $table->count());

        $table->setMaxSize(0);

        static::assertSame(0, $table->count());
        static::assertSame(0, $table->size());
    }

    public function testSetMaxSizeEvictsOldest(): void
    {
        $table = new DynamicTable(4096);
        $table->insert('first', 5, 'one', 3);
        $table->insert('second', 6, 'two', 3);
        $table->insert('third', 5, 'three', 5);

        $table->setMaxSize(strlen('third') + strlen('three') + 32 + strlen('second') + strlen('two') + 32);

        static::assertSame(2, $table->count());
        static::assertSame(['third', 'three'], $table->get(0));
        static::assertSame(['second', 'two'], $table->get(1));
    }

    public function testSearchFullMatch(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 10, 'custom-value', 12);

        $result = $table->search('custom-key', 'custom-value');

        static::assertSame([0, true], $result);
    }

    public function testSearchNameOnly(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 10, 'custom-value', 12);

        $result = $table->search('custom-key', 'other-value');

        static::assertSame([0, false], $result);
    }

    public function testSearchNoMatch(): void
    {
        $table = new DynamicTable();
        $table->insert('custom-key', 10, 'custom-value', 12);

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
            $name = 'key' . $i;
            $value = 'val' . $i;
            $table->insert($name, strlen($name), $value, strlen($value));
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
            $table->insert('k', 1, 'v', 1);
        }

        static::assertSame(1, $table->count());
        static::assertSame(['k', 'v'], $table->get(0));
    }
}
