<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Exception\InvariantViolationException;
use Psl\Ref;

final class WaitGroupTest extends TestCase
{
    public function testWaitReturnsImmediatelyWhenCountIsZero(): void
    {
        $wg = new Async\WaitGroup();

        $wg->wait();

        static::assertSame(0, $wg->getCount());
    }

    public function testAddAndDone(): void
    {
        $wg = new Async\WaitGroup();

        $wg->add();
        static::assertSame(1, $wg->getCount());

        $wg->add();
        static::assertSame(2, $wg->getCount());

        $wg->done();
        static::assertSame(1, $wg->getCount());

        $wg->done();
        static::assertSame(0, $wg->getCount());
    }

    public function testDoneThrowsWhenCountIsZero(): void
    {
        $wg = new Async\WaitGroup();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('WaitGroup counter is already zero.');

        $wg->done();
    }

    public function testWaitBlocksUntilDone(): void
    {
        $ref = new Ref('');

        Async\run(static function () use ($ref): void {
            $wg = new Async\WaitGroup();

            $wg->add();
            Async\run(static function () use ($wg, $ref): void {
                Async\sleep(Duration::milliseconds(10));
                $ref->value .= 'task';
                $wg->done();
            })->ignore();

            $wg->wait();
            $ref->value .= '-waited';
        })->await();

        static::assertSame('task-waited', $ref->value);
    }

    public function testMultipleWaiters(): void
    {
        $count = 0;

        Async\run(static function () use (&$count): void {
            $wg = new Async\WaitGroup();
            $wg->add();

            $a = Async\run(static function () use ($wg, &$count): void {
                $wg->wait();
                $count++;
            });

            $b = Async\run(static function () use ($wg, &$count): void {
                $wg->wait();
                $count++;
            });

            Async\sleep(Duration::milliseconds(10));
            $wg->done();

            $a->await();
            $b->await();
        })->await();

        static::assertSame(2, $count);
    }

    public function testWaitWithCancellation(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $wg = new Async\WaitGroup();
            $wg->add();

            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\run(static function () use ($wg): void {
                Async\sleep(Duration::seconds(5));
                $wg->done();
            })->ignore();

            $wg->wait($token);
        })->await();
    }

    public function testWaitWithAlreadyCancelledToken(): void
    {
        $wg = new Async\WaitGroup();
        $wg->add();

        $token = new Async\SignalCancellationToken();
        $token->cancel();

        Async\run(static function () use ($wg): void {
            Async\sleep(Duration::seconds(5));
            $wg->done();
        })->ignore();

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function () use ($wg, $token): void {
            $wg->wait($token);
        })->await();
    }

    public function testCancelledWaitDoesNotAffectOtherWaiters(): void
    {
        $result = Async\run(static function (): bool {
            $wg = new Async\WaitGroup();
            $wg->add();
            $normalCompleted = false;

            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            Async\run(static function () use ($wg, $token): void {
                try {
                    $wg->wait($token);
                } catch (Async\Exception\CancelledException) {
                    // @mago-expect lint:no-empty-catch-clause - expected :)
                }
            })->ignore();

            Async\run(static function () use ($wg, &$normalCompleted): void {
                $wg->wait();
                $normalCompleted = true;
            })->ignore();

            Async\sleep(Duration::milliseconds(30));
            $wg->done();
            Async\later();

            return $normalCompleted;
        })->await();

        static::assertTrue($result);
    }

    public function testReusable(): void
    {
        $ref = new Ref('');

        Async\run(static function () use ($ref): void {
            $wg = new Async\WaitGroup();

            $wg->add();
            Async\run(static function () use ($wg, $ref): void {
                $ref->value .= 'a';
                $wg->done();
            })->ignore();

            $wg->wait();

            $wg->add();
            Async\run(static function () use ($wg, $ref): void {
                $ref->value .= 'b';
                $wg->done();
            })->ignore();

            $wg->wait();
        })->await();

        static::assertSame('ab', $ref->value);
    }

    public function testMultipleTasks(): void
    {
        $count = 0;

        Async\run(static function () use (&$count): void {
            $wg = new Async\WaitGroup();

            for ($i = 0; $i < 5; $i++) {
                $wg->add();
                Async\run(static function () use ($wg, &$count): void {
                    Async\sleep(Duration::milliseconds(5));
                    $count++;
                    $wg->done();
                })->ignore();
            }

            $wg->wait();
        })->await();

        static::assertSame(5, $count);
    }
}
