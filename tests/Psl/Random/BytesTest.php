<?php

declare(strict_types=1);

namespace Psl\Tests\Random;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Random;
use Psl\Str;

class BytesTest extends TestCase
{
    public function testBytes(): void
    {
        $random = Random\bytes(32);

        self::assertSame(32, Str\Byte\length($random));
    }

    public function testBytesEarlyReturnForZeroLength(): void
    {
        self::assertSame('', Random\bytes(0));
    }

    public function testBytesThrowsForNegativeLength(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected positive length, got -1');

        Random\bytes(-1);
    }
}
