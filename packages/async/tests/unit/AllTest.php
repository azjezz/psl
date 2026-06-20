<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime;
use Psl\Exception\InvariantViolationException;

final class AllTest extends TestCase
{
    public function testAll(): void
    {
        $awaitables = [
            'a' => Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(3));

                return 'a';
            }),
            'b' => Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(1));

                return 'b';
            }),
            'c' => Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(10));

                return 'c';
            }),
        ];

        $results = Async\all::<string, string>($awaitables);

        static::assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $results);
    }

    public function testAllForwardsException(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('a');

        Async\all::<int, string>([
            Async\Awaitable::error(new InvariantViolationException('a')),
            Async\Awaitable::<string>::complete('b'),
            Async\Awaitable::<string>::complete('c'),
        ]);

        Async\Scheduler::run();
    }

    public function testAllCompositeException(): void
    {
        try {
            Async\all::<int, string>([
                Async\Awaitable::error(new InvariantViolationException('a')),
                Async\Awaitable::error(new InvariantViolationException('b')),
                Async\Awaitable::<string>::complete('c'),
            ]);
        } catch (Async\Exception\CompositeException $exception) {
            $reasons = $exception->getReasons();

            static::assertCount(2, $reasons);
            static::assertSame('a', $reasons[0]->getMessage());
            static::assertSame('b', $reasons[1]->getMessage());
        }

        Async\Scheduler::run();
    }

    public function testAllAwaitablesAreCompletedAtALaterTime(): void
    {
        /** @var Psl\Ref<string> */
        $ref = new Psl\Ref::<string>('');

        try {
            Async\all::<int, void>([
                Async\run::<void>(static function () use ($ref): void {
                    $ref->value .= 'a';

                    throw new InvariantViolationException('a');
                }),
                Async\run::<void>(static function () use ($ref): void {
                    Async\sleep(DateTime\Duration::milliseconds(20));

                    $ref->value .= 'b';

                    throw new InvariantViolationException('b');
                }),
                Async\run::<void>(static function () use ($ref): void {
                    Async\sleep(DateTime\Duration::milliseconds(50));

                    $ref->value .= 'c';
                }),
                Async\run::<void>(static function () use ($ref): void {
                    Async\sleep(DateTime\Duration::microseconds(5));

                    Async\later();

                    Async\sleep(DateTime\Duration::microseconds(5));

                    $ref->value .= 'd';
                }),
            ]);
        } catch (InvariantViolationException $exception) {
            static::assertSame('a', $exception->getMessage());
        }

        Async\Scheduler::run();

        static::assertSame('adbc', $ref->value);
    }
}
