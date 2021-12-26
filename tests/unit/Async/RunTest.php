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
            Async\concurrently([
                static fn() => Async\sleep(0.001),
                static fn() => Async\sleep(0.001),
                static fn() => Async\sleep(0.001),
            ]);

            return 'hello';
        });

        static::assertSame('hello', Async\await($awaitable));
    }
}
