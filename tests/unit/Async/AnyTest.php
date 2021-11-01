<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class AnyTest extends TestCase
{
    public function testAny(): void
    {
        $result = Async\any([
            Async\run(static function (): string {
                Async\usleep(100);

                throw new InvariantViolationException('a');
            }),
            Async\run(static function (): string {
                Async\usleep(200);

                throw new InvariantViolationException('b');
            }),
            Async\run(static function (): string {
                Async\usleep(300);

                return 'c';
            }),
            Async\run(static function (): string {
                Async\usleep(50);

                Async\later();

                Async\usleep(50);

                return 'c';
            }),
        ]);

        static::assertSame('c', $result);
    }

    public function testAnyWithNoArguments(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No awaitables were provided.');

        Async\any([]);
    }

    public function testAnyWillFailingAwaitables(): void
    {
        $this->expectException(Async\Exception\CompositeException::class);

        Async\any([
            Async\Awaitable::error(new InvariantViolationException('foo')),
            Async\Awaitable::error(new InvariantViolationException('bar')),
        ]);
    }
}
