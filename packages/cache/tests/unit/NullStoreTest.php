<?php

declare(strict_types=1);

namespace Psl\Cache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Cache\Exception\UnavailableItemException;
use Psl\Cache\NullStore;
use Psl\DateTime\Duration;

final class NullStoreTest extends TestCase
{
    public function testGetAlwaysThrows(): void
    {
        $store = new NullStore();

        $this->expectException(UnavailableItemException::class);
        $store->get('anything');
    }

    public function testComputeAlwaysInvokesComputer(): void
    {
        $store = new NullStore();
        $calls = 0;

        $result1 = $store->compute('key', static function () use (&$calls): string {
            $calls++;
            return 'value';
        });

        $result2 = $store->compute('key', static function () use (&$calls): string {
            $calls++;
            return 'value';
        });

        static::assertSame('value', $result1);
        static::assertSame('value', $result2);
        static::assertSame(2, $calls);
    }

    public function testComputeIgnoresTtl(): void
    {
        $store = new NullStore();

        $result = $store->compute('key', static fn(): string => 'value', Duration::hours(1));

        static::assertSame('value', $result);
    }

    public function testUpdateAlwaysInvokesWithNull(): void
    {
        $store = new NullStore();

        $result = $store->update('key', static fn(null|int $old): int => ($old ?? 0) + 1);

        static::assertSame(1, $result);
    }

    public function testDeleteIsNoOp(): void
    {
        $store = new NullStore();

        $store->delete('anything');

        // No exception
        static::assertTrue(true);
    }
}
