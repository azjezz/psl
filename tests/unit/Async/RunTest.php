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
            Async\concurrent([
                static fn() => Async\sleep(0.001),
                static fn() => Async\sleep(0.001),
                static fn() => Async\sleep(0.001),
            ]);

            return 'hello';
        });

        static::assertSame('hello', Async\await($awaitable));
    }

    public function testRunWithTimeout(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\concurrent([
                static fn() => Async\sleep(0.01),
                static fn() => Async\sleep(0.01),
                static fn() => Async\sleep(0.01),
            ]);

            return 'hello';
        }, timeout: 0.02);

        static::assertSame('hello', $awaitable->await());
    }

    public function testRunTimedOut(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\concurrent([
                static fn() => Async\sleep(1),
                static fn() => Async\sleep(1),
                static fn() => Async\sleep(1),
            ]);

            return 'hello';
        }, timeout: 0.0001);

        $this->expectException(Async\Exception\TimeoutException::class);

        $awaitable->await();
    }
}
