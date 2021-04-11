<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\SecureRandom;

final class Base64Test extends TestCase
{
    /**
     * @dataProvider provideRandomBytes
     */
    public function testEncodeAndDecode(string $random): void
    {
        $encoded = Base64\encode($random);
        static::assertSame($random, Base64\decode($encoded));
    }

    public function testDecodeThrowsForCharactersOutsideTheBase64Range(): void
    {
        $this->expectException(Exception\RangeException::class);

        Base64\decode('@~==');
    }

    public function testDecodeThrowsForIncorrectPadding(): void
    {
        $this->expectException(Exception\IncorrectPaddingException::class);

        Base64\decode('ab');
    }

    public function provideRandomBytes(): iterable
    {
        for ($i = 1; $i < 128; ++$i) {
            yield [SecureRandom\bytes($i)];
        }
    }
}
