<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\ControlSequenceIntroducerKind;

final class BackgroundTest extends TestCase
{
    public function testBasicColor(): void
    {
        $sequence = Ansi\background(Color\red());

        static::assertSame('41', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
    }

    public function testAnsi256Color(): void
    {
        $sequence = Ansi\background(Color\ansi256(196));

        static::assertSame('48;5;196', $sequence->parameters);
    }

    public function testRgbColor(): void
    {
        $sequence = Ansi\background(Color\rgb(255, 128, 0));

        static::assertSame('48;2;255;128;0', $sequence->parameters);
    }
}
