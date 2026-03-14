<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use RuntimeException;

final class OptionalIncrementalTimeoutTest extends TestCase
{
    public function testNullTimeoutReturnsNull(): void
    {
        $timer = new Async\OptionalIncrementalTimeout(null, static function (): never {
            throw new RuntimeException('Should not be called');
        });

        static::assertNull($timer->getRemaining());
    }

    public function testPositiveTimeoutReturnsRemainingDuration(): void
    {
        $timer = new Async\OptionalIncrementalTimeout(Duration::seconds(10), static function (): never {
            throw new RuntimeException('Should not be called');
        });

        $remaining = $timer->getRemaining();

        static::assertNotNull($remaining);
        static::assertTrue($remaining->isPositive());
    }

    public function testZeroDurationInvokesHandlerImmediately(): void
    {
        $called = false;

        $timer = new Async\OptionalIncrementalTimeout(Duration::zero(), static function () use (&$called): null {
            $called = true;

            return null;
        });

        $result = $timer->getRemaining();

        static::assertTrue($called);
        static::assertNull($result);
    }

    public function testNegativeDurationInvokesHandlerImmediately(): void
    {
        $called = false;

        $timer = new Async\OptionalIncrementalTimeout(Duration::milliseconds(-1), static function () use (
            &$called,
        ): null {
            $called = true;

            return null;
        });

        $result = $timer->getRemaining();

        static::assertTrue($called);
        static::assertNull($result);
    }

    public function testHandlerReturnValueIsReturned(): void
    {
        $timer = new Async\OptionalIncrementalTimeout(Duration::zero(), static fn(): Duration => Duration::seconds(5));

        $result = $timer->getRemaining();

        static::assertNotNull($result);
        static::assertSame(5, $result->getSeconds());
    }

    public function testExpiredTimeoutInvokesHandler(): void
    {
        Async\run(static function (): void {
            $called = false;

            $timer = new Async\OptionalIncrementalTimeout(Duration::milliseconds(1), static function () use (
                &$called,
            ): null {
                $called = true;

                return null;
            });

            Async\sleep(Duration::milliseconds(10));

            $timer->getRemaining();

            static::assertTrue($called);
        })->await();
    }
}
