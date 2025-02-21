<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime;

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
         * @var Async\Semaphore<array{time: ?DateTime\Duration, value: string}, void>
         */
        $semaphore = new Async\Semaphore(1, static function (array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => DateTime\Duration::milliseconds(3),
            'value' => 'a',
        ]));
        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => DateTime\Duration::milliseconds(4),
            'value' => 'b',
        ]));
        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => DateTime\Duration::milliseconds(5),
            'value' => 'c',
        ]));
        $last = Async\run(static fn(): null => $semaphore->waitFor([
            'time' => null,
            'value' => 'd',
        ]));
        $last->await();

        static::assertSame(['a', 'b', 'c', 'd'], $spy->value);
    }

    public function testOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<array{time: ?DateTime\Duration, value: string}, void>
         */
        $semaphore = new Async\Semaphore(2, static function (array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => Datetime\Duration::milliseconds(3),
            'value' => 'a',
        ]));
        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => Datetime\Duration::milliseconds(4),
            'value' => 'b',
        ]));
        $beforeLast = Async\run(static fn(): null => $semaphore->waitFor([
            'time' => Datetime\Duration::milliseconds(5),
            'value' => 'c',
        ]));
        Async\run(static fn(): null => $semaphore->waitFor([
            'time' => null,
            'value' => 'd',
        ]));

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

            Async\sleep(Datetime\Duration::milliseconds(2));
        });

        $awaitable = Async\run(static fn(): null => $semaphore->waitFor('hello'));

        Async\sleep(Datetime\Duration::milliseconds(1));

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

            Async\sleep(Datetime\Duration::milliseconds(2));
        });

        Async\run(static fn(): null => $semaphore->waitFor('hello'));
        $awaitable = Async\run(static fn(): null => $semaphore->waitFor('world'));

        Async\sleep(Datetime\Duration::milliseconds(1));

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
            Async\sleep(Datetime\Duration::milliseconds(40));

            return $input;
        });

        static::assertSame(1, $semaphore->getConcurrencyLimit());

        $one = Async\run(static fn(): string => $semaphore->waitFor('one'));
        $two = Async\run(static fn(): string => $semaphore->waitFor('two'));

        Async\sleep(Datetime\Duration::milliseconds(10));

        $semaphore->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('one', $one->await());

        $this->expectException(Async\Exception\TimeoutException::class);
        $this->expectExceptionMessage('The semaphore is destroyed.');

        $two->await();
    }

    public function testSemaphoreStatus(): void
    {
        /**
         * @var Async\Semaphore<string, string>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(Datetime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $semaphore->waitFor('one'));
        $two = Async\run(static fn(): string => $semaphore->waitFor('two'));
        static::assertSame(0, $semaphore->getIngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertFalse($semaphore->hasIngoingOperations());
        static::assertFalse($semaphore->hasPendingOperations());
        Async\later();
        static::assertSame(1, $semaphore->getIngoingOperations());
        static::assertSame(1, $semaphore->getPendingOperations());
        static::assertTrue($semaphore->hasPendingOperations());
        static::assertTrue($semaphore->hasIngoingOperations());
        $one->await();
        static::assertSame(1, $semaphore->getIngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertTrue($semaphore->hasIngoingOperations());
        static::assertFalse($semaphore->hasPendingOperations());
        $two->await();
        static::assertSame(0, $semaphore->getIngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertFalse($semaphore->hasIngoingOperations());
        static::assertFalse($semaphore->hasPendingOperations());
    }

    public function testWaitForPending(): void
    {
        /**
         * @var Async\Semaphore<string, string>
         */
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(Datetime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $semaphore->waitFor('one'));
        Async\later();
        static::assertFalse($one->isComplete());
        $semaphore->waitForPending();
        static::assertTrue($one->isComplete());
        static::assertSame('one', $one->await());
    }
}
