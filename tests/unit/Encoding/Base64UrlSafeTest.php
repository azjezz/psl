<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\Regex;
use Psl\SecureRandom;

final class Base64UrlSafeTest extends TestCase
{
    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeAndDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::UrlSafe);
        static::assertSame($random, Base64\decode($encoded, Base64\Variant::UrlSafe));
    }

    public function testDecodeThrowsForCharactersOutsideTheBase64Range(): void
    {
        $this->expectException(Exception\RangeException::class);

        Base64\decode('@~==', Base64\Variant::UrlSafe);
    }

    public function testDecodeThrowsForIncorrectPadding(): void
    {
        $this->expectException(Exception\IncorrectPaddingException::class);

        Base64\decode('ab', Base64\Variant::UrlSafe);
    }

    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeWithoutPaddingThenDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::UrlSafe, false);
        static::assertFalse(Regex\matches($encoded, '/={1,3}$/'));
        static::assertSame($random, Base64\decode($encoded, Base64\Variant::UrlSafe, false));
    }

    public function provideRandomBytes(): iterable
    {
        for ($i = 1; $i < 128; ++$i) {
            yield [SecureRandom\bytes($i)];
        }
    }
}
