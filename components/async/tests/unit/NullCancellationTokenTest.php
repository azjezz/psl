<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;

final class NullCancellationTokenTest extends TestCase
{
    public function testIsNeverCancelled(): void
    {
        $token = new Async\NullCancellationToken();

        static::assertFalse($token->isCancelled());
    }

    public function testThrowIfCancelledDoesNotThrow(): void
    {
        $token = new Async\NullCancellationToken();

        $token->throwIfCancelled();

        static::assertFalse($token->isCancelled());
    }

    public function testSubscribeReturnsNullString(): void
    {
        $token = new Async\NullCancellationToken();

        $id = $token->subscribe(static function (Async\Exception\CancelledException $e): void {
            static::fail('Should not be called');
        });

        static::assertSame('null', $id);
    }

    public function testUnsubscribeIsNoOp(): void
    {
        $token = new Async\NullCancellationToken();

        $token->unsubscribe('null');
        $token->unsubscribe('anything');

        static::assertFalse($token->isCancelled());
    }

    public function testCancellableIsFalse(): void
    {
        $token = new Async\NullCancellationToken();

        static::assertFalse($token->cancellable);
    }

    public function testDefault(): void
    {
        $token = Async\NullCancellationToken::default();

        static::assertInstanceOf(Async\NullCancellationToken::class, $token);
        static::assertFalse($token->cancellable);
    }
}
