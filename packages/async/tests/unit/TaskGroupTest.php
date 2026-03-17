<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Ref;
use RuntimeException;

final class TaskGroupTest extends TestCase
{
    public function testAwaitAllWithNoTasks(): void
    {
        $group = new Async\TaskGroup();

        $group->awaitAll();

        static::addToAssertionCount(1);
    }

    public function testAllTasksRun(): void
    {
        $ref = new Ref('');

        Async\run(static function () use ($ref): void {
            $group = new Async\TaskGroup();

            $group->defer(static function () use ($ref): void {
                $ref->value .= 'a';
            });

            $group->defer(static function () use ($ref): void {
                $ref->value .= 'b';
            });

            $group->awaitAll();
        })->await();

        static::assertSame('ab', $ref->value);
    }

    public function testTasksRunConcurrently(): void
    {
        $result = Async\run(static function (): string {
            $ref = new Ref('');
            $group = new Async\TaskGroup();

            $group->defer(static function () use ($ref): void {
                Async\sleep(Duration::milliseconds(20));
                $ref->value .= 'slow';
            });

            $group->defer(static function () use ($ref): void {
                $ref->value .= 'fast';
            });

            $group->awaitAll();

            return $ref->value;
        })->await();

        static::assertSame('fastslow', $result);
    }

    public function testSingleTaskThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('boom');

        Async\run(static function (): void {
            $group = new Async\TaskGroup();

            $group->defer(static function (): void {
                throw new RuntimeException('boom');
            });

            $group->awaitAll();
        })->await();
    }

    public function testMultipleTasksThrowCompositeException(): void
    {
        $this->expectException(Async\Exception\CompositeException::class);

        Async\run(static function (): void {
            $group = new Async\TaskGroup();

            $group->defer(static function (): void {
                throw new RuntimeException('first');
            });

            $group->defer(static function (): void {
                throw new RuntimeException('second');
            });

            $group->awaitAll();
        })->await();
    }

    public function testAwaitAllWithCancellation(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $group = new Async\TaskGroup();
            $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

            $group->defer(static function (): void {
                Async\sleep(Duration::seconds(5));
            });

            $group->awaitAll($token);
        })->await();
    }

    public function testAwaitAllWithAlreadyCancelledToken(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $token = new Async\SignalCancellationToken();
            $token->cancel();

            $group = new Async\TaskGroup();

            $group->defer(static function (): void {
                Async\sleep(Duration::seconds(5));
            });

            $group->awaitAll($token);
        })->await();
    }

    public function testAwaitAllClearsTaskList(): void
    {
        $count = 0;

        Async\run(static function () use (&$count): void {
            $group = new Async\TaskGroup();

            $group->defer(static function () use (&$count): void {
                $count++;
            });

            $group->awaitAll();
            $group->awaitAll();
        })->await();

        static::assertSame(1, $count);
    }

    public function testDeferAfterAwaitAll(): void
    {
        $ref = new Ref('');

        Async\run(static function () use ($ref): void {
            $group = new Async\TaskGroup();

            $group->defer(static function () use ($ref): void {
                $ref->value .= 'first';
            });

            $group->awaitAll();

            $group->defer(static function () use ($ref): void {
                $ref->value .= '-second';
            });

            $group->awaitAll();
        })->await();

        static::assertSame('first-second', $ref->value);
    }
}
