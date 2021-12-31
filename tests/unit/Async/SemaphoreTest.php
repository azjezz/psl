<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;

final class SemaphoreTest extends TestCase
{
    public function testItCallsTheOperation(): void
    {
        $sequence = new Async\Semaphore(1, static function (int $input): int {
            return $input * 2;
        });

        static::assertSame(4, $sequence->waitFor(2));
    }

    public function testSequenceOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<array{time: ?float, value: string}, void>
         */
        $semaphore = new Async\Semaphore(1, static function (array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn() => $semaphore->waitFor(['time' => 0.003, 'value' => 'a']));
        Async\run(static fn() => $semaphore->waitFor(['time' => 0.004, 'value' => 'b']));
        Async\run(static fn() => $semaphore->waitFor(['time' => 0.005, 'value' => 'c']));
        $last = Async\run(static fn() => $semaphore->waitFor(['time' => null, 'value' => 'd']));
        $last->await();

        static::assertSame(['a', 'b', 'c', 'd'], $spy->value);
    }

    public function testOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<array{time: ?float, value: string}, void>
         */
        $semaphore = new Async\Semaphore(2, static function (array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn() => $semaphore->waitFor(['time' => 0.003, 'value' => 'a']));
        Async\run(static fn() => $semaphore->waitFor(['time' => 0.004, 'value' => 'b']));
        $beforeLast = Async\run(static fn() => $semaphore->waitFor(['time' => 0.005, 'value' => 'c']));
        Async\run(static fn() => $semaphore->waitFor(['time' => null, 'value' => 'd']));

        $beforeLast->await();

        static::assertSame(['a', 'b', 'd', 'c'], $spy->value);
    }

    public function testOperationIsStartedIfLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<string, void>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(0.002);
        });

        $awaitable = Async\run(static fn() => $semaphore->waitFor('hello'));

        Async\sleep(0.001);

        static::assertSame(['hello'], $spy->value);

        $awaitable->await();
    }

    public function testOperationIsNotStartedIfLimitIsReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<string, void>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(0.002);
        });

        Async\run(static fn() => $semaphore->waitFor('hello'));
        $awaitable = Async\run(static fn() => $semaphore->waitFor('world'));

        Async\sleep(0.001);

        static::assertNotContains('world', $spy->value);

        $awaitable->await();
    }

    public function testCancelingTheSemaphoreAllowsForFutureOperations(): void
    {
        /**
         * @var Async\Semaphore<string, string>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            return $input;
        });

        $semaphore->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('hello', $semaphore->waitFor('hello'));
    }

    public function testCancelPendingOperationsButNotTheOngoingOne(): void
    {
        /**
         * @var Async\Semaphore<string, string>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(0.04);

            return $input;
        });

        $one = Async\run(static fn() => $semaphore->waitFor('one'));
        $two = Async\run(static fn() => $semaphore->waitFor('two'));

        Async\sleep(0.01);

        $semaphore->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('one', $one->await());

        $this->expectException(Async\Exception\TimeoutException::class);
        $this->expectExceptionMessage('The semaphore is destroyed.');

        $two->await();
    }

    public function testIsBusy(): void
    {
        /**
         * @var Async\Semaphore<string, string>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(0.04);

            return $input;
        });

        $one = Async\run(static fn() => $semaphore->waitFor('one'));
        static::assertFalse($semaphore->isFull());
        Async\later();
        static::assertTrue($semaphore->isFull());
        $one->await();
        static::assertFalse($semaphore->isFull());
    }
}
