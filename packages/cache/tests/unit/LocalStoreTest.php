<?php

declare(strict_types=1);

namespace Psl\Cache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Cache\Exception\UnavailableItemException;
use Psl\Cache\LocalStore;
use Psl\DateTime\Duration;

final class LocalStoreTest extends TestCase
{
    public function testComputeStoresAndReturns(): void
    {
        $store = new LocalStore();

        $value = $store->compute('key', static fn(): string => 'hello');

        static::assertSame('hello', $value);
    }

    public function testComputeReturnsCachedOnSecondCall(): void
    {
        $store = new LocalStore();
        $calls = 0;

        $computer = static function () use (&$calls): string {
            $calls++;
            return 'value';
        };

        $store->compute('key', $computer);
        $store->compute('key', $computer);

        static::assertSame(1, $calls);
    }

    public function testGetReturnsComputedValue(): void
    {
        $store = new LocalStore();
        $store->compute('key', static fn(): string => 'cached');

        static::assertSame('cached', $store->get('key'));
    }

    public function testGetThrowsForMissingKey(): void
    {
        $store = new LocalStore();

        $this->expectException(UnavailableItemException::class);
        $store->get('missing');
    }

    public function testDeleteRemovesEntry(): void
    {
        $store = new LocalStore();
        $store->compute('key', static fn(): string => 'value');

        $store->delete('key');

        $this->expectException(UnavailableItemException::class);
        $store->get('key');
    }

    public function testDeleteNonExistentKeyDoesNothing(): void
    {
        $store = new LocalStore();
        $store->delete('nonexistent');

        // No exception - just a no-op
        static::addToAssertionCount(1);
    }

    public function testUpdateAlwaysInvokesComputer(): void
    {
        $store = new LocalStore();
        $store->compute('counter', static fn(): int => 0);

        $result = $store->update('counter', static fn(null|int $old): int => ($old ?? 0) + 1);
        static::assertSame(1, $result);

        $result = $store->update('counter', static fn(null|int $old): int => ($old ?? 0) + 1);
        static::assertSame(2, $result);

        static::assertSame(2, $store->get('counter'));
    }

    public function testUpdateOnMissingKeyCreatesEntry(): void
    {
        $store = new LocalStore();

        $result = $store->update('new', static fn(null|int $old): int => ($old ?? 0) + 10);

        static::assertSame(10, $result);
        static::assertSame(10, $store->get('new'));
    }

    public function testLruEviction(): void
    {
        $store = new LocalStore(maxSize: 3);

        $store->compute('a', static fn(): string => 'A');
        $store->compute('b', static fn(): string => 'B');
        $store->compute('c', static fn(): string => 'C');

        $store->compute('d', static fn(): string => 'D');

        $this->expectException(UnavailableItemException::class);
        $store->get('a');
    }

    public function testLruEvictionRespectsAccessOrder(): void
    {
        $store = new LocalStore(maxSize: 3);

        $store->compute('a', static fn(): string => 'A');
        $store->compute('b', static fn(): string => 'B');
        $store->compute('c', static fn(): string => 'C');

        $store->get('a');

        $store->compute('d', static fn(): string => 'D');

        static::assertSame('A', $store->get('a'));
        static::assertSame('D', $store->get('d'));

        $this->expectException(UnavailableItemException::class);
        $store->get('b');
    }

    public function testTtlExpiration(): void
    {
        $store = new LocalStore(cleanupInterval: Duration::milliseconds(50));

        $store->compute('key', static fn(): string => 'value', Duration::milliseconds(100));

        static::assertSame('value', $store->get('key'));

        Async\sleep(Duration::milliseconds(200));

        $this->expectException(UnavailableItemException::class);
        $store->get('key');
    }

    public function testTtlExpirationRecomputesOnNextAccess(): void
    {
        $store = new LocalStore(cleanupInterval: Duration::milliseconds(50));
        $calls = 0;

        $store->compute(
            'key',
            static function () use (&$calls): string {
                $calls++;
                return 'v' . $calls;
            },
            Duration::milliseconds(100),
        );

        static::assertSame('v1', $store->get('key'));

        // Wait for expiration
        Async\sleep(Duration::milliseconds(200));

        // compute again - should call computer since expired
        $value = $store->compute(
            'key',
            static function () use (&$calls): string {
                $calls++;
                return 'v' . $calls;
            },
            Duration::milliseconds(100),
        );

        static::assertSame('v2', $value);
        static::assertSame(2, $calls);
    }

    public function testNoTtlNeverExpires(): void
    {
        $store = new LocalStore(cleanupInterval: Duration::milliseconds(50));

        $store->compute('permanent', static fn(): string => 'forever');

        Async\sleep(Duration::milliseconds(150));

        static::assertSame('forever', $store->get('permanent'));
    }

    public function testMaxSizeOne(): void
    {
        $store = new LocalStore(maxSize: 1);

        $store->compute('a', static fn(): string => 'A');
        $store->compute('b', static fn(): string => 'B');

        static::assertSame('B', $store->get('b'));

        $this->expectException(UnavailableItemException::class);
        $store->get('a');
    }

    public function testComputeWithDifferentKeys(): void
    {
        $store = new LocalStore();

        $store->compute('x', static fn(): int => 1);
        $store->compute('y', static fn(): int => 2);
        $store->compute('z', static fn(): int => 3);

        static::assertSame(1, $store->get('x'));
        static::assertSame(2, $store->get('y'));
        static::assertSame(3, $store->get('z'));
    }

    public function testUpdateWithTtl(): void
    {
        $store = new LocalStore(cleanupInterval: Duration::milliseconds(50));

        $store->update('key', static fn(null|string $old): string => 'value', Duration::milliseconds(100));

        static::assertSame('value', $store->get('key'));

        Async\sleep(Duration::milliseconds(200));

        $this->expectException(UnavailableItemException::class);
        $store->get('key');
    }

    public function testConcurrentComputeOnSameKeyDeduplicates(): void
    {
        $store = new LocalStore();
        $calls = 0;

        $computer = static function () use (&$calls): string {
            $calls++;
            Async\sleep(Duration::milliseconds(50));
            return 'result';
        };

        [$a, $b] = Async\concurrently([
            static fn() => $store->compute('key', $computer),
            static fn() => $store->compute('key', $computer),
        ]);

        static::assertSame('result', $a);
        static::assertSame('result', $b);
        static::assertSame(1, $calls);
    }

    public function testConcurrentComputeOnDifferentKeysRunsInParallel(): void
    {
        $store = new LocalStore();
        $calls = 0;

        $computer = static function () use (&$calls): string {
            $calls++;
            return 'result';
        };

        Async\concurrently([
            static fn() => $store->compute('a', $computer),
            static fn() => $store->compute('b', $computer),
        ]);

        static::assertSame(2, $calls);
    }

    public function testOverwriteExistingKeyUpdatesLruPosition(): void
    {
        $store = new LocalStore(maxSize: 3);

        $store->compute('a', static fn(): string => 'A');
        $store->compute('b', static fn(): string => 'B');
        $store->compute('c', static fn(): string => 'C');

        $store->update('a', static fn(null|string $old): string => 'A2');

        $store->compute('d', static fn(): string => 'D');

        static::assertSame('A2', $store->get('a'));

        $this->expectException(UnavailableItemException::class);
        $store->get('b');
    }
}
