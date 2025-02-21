<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Result;
use Throwable;

final class TryCatchTest extends TestCase
{
    public function testTryResulting(): void
    {
        $actual = Result\try_catch(static fn(): true => true, static fn(): false => false);

        static::assertTrue($actual);
    }

    public function testTryFailing(): void
    {
        $actual = Result\try_catch(
            static fn(): never => throw new Exception('Not my style'),
            static fn(): false => false,
        );

        static::assertFalse($actual);
    }

    public function testTryThrowing(): void
    {
        $this->expectExceptionObject($expected = new Exception('Mine either'));

        Result\try_catch(
            static fn(): never => throw new Exception('Not my style'),
            static fn(): never => throw $expected,
        );
    }

    public function testReThrowing(): void
    {
        $this->expectExceptionObject($expected = new Exception('Not my style'));

        Result\try_catch(
            static fn(): never => throw $expected,
            static fn(Throwable $previous): never => throw $previous,
        );
    }
}
