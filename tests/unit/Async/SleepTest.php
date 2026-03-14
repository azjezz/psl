<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;

final class SleepTest extends TestCase
{
    public function testSleepCompletes(): void
    {
        Async\run(static function (): void {
            Async\sleep(Duration::milliseconds(10));
        })->await();

        static::addToAssertionCount(1);
    }

    public function testSleepCancelledBySignal(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $token = new Async\SignalCancellationToken();

            Async\run(static function () use ($token): void {
                Async\sleep(Duration::milliseconds(5));
                $token->cancel();
            })->ignore();

            Async\sleep(Duration::seconds(5), $token);
        })->await();
    }

    public function testSleepCancelledByTimeout(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\sleep(Duration::seconds(5), $token);
        })->await();
    }

    public function testSleepWithAlreadyCancelledToken(): void
    {
        $token = new Async\SignalCancellationToken();
        $token->cancel();

        $this->expectException(Async\Exception\CancelledException::class);

        Async\sleep(Duration::seconds(5), $token);
    }

    public function testSleepWakesEarlyOnCancel(): void
    {
        $token = new Async\SignalCancellationToken();
        $task = Async\run(static fn() => Async\sleep(Duration::seconds(5), $token))->ignore();
        $token->cancel();

        static::assertTrue($token->isCancelled());

        $this->expectException(Async\Exception\CancelledException::class);

        $task->await();
    }
}
