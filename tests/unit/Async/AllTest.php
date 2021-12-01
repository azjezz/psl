<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class AllTest extends TestCase
{
    public function testAll(): void
    {
        $awaitables = [
            'a' => Async\run(static function (): string {
                Async\sleep(0.003);

                return 'a';
            }),
            'b' => Async\run(static function (): string {
                Async\sleep(0.001);

                return 'b';
            }),
            'c' => Async\run(static function (): string {
                Async\sleep(0.01);

                return 'c';
            }),
        ];

        $results = Async\all($awaitables);

        static::assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $results);
    }

    public function testAllForwardsException(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('a');

        Async\all([
            Async\Awaitable::error(new InvariantViolationException('a')),
            Async\Awaitable::complete('b'),
            Async\Awaitable::complete('c'),
        ]);

        Async\Scheduler::run();
    }

    public function testAllCompositeException(): void
    {
        try {
            Async\all([
                Async\Awaitable::error(new InvariantViolationException('a')),
                Async\Awaitable::error(new InvariantViolationException('b')),
                Async\Awaitable::complete('c'),
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
        $ref = new Psl\Ref('');

        try {
            Async\all([
                Async\run(static function () use ($ref): void {
                    $ref->value .= 'a';

                    throw new InvariantViolationException('a');
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.02);

                    $ref->value .= 'b';

                    throw new InvariantViolationException('b');
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.05);

                    $ref->value .= 'c';
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.00005);

                    Async\later();

                    Async\sleep(0.00005);

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
