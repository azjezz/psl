<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Internal\PoolReleasingBodyHandle;
use Psl\IO\MemoryHandle;

final class PoolReleasingBodyHandleTest extends TestCase
{
    public function testReadDelegatesToInner(): void
    {
        $inner = new MemoryHandle('hello world');
        $released = false;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$released): void {
            $released = true;
        });

        $data = $handle->read(5);

        static::assertSame('hello', $data);
    }

    public function testTryReadDelegatesToInner(): void
    {
        $inner = new MemoryHandle('hello world');
        $released = false;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$released): void {
            $released = true;
        });

        $data = $handle->tryRead(5);

        static::assertSame('hello', $data);
    }

    public function testReachedEndOfDataSourceDelegatesToInner(): void
    {
        $inner = new MemoryHandle('data');
        $released = false;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$released): void {
            $released = true;
        });

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read();
        $handle->tryRead();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReleaseCalledOnEof(): void
    {
        $inner = new MemoryHandle('hi');
        $released = false;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$released): void {
            $released = true;
        });

        $handle->read();
        static::assertFalse($released);

        $handle->tryRead();
        static::assertTrue($released);
    }

    public function testNotReleasedBeforeEof(): void
    {
        $inner = new MemoryHandle('hello');
        $released = false;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$released): void {
            $released = true;
        });

        $handle->read(3);
        static::assertFalse($released);
        static::assertFalse($handle->reachedEndOfDataSource());
        static::assertFalse($released);
    }

    public function testReleaseCalledOnlyOnce(): void
    {
        $inner = new MemoryHandle('hi');
        $releaseCount = 0;
        $handle = new PoolReleasingBodyHandle($inner, static function () use (&$releaseCount): void {
            $releaseCount++;
        });

        $handle->read();
        $handle->tryRead();
        static::assertSame(1, $releaseCount);

        $handle->reachedEndOfDataSource();
        static::assertSame(1, $releaseCount);

        $handle->tryRead();
        static::assertSame(1, $releaseCount);
    }
}
