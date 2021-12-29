<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\Str;

use function microtime;

final class SequenceTest extends TestCase
{
    public function testItCallsTheOperation(): void
    {
        $sequence = new Async\Sequence(static function (int $input): int {
            return $input * 2;
        });

        static::assertSame(4, $sequence->waitFor(2));
    }

    public function testSequenceOperationWaitsForPendingOperationsWhenLimitIsNotReached(): void
    {
        $spy = new Psl\Ref([]);

        /**
         * @var Async\Sequence<array{time: ?float, value: string}, void>
         */
        $sequence = new Async\Sequence(static function (array $data) use ($spy): void {
            if ($data['time'] !== null) {
                Async\sleep($data['time']);
            }

            $spy->value[] = $data['value'];
        });

        Async\run(static fn() => $sequence->waitFor(['time' => 0.003, 'value' => 'a']));
        Async\run(static fn() => $sequence->waitFor(['time' => 0.004, 'value' => 'b']));
        Async\run(static fn() => $sequence->waitFor(['time' => 0.005, 'value' => 'c']));
        $last = Async\run(static fn() => $sequence->waitFor(['time' => null, 'value' => 'd']));
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

            Async\sleep(0.002);
        });

        $awaitable = Async\run(static fn() => $sequence->waitFor('hello'));

        Async\sleep(0.001);

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

            Async\sleep(0.002);
        });

        Async\run(static fn() => $sequence->waitFor('hello'));
        $awaitable = Async\run(static fn() => $sequence->waitFor('world'));

        Async\sleep(0.001);

        static::assertNotContains('world', $spy->value);

        $awaitable->await();
    }

    public function testCancelingTheSequenceAllowsForFutureOperations(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $sequence = new Async\Sequence(static function (string $input): string {
            return $input;
        });

        $sequence->cancel(new Async\Exception\TimeoutException('The semaphore is destroyed.'));

        static::assertSame('hello', $sequence->waitFor('hello'));
    }

    public function testCancelPendingOperationsButNotTheOngoingOne(): void
    {
        /**
         * @var Async\Sequence<string, string>
         */
        $sequence = new Async\Sequence(static function (string $input): string {
            Async\sleep(0.04);

            return $input;
        });

        $one = Async\run(static fn() => $sequence->waitFor('one'));
        $two = Async\run(static fn() => $sequence->waitFor('two'));

        Async\sleep(0.01);

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

        $sequence = new Async\Sequence(static function (float $value) use ($ref): void {
            $ref->value .= Str\format('%f', $value);

            Async\sleep($value);
        });

        $time = microtime(true);

        Async\concurrently([
            static function () use ($sequence): void {
                $sequence->waitFor(0.02);
                $sequence->waitFor(0.02);
            },
            static fn() => $sequence->waitFor(0.02),
        ]);

        $duration = microtime(true) - $time;

        static::assertGreaterThanOrEqual(0.06, $duration);
        static::assertSame('0.0200000.0200000.020000', $ref->value);
    }
}
