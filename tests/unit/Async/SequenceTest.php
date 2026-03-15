<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime;
use Psl\Str;

use function microtime;

final class SequenceTest extends TestCase
{
    public function testItCallsTheOperation(): void
    {
        $sequence = new Async\Sequence(static fn(int $input): int => $input * 2);

        static::assertSame(4, $sequence->waitFor(2));
    }

    public function testSequenceOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Sequence<array{time: ?float, value: string}, void>
         */
        $sequence = new Async\Sequence(static function (array $data) use ($spy): void {
            if (null !== $data['time']) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn(): null => $sequence->waitFor([
            'time' => DateTime\Duration::milliseconds(3),
            'value' => 'a',
        ]));
        Async\run(static fn(): null => $sequence->waitFor([
            'time' => DateTime\Duration::milliseconds(4),
            'value' => 'b',
        ]));
        Async\run(static fn(): null => $sequence->waitFor([
            'time' => DateTime\Duration::milliseconds(5),
            'value' => 'c',
        ]));
        $last = Async\run(static fn(): null => $sequence->waitFor([
            'time' => null,
            'value' => 'd',
        ]));
        $last->await();

        static::assertSame(['a', 'b', 'c', 'd'], $spy->value);
    }

    public function testOperationIsStartedIfLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Sequence<string, void>
         */
        $sequence = new Async\Sequence(static function (string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(DateTime\Duration::milliseconds(2));
        });

        $awaitable = Async\run(static fn(): null => $sequence->waitFor('hello'));

        Async\sleep(DateTime\Duration::milliseconds(1));

        static::assertSame(['hello'], $spy->value);

        $awaitable->await();
    }

    public function testOperationIsNotStartedIfLimitIsReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Sequence<string, void>
         */
        $sequence = new Async\Sequence(static function (string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(DateTime\Duration::milliseconds(2));
        });

        Async\run(static fn(): null => $sequence->waitFor('hello'));
        $awaitable = Async\run(static fn(): null => $sequence->waitFor('world'));

        Async\sleep(DateTime\Duration::milliseconds(1));

        static::assertNotContains('world', $spy->value);

        $awaitable->await();
    }

    public function testCancelingTheSequenceAllowsForFutureOperations(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $sequence = new Async\Sequence(static fn(string $input): string => $input);

        $sequence->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('hello', $sequence->waitFor('hello'));
    }

    public function testCancelPendingOperationsButNotTheOngoingOne(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $sequence->waitFor('one'));
        $two = Async\run(static fn(): string => $sequence->waitFor('two'));

        Async\sleep(DateTime\Duration::milliseconds(10));

        $sequence->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('one', $one->await());

        $this->expectException(Async\Exception\TimeoutException::class);
        $this->expectExceptionMessage('The semaphore is destroyed.');

        $two->await();
    }

    /**
     * @link https://github.com/azjezz/psl/issues/327
     */
    public function testBug327(): void
    {
        $ref = new Psl\Ref('');

        $sequence = new Async\Sequence(static function (DateTime\Duration $value) use ($ref): void {
            $ref->value .= Str\format('%f', $value->getTotalSeconds());

            Async\sleep($value);
        });

        $time = microtime(true);

        Async\concurrently([
            static function () use ($sequence): void {
                $sequence->waitFor(DateTime\Duration::milliseconds(20));
                $sequence->waitFor(DateTime\Duration::milliseconds(20));
            },
            static fn(): null => $sequence->waitFor(DateTime\Duration::milliseconds(20)),
        ]);

        $duration = microtime(true) - $time;

        static::assertGreaterThanOrEqual(0.06, $duration);
        static::assertSame('0.0200000.0200000.020000', $ref->value);
    }

    public function testStatus(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $s = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $s->waitFor('one'));
        $two = Async\run(static fn(): string => $s->waitFor('two'));
        static::assertFalse($s->hasOngoingOperations());
        static::assertFalse($s->hasPendingOperations());
        static::assertSame(0, $s->getPendingOperations());
        Async\later();
        static::assertTrue($s->hasOngoingOperations());
        static::assertTrue($s->hasPendingOperations());
        static::assertSame(1, $s->getPendingOperations());
        $one->await();
        static::assertTrue($s->hasOngoingOperations());
        static::assertFalse($s->hasPendingOperations());
        static::assertSame(0, $s->getPendingOperations());
        $two->await();
        static::assertFalse($s->hasOngoingOperations());
        static::assertFalse($s->hasPendingOperations());
        static::assertSame(0, $s->getPendingOperations());
    }

    public function testWaitForPending(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $s = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $s->waitFor('one'));
        $two = Async\run(static fn(): string => $s->waitFor('two'));
        static::assertFalse($s->hasOngoingOperations());
        static::assertFalse($s->hasPendingOperations());
        static::assertSame(0, $s->getPendingOperations());
        static::assertFalse($one->isComplete());
        static::assertFalse($two->isComplete());
        Async\later();
        static::assertTrue($s->hasOngoingOperations());
        static::assertTrue($s->hasPendingOperations());
        static::assertSame(1, $s->getPendingOperations());
        static::assertFalse($one->isComplete());
        static::assertFalse($two->isComplete());
        $s->waitForPending();
        static::assertFalse($s->hasOngoingOperations());
        static::assertFalse($s->hasPendingOperations());
        static::assertSame(0, $s->getPendingOperations());
        static::assertTrue($one->isComplete());
        static::assertTrue($two->isComplete());
    }

    public function testWaitForPendingReturnsImmediatelyWhenNotIngoing(): void
    {
        $s = new Async\Sequence(static fn(string $input): string => $input);

        $s->waitForPending();

        static::assertFalse($s->hasOngoingOperations());
    }

    public function testWaitForCancelledWhileWaiting(): void
    {
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $sequence->waitFor('first'))->ignore();

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): string => $sequence->waitFor('second', $token))->await();
    }

    public function testWaitForPendingCancelledWhileWaiting(): void
    {
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $sequence->waitFor('first'))->ignore();

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): null => $sequence->waitForPending($token))->await();
    }

    public function testWaitForWithAlreadyCancelledToken(): void
    {
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(100));

            return $input;
        });

        Async\run(static fn(): string => $sequence->waitFor('first'))->ignore();

        $token = new Async\SignalCancellationToken();
        $token->cancel();

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static fn(): string => $sequence->waitFor('second', $token))->await();
    }

    public function testCancelledWaitForDoesNotAffectOtherOperations(): void
    {
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(30));

            return $input;
        });

        $first = Async\run(static fn(): string => $sequence->waitFor('first'));

        $token = new Async\TimeoutCancellationToken(DateTime\Duration::milliseconds(10));
        $second = Async\run(static fn(): string => $sequence->waitFor('second', $token));

        try {
            $second->await();
            static::fail('Expected CancelledException');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        }

        static::assertSame('first', $first->await());
        static::assertFalse($sequence->hasPendingOperations());
    }
}
