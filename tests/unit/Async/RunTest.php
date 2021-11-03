<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;

final class RunTest extends TestCase
{
    public function testRun(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\await(Async\concurrently([
                static fn() => Async\usleep(100),
                static fn() => Async\usleep(100),
                static fn() => Async\usleep(100),
            ]));

            return 'hello';
        });

        static::assertSame('hello', $awaitable->await());
    }

    public function testRunWithTimeout(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\await(Async\concurrently([
                static fn() => Async\usleep(10_000),
                static fn() => Async\usleep(10_000),
                static fn() => Async\usleep(10_000),
            ]));

            return 'hello';
        }, timeout_ms: 20_000);

        static::assertSame('hello', $awaitable->await());
    }

    public function testRunTimedOut(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\await(Async\concurrently([
                static fn() => Async\usleep(1000),
                static fn() => Async\usleep(1000),
                static fn() => Async\usleep(1000),
            ]));

            return 'hello';
        }, timeout_ms: 100);

        $this->expectException(Async\Exception\TimeoutException::class);

        $awaitable->await();
    }
}
