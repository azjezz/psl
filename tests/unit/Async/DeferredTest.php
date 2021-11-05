<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class DeferredTest extends TestCase
{
    public function testComplete(): void
    {
        $deferred = new Async\Deferred();

        $placeholder = Async\run(static function () use ($deferred) {
            Async\sleep(0.0001);

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
        $deferred = new Async\Deferred();

        $placeholder = Async\run(static function () use ($deferred) {
            Async\sleep(0.0001);

            $deferred->error(new InvariantViolationException('hello'));
        });

        static::assertFalse($deferred->isComplete());
        static::assertFalse($placeholder->isComplete());

        $this->expectException(InvariantViolationException::class);

        $deferred->getAwaitable()->await();
    }
}
