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
                Async\sleep(0.0001);

                throw new InvariantViolationException('a');
            }),
            Async\run(static function (): string {
                Async\sleep(0.0002);

                throw new InvariantViolationException('b');
            }),
            Async\run(static function (): string {
                Async\sleep(0.0003);

                return 'c';
            }),
            Async\run(static function (): string {
                Async\sleep(0.00005);

                Async\later();

                Async\sleep(0.00005);

                return 'c';
            }),
        ]);

        static::assertSame('c', $result);
    }

    public function testAnyWithNoArguments(): void
    {
        $this->expectException(Async\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$awaitables must be a non-empty-iterable.');

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
