<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\Regex;
use Psl\SecureRandom;

final class Base64urlTest extends TestCase
{
    /**
     * @dataProvider provideRandomBytes
     */
    public function testUrlEncodeAndUrlDecode(string $random): void
    {
        $encoded = Base64\url_encode($random, true);
        static::assertSame($random, Base64\url_decode($encoded, true));
    }

    public function testUrlDecodeThrowsForCharactersOutsideTheBase64urlRange(): void
    {
        $this->expectException(Exception\RangeException::class);

        Base64\url_decode('@~==');
    }

    public function testUrlDecodeThrowsForIncorrectPadding(): void
    {
        $this->expectException(Exception\IncorrectPaddingException::class);

        Base64\url_decode('ab', true);
    }

    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeWithoutPaddingThenDecode(string $random): void
    {
        $encoded = Base64\url_encode($random);
        static::assertFalse(Regex\matches($encoded, '/={1,3}$/'));
        static::assertSame($random, Base64\url_decode($encoded));
    }

    public function provideRandomBytes(): iterable
    {
        for ($i = 1; $i < 128; ++$i) {
            yield [SecureRandom\bytes($i)];
        }
    }
}
