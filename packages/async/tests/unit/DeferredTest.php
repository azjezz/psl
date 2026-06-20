<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;
use Psl\Exception\InvariantViolationException;

final class DeferredTest extends TestCase
{
    public function testComplete(): void
    {
        $deferred = new Async\Deferred::<string>();

        $placeholder = Async\run::<void>(static function () use ($deferred): void {
            Async\sleep(DateTime\Duration::milliseconds(1));

            $deferred->complete('hello');
        });

        static::assertFalse($deferred->isComplete());
        static::assertFalse($placeholder->isComplete());

        static::assertSame('hello', $deferred->getAwaitable()->await());

        static::assertTrue($deferred->isComplete());
        static::assertTrue($placeholder->isComplete());
    }

    public function testError(): void
    {
        $deferred = new Async\Deferred::<null>();

        $placeholder = Async\run::<void>(static function () use ($deferred): void {
            Async\sleep(DateTime\Duration::milliseconds(1));

            $deferred->error(new InvariantViolationException('hello'));
        });

        static::assertFalse($deferred->isComplete());
        static::assertFalse($placeholder->isComplete());

        $this->expectException(InvariantViolationException::class);

        $deferred->getAwaitable()->await();
    }
}
