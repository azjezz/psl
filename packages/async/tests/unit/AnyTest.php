<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;
use Psl\Exception\InvariantViolationException;

final class AnyTest extends TestCase
{
    public function testAny(): void
    {
        $result = Async\any::<string>([
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(1));

                throw new InvariantViolationException('a');
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(2));

                throw new InvariantViolationException('b');
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(3));

                return 'c';
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::microseconds(500));

                Async\later();

                Async\sleep(DateTime\Duration::microseconds(500));

                return 'c';
            }),
        ]);

        static::assertSame('c', $result);
    }

    public function testAnyWithNoArguments(): void
    {
        $this->expectException(Async\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$awaitables must be a non-empty-iterable.');

        Async\any::<mixed>([]);
    }

    public function testAnyWillFailingAwaitables(): void
    {
        $this->expectException(Async\Exception\CompositeException::class);

        Async\any::<mixed>([
            Async\Awaitable::error(new InvariantViolationException('foo')),
            Async\Awaitable::error(new InvariantViolationException('bar')),
        ]);
    }
}
