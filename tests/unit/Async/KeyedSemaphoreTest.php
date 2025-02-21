<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime;

final class KeyedSemaphoreTest extends TestCase
{
    public function testItCallsTheOperation(): void
    {
        $ks = new Async\KeyedSemaphore(1, static function (string $key, int $input): int {
            static::assertSame('one', $key);

            return $input * 2;
        });

        static::assertSame(4, $ks->waitFor('one', 2));
    }

    public function testSequenceOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\KeyedSemaphore<string, array{time: ?DateTime\Duration, value: string}, void>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $key, array $data) use ($spy): void {
            static::assertSame('operation', $key);

            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn(): null => $ks->waitFor('operation', [
            'time' => DateTime\Duration::milliseconds(3),
            'value' => 'a',
        ]));
        Async\run(static fn(): null => $ks->waitFor('operation', [
            'time' => DateTime\Duration::milliseconds(4),
            'value' => 'b',
        ]));
        Async\run(static fn(): null => $ks->waitFor('operation', [
            'time' => DateTime\Duration::milliseconds(5),
            'value' => 'c',
        ]));
        $last = Async\run(static fn(): null => $ks->waitFor('operation', [
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
         * @var Async\KeyedSemaphore<string, array{time: ?DateTime\Duration, value: string}, void>
         */
        $ks = new Async\KeyedSemaphore(2, static function (string $_, array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn(): null => $ks->waitFor('key', [
            'time' => DateTime\Duration::milliseconds(3),
            'value' => 'a',
        ]));
        Async\run(static fn(): null => $ks->waitFor('key', [
            'time' => DateTime\Duration::milliseconds(4),
            'value' => 'b',
        ]));
        $beforeLast = Async\run(static fn(): null => $ks->waitFor('key', [
            'time' => DateTime\Duration::milliseconds(5),
            'value' => 'c',
        ]));
        Async\run(static fn(): null => $ks->waitFor('key', [
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
         * @var Async\KeyedSemaphore<string, string, void>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(DateTime\Duration::milliseconds(2));
        });

        $awaitable = Async\run(static fn(): null => $ks->waitFor('x', 'hello'));

        Async\later();

        static::assertSame(['hello'], $spy->value);

        $awaitable->await();
    }

    public function testOperationIsNotStartedIfLimitIsReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\KeyedSemaphore<string, string, void>
         */
        $semaphore = new Async\KeyedSemaphore(1, static function (string $_, string $input) use ($spy): void {
            $spy->value[] = $input;

            Async\sleep(DateTime\Duration::milliseconds(2));
        });

        Async\run(static fn(): null => $semaphore->waitFor('x', 'hello'));
        $awaitable = Async\run(static fn(): null => $semaphore->waitFor('x', 'world'));

        Async\sleep(DateTime\Duration::milliseconds(1));

        static::assertNotContains('world', $spy->value);

        $awaitable->await();
    }

    public function testCancelingTheSemaphoreAllowsForFutureOperations(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $semaphore = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            return $input;
        });

        $semaphore->cancelAll(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('hello', $semaphore->waitFor('x', 'hello'));
    }

    public function testCancelPendingOperationsButNotTheOngoingOne(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $one = Async\run(static fn(): string => $ks->waitFor('foo', 'one'));
        $two = Async\run(static fn(): string => $ks->waitFor('foo', 'two'));

        Async\sleep(DateTime\Duration::milliseconds(10));

        $ks->cancel('foo', new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('one', $one->await());

        $this->expectException(Async\Exception\TimeoutException::class);
        $this->expectExceptionMessage('The semaphore is destroyed.');

        $two->await();
    }

    public function testCancelAllPendingOperations(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $ingoing = [
            Async\run(static fn(): string => $ks->waitFor('foo', 'ingoing')),
            Async\run(static fn(): string => $ks->waitFor('bar', 'ingoing')),
            Async\run(static fn(): string => $ks->waitFor('baz', 'ingoing')),
        ];

        $pending = [
            Async\run(static fn(): string => $ks->waitFor('foo', 'pending')),
            Async\run(static fn(): string => $ks->waitFor('bar', 'pending')),
            Async\run(static fn(): string => $ks->waitFor('baz', 'pending')),
        ];

        Async\sleep(DateTime\Duration::milliseconds(10));

        $ks->cancelAll(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        foreach ($ingoing as $awaitable) {
            static::assertSame('ingoing', $awaitable->await());
        }

        foreach ($pending as $awaitable) {
            try {
                static::assertSame('ingoing', $awaitable->await());
            } catch (Async\Exception\TimeoutException $e) {
                static::assertSame('The semaphore is destroyed.', $e->getMessage());
            }
        }
    }

    public function testSemaphoreStatus(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));

            return $input;
        });

        $key = 'foo';

        $one = Async\run(static fn(): string => $ks->waitFor($key, 'one'));
        $two = Async\run(static fn(): string => $ks->waitFor($key, 'two'));
        static::assertSame(0, $ks->getIngoingOperations($key));
        static::assertSame(0, $ks->getPendingOperations($key));
        static::assertFalse($ks->hasIngoingOperations($key));
        static::assertFalse($ks->hasPendingOperations($key));
        static::assertSame(0, $ks->getTotalIngoingOperations());
        static::assertSame(0, $ks->getTotalPendingOperations());
        static::assertFalse($ks->hasAnyIngoingOperations());
        static::assertFalse($ks->hasAnyPendingOperations());
        Async\later();
        static::assertSame(1, $ks->getIngoingOperations($key));
        static::assertSame(1, $ks->getPendingOperations($key));
        static::assertTrue($ks->hasPendingOperations($key));
        static::assertTrue($ks->hasIngoingOperations($key));
        static::assertSame(1, $ks->getTotalIngoingOperations());
        static::assertSame(1, $ks->getTotalPendingOperations());
        static::assertTrue($ks->hasAnyPendingOperations());
        static::assertTrue($ks->hasAnyIngoingOperations());
        $one->await();
        static::assertSame(1, $ks->getIngoingOperations($key));
        static::assertSame(0, $ks->getPendingOperations($key));
        static::assertTrue($ks->hasIngoingOperations($key));
        static::assertFalse($ks->hasPendingOperations($key));
        static::assertSame(1, $ks->getTotalIngoingOperations());
        static::assertSame(0, $ks->getTotalPendingOperations());
        static::assertTrue($ks->hasAnyIngoingOperations());
        static::assertFalse($ks->hasAnyPendingOperations());
        $two->await();
        static::assertSame(0, $ks->getIngoingOperations($key));
        static::assertSame(0, $ks->getPendingOperations($key));
        static::assertFalse($ks->hasIngoingOperations($key));
        static::assertFalse($ks->hasPendingOperations($key));
        static::assertSame(0, $ks->getTotalIngoingOperations());
        static::assertSame(0, $ks->getTotalPendingOperations());
        static::assertFalse($ks->hasAnyIngoingOperations());
        static::assertFalse($ks->hasAnyPendingOperations());
    }

    public function testWaitForRoom(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));
            return $input;
        });

        $one = Async\run(static fn(): string => $ks->waitFor('foo', 'one'));
        Async\later();
        static::assertFalse($one->isComplete());
        $ks->waitForPending('foo');
        static::assertTrue($one->isComplete());
        static::assertSame('one', $one->await());
    }

    public function testConcurrencyLimitOnDifferentKeys(): void
    {
        /**
         * @var Async\KeyedSemaphore<string, string, string>
         */
        $ks = new Async\KeyedSemaphore(1, static function (string $_, string $input): string {
            Async\sleep(DateTime\Duration::milliseconds(40));
            return $input;
        });
        static::assertSame(1, $ks->getConcurrencyLimit());

        static::assertFalse($ks->hasIngoingOperations('foo'));
        static::assertFalse($ks->hasIngoingOperations('bar'));
        static::assertFalse($ks->hasPendingOperations('foo'));
        static::assertFalse($ks->hasPendingOperations('bar'));

        $fooOne = Async\run(static fn(): string => $ks->waitFor('foo', 'one'));
        $barOne = Async\run(static fn(): string => $ks->waitFor('bar', 'one'));

        Async\later();

        static::assertTrue($ks->hasIngoingOperations('foo'));
        static::assertTrue($ks->hasIngoingOperations('bar'));
        static::assertFalse($ks->hasPendingOperations('foo'));
        static::assertFalse($ks->hasPendingOperations('bar'));

        $fooTwo = Async\run(static fn(): string => $ks->waitFor('foo', 'two'));
        $barTwo = Async\run(static fn(): string => $ks->waitFor('bar', 'two'));

        Async\later();

        static::assertTrue($ks->hasIngoingOperations('foo'));
        static::assertTrue($ks->hasIngoingOperations('bar'));
        static::assertTrue($ks->hasPendingOperations('foo'));
        static::assertTrue($ks->hasPendingOperations('bar'));

        static::assertSame('one', $fooOne->await());
        static::assertSame('one', $barOne->await());

        static::assertTrue($ks->hasIngoingOperations('foo'));
        static::assertTrue($ks->hasIngoingOperations('bar'));
        static::assertFalse($ks->hasPendingOperations('foo'));
        static::assertFalse($ks->hasPendingOperations('bar'));

        static::assertSame('two', $fooTwo->await());
        static::assertSame('two', $barTwo->await());

        static::assertFalse($ks->hasIngoingOperations('foo'));
        static::assertFalse($ks->hasIngoingOperations('bar'));
        static::assertFalse($ks->hasPendingOperations('foo'));
        static::assertFalse($ks->hasPendingOperations('bar'));
    }
}
