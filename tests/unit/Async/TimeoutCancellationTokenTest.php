<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;

final class TimeoutCancellationTokenTest extends TestCase
{
    public function testIsNotCancelledImmediately(): void
    {
        $token = new Async\TimeoutCancellationToken(Duration::seconds(10));

        static::assertFalse($token->isCancelled());
    }

    public function testCancelsAfterTimeout(): void
    {
        $result = Async\run(static function (): bool {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\sleep(Duration::milliseconds(50));

            return $token->isCancelled();
        })->await();

        static::assertTrue($result);
    }

    public function testThrowIfCancelledAfterTimeout(): void
    {
        $previous = Async\run(static function (): \Throwable|null {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\sleep(Duration::milliseconds(50));

            try {
                $token->throwIfCancelled();

                return null;
            } catch (Async\Exception\CancelledException $e) {
                return $e->getPrevious();
            }
        })->await();

        static::assertInstanceOf(Async\Exception\TimeoutException::class, $previous);
    }

    public function testSubscriberCalledOnTimeout(): void
    {
        $result = Async\run(static function (): bool {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));
            $called = false;

            $token->subscribe(static function (Async\Exception\CancelledException $e) use (&$called): void {
                $called = true;
            });

            Async\sleep(Duration::milliseconds(50));

            return $called;
        })->await();

        static::assertTrue($result);
    }

    public function testUnsubscribePreventsCallback(): void
    {
        $result = Async\run(static function (): bool {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));
            $called = false;

            $id = $token->subscribe(static function (Async\Exception\CancelledException $e) use (&$called): void {
                $called = true;
            });

            $token->unsubscribe($id);

            Async\sleep(Duration::milliseconds(50));

            return $called;
        })->await();

        static::assertFalse($result);
    }

    public function testZeroDurationCancelsImmediately(): void
    {
        $result = Async\run(static function (): bool {
            $token = new Async\TimeoutCancellationToken(Duration::zero());

            Async\sleep(Duration::milliseconds(10));

            return $token->isCancelled();
        })->await();

        static::assertTrue($result);
    }

    public function testGetTokenReturnsSelf(): void
    {
        $token = Async\run(static function (): Async\CancellationTokenInterface {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\sleep(Duration::milliseconds(50));

            try {
                $token->throwIfCancelled();

                throw new \RuntimeException('Expected CancelledException');
            } catch (Async\Exception\CancelledException $e) {
                return $e->getToken();
            }
        })->await();

        static::assertInstanceOf(Async\TimeoutCancellationToken::class, $token);
    }

    public function testSubscribeAfterTimeoutInvokesImmediately(): void
    {
        $result = Async\run(static function (): bool {
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\sleep(Duration::milliseconds(50));

            /** @var bool $called */
            $called = false;
            $id = $token->subscribe(static function (Async\Exception\CancelledException $_) use (&$called): void {
                $called = true;
            });

            return $called && $id === 'null';
        })->await();

        static::assertTrue($result);
    }

    public function testCancellableIsTrue(): void
    {
        $token = new Async\TimeoutCancellationToken(Duration::seconds(10));

        static::assertTrue($token->cancellable);
    }

    public function testWeakReferenceDroppedBeforeTimeout(): void
    {
        Async\run(static function (): void {
            // Create a token with a long timeout, then drop all references
            $token = new Async\TimeoutCancellationToken(Duration::seconds(10));
            unset($token);

            gc_collect_cycles();

            Async\sleep(Duration::milliseconds(10));
        })->await();

        // If we got here without error, the WeakReference null check worked
        static::addToAssertionCount(1);
    }
}
