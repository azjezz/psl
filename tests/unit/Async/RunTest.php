<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;

final class RunTest extends TestCase
{
    public function testRun(): void
    {
        $awaitable = Async\run(static function (): string {
            Async\concurrently([
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
            ]);

            return 'hello';
        });

        static::assertSame('hello', Async\await($awaitable));
    }
}
