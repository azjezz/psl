<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\Regex;
use Psl\SecureRandom;

final class Base64DotSlashOrderedTest extends TestCase
{
    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeAndDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::DotSlashOrdered);
        static::assertSame($random, Base64\decode($encoded, Base64\Variant::DotSlashOrdered));
    }

    public function testDecodeThrowsForCharactersOutsideTheBase64Range(): void
    {
        $this->expectException(Exception\RangeException::class);

        Base64\decode('@~==', Base64\Variant::DotSlashOrdered);
    }

    public function testDecodeThrowsForIncorrectPadding(): void
    {
        $this->expectException(Exception\IncorrectPaddingException::class);

        Base64\decode('ab', Base64\Variant::DotSlashOrdered);
    }

    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeWithoutPaddingThenDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::DotSlashOrdered, false);
        static::assertFalse(Regex\matches($encoded, '/={1,3}$/'));
        static::assertSame($random, Base64\decode($encoded, Base64\Variant::DotSlashOrdered, false));
    }

    public function provideRandomBytes(): iterable
    {
        for ($i = 1; $i < 128; ++$i) {
            yield [SecureRandom\bytes($i)];
        }
    }
}
