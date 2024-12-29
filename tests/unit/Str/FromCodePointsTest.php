<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FromCodePointsTest extends TestCase
{
    public function testFromCodePoints(): void
    {
        static::assertSame(/* NULL = */ Str\chr(0), Str\from_code_points(0));

        static::assertSame(
            'مرحبا بكم',
            Str\from_code_points(1605, 1585, 1581, 1576, 1575, 32, 1576, 1603, 1605),
        );

        static::assertSame('Hello', Str\from_code_points(72, 101, 108, 108, 111));

        static::assertSame('ẞoo!', Str\from_code_points(7838, 111, 111, 33));

        static::assertSame('ς', Str\from_code_points(962));

        static::assertSame("\u{10001}", Str\from_code_points(65537));
    }
}
