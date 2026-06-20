<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;

final class RunTest extends TestCase
{
    public function testRun(): void
    {
        $awaitable = Async\run::<string>(static function (): string {
            Async\concurrently::<int, null>([
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
                static fn(): null => Async\sleep(DateTime\Duration::milliseconds(1)),
            ]);

            return 'hello';
        });

        static::assertSame('hello', Async\await::<string>($awaitable));
    }
}
