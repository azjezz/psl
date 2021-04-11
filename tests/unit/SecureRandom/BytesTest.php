<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\SecureRandom;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\SecureRandom;
use Psl\Str;

final class BytesTest extends TestCase
{
    public function testBytes(): void
    {
        $random = SecureRandom\bytes(32);

        static::assertSame(32, Str\Byte\length($random));
    }

    public function testBytesEarlyReturnForZeroLength(): void
    {
        static::assertSame('', SecureRandom\bytes(0));
    }

    public function testBytesThrowsForNegativeLength(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-negative length.');

        SecureRandom\bytes(-1);
    }
}
