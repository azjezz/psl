<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\SecureRandom;

use PHPUnit\Framework\TestCase;
use Psl\SecureRandom;
use Psl\Str;

final class StringTest extends TestCase
{
    public function testString(): void
    {
        $random = SecureRandom\string(32);

        static::assertSame(32, Str\length($random));
        foreach (Str\chunk($random) as $char) {
            static::assertTrue(Str\contains(Str\ALPHABET_ALPHANUMERIC, $char));
        }
    }

    public function testStringWithSpecificChars(): void
    {
        $random = SecureRandom\string(32, 'abc');

        static::assertSame(32, Str\length($random));
        foreach (Str\chunk($random) as $char) {
            static::assertTrue(Str\contains('abc', $char));
        }
    }

    public function testStringEarlyReturnForZeroLength(): void
    {
        static::assertSame('', SecureRandom\string(0));
    }

    public function testStringAlphabetMin(): void
    {
        $this->expectException(SecureRandom\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$alphabet\'s length must be in [2^1, 2^56]');

        SecureRandom\string(32, 'a');
    }
}
