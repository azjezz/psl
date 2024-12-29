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
        $actual = Result\try_catch(static fn() => true, static fn() => false);

        static::assertTrue($actual);
    }

    public function testTryFailing(): void
    {
        $actual = Result\try_catch(static fn() => throw new Exception('Not my style'), static fn() => false);

        static::assertFalse($actual);
    }

    public function testTryThrowing(): void
    {
        $this->expectExceptionObject($expected = new Exception('Mine either'));

        Result\try_catch(static fn() => throw new Exception('Not my style'), static fn() => throw $expected);
    }

    public function testReThrowing(): void
    {
        $this->expectExceptionObject($expected = new Exception('Not my style'));

        Result\try_catch(static fn() => throw $expected, static fn(Throwable $previous) => throw $previous);
    }
}
