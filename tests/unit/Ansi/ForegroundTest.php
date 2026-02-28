<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\ControlSequenceIntroducerKind;

final class ForegroundTest extends TestCase
{
    public function testBasicColor(): void
    {
        $sequence = Ansi\foreground(Color\red());

        static::assertSame('31', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
    }

    public function testAnsi256Color(): void
    {
        $sequence = Ansi\foreground(Color\ansi256(196));

        static::assertSame('38;5;196', $sequence->parameters);
    }

    public function testRgbColor(): void
    {
        $sequence = Ansi\foreground(Color\rgb(255, 128, 0));

        static::assertSame('38;2;255;128;0', $sequence->parameters);
    }
}
