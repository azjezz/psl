<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime;

final class SemaphoreTest extends TestCase
{
    public function testItCallsTheOperation(): void
    {
        $sequence = new Async\Semaphore(1, static fn(int $input): int => $input * 2);

        static::assertSame(4, $sequence->waitFor(2));
    }

    public function testSequenceOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Semaphore<array{time: ?DateTime\Duration, value: string}, void>
         */
        $semaphore = new Async\Semaphore(1, static function (array $data) use ($spy): void {
            if (null !== $data['time']) {
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
            if (null !== $data['time']) {
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
        $semaphore = new Async\Semaphore(1, static fn(string $input): string => $input);

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
        static::assertSame(0, $semaphore->getOngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertFalse($semaphore->hasOngoingOperations());
        static::assertFalse($semaphore->hasPendingOperations());
        Async\later();
        static::assertSame(1, $semaphore->getOngoingOperations());
        static::assertSame(1, $semaphore->getPendingOperations());
        static::assertTrue($semaphore->hasPendingOperations());
        static::assertTrue($semaphore->hasOngoingOperations());
        $one->await();
        static::assertSame(1, $semaphore->getOngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertTrue($semaphore->hasOngoingOperations());
        static::assertFalse($semaphore->hasPendingOperations());
        $two->await();
        static::assertSame(0, $semaphore->getOngoingOperations());
        static::assertSame(0, $semaphore->getPendingOperations());
        static::assertFalse($semaphore->hasOngoingOperations());
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

    public function testWaitForPendingReturnsImmediatelyWhenNotAtLimit(): void
    {
        $semaphore = new Async\Semaphore(1, static fn(string $input): string => $input);

        $semaphore->waitForPending();

        static::assertFalse($semaphore->hasOngoingOperations());
    }

    public function testWaitForCancelledWhileWaitingForSlot(): void
    {
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $semaphore->waitFor('first'))->ignore();

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): string => $semaphore->waitFor('second', $token))->await();
    }

    public function testWaitForPendingCancelledWhileWaiting(): void
    {
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $semaphore->waitFor('first'))->ignore();

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): null => $semaphore->waitForPending($token))->await();
    }

    public function testWaitForWithAlreadyCancelledToken(): void
    {
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $semaphore->waitFor('first'))->ignore();

        $token = new Async\SignalCancellationToken();
        $token->cancel();

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): string => $semaphore->waitFor('second', $token))->await();
    }

    public function testCancelledWaitForDoesNotAffectOtherOperations(): void
    {
        $semaphore = new Async\Semaphore(1, static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(30));

            return $input;
        });

        $first = Async\run(static fn(): string => $semaphore->waitFor('first'));

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));
        $second = Async\run(static fn(): string => $semaphore->waitFor('second', $token));

        try {
            $second->await();
            static::fail('Expected CancelledException');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        }

        static::assertSame('first', $first->await());
        static::assertFalse($semaphore->hasPendingOperations());
    }
}
