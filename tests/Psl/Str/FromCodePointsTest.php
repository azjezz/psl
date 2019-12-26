<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class FromCodePointsTest extends TestCase
{
    public function testFromCodePoints(): void
    {
        self::assertSame(/* NULL = */ Str\chr(0), Str\from_code_points(0));

        self::assertSame('مرحبا بكم', Str\from_code_points(1605, 1585, 1581, 1576, 1575, 32, 1576, 1603, 1605));

        self::assertSame('Hello', Str\from_code_points(72, 101, 108, 108, 111));

        self::assertSame('ẞoo!', Str\from_code_points(7838, 111, 111, 33));

        self::assertSame('ς', Str\from_code_points(962));

        self::assertSame(Str\chr(0x10000 + 1), Str\from_code_points(0x10000 + 1));
    }
}
