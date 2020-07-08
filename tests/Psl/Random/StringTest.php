<?php

declare(strict_types=1);

namespace Psl\Tests\Random;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Random;
use Psl\Str;

class StringTest extends TestCase
{
    public function testString(): void
    {
        $random = Random\string(32);

        self::assertSame(32, Str\length($random));
        foreach (Str\chunk($random) as $char) {
            self::assertTrue(Str\contains(Str\ALPHABET_ALPHANUMERIC, $char));
        }
    }

    public function testStringWithSpecificChars(): void
    {
        $random = Random\string(32, 'abc');

        self::assertSame(32, Str\length($random));
        foreach (Str\chunk($random) as $char) {
            self::assertTrue(Str\contains('abc', $char));
        }
    }

    public function testStringEarlyReturnForZeroLength(): void
    {
        self::assertSame('', Random\string(0));
    }

    public function testStringThrowsForNegativeLength(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-negative length.');

        Random\string(-1);
    }

    public function testStringAlphabetMin(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $alphabet\'s length to be in [2^1, 2^56]');

        Random\string(32, 'a');
    }

    //  public function testStringAlphabetMax(): void
    //  {
    //      $this->markTestSkipped('Memory exhausting');
    //
    //      $this->expectException(Exception\InvariantViolationException::class);
    //      $this->expectExceptionMessage('Expected $alphabet\'s length to be in [2^1, 2^56]');
    //
    //      Random\string(32, Str\repeat('a', (2 ** 56) + 1));
    //  }
}
