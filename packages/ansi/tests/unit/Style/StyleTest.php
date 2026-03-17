<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit\Style;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\ControlSequenceIntroducerKind;
use Psl\Ansi\Style;

final class StyleTest extends TestCase
{
    public function testBold(): void
    {
        $sequence = Style\bold();

        static::assertSame('1', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[1m", $sequence->toString());
    }

    public function testDim(): void
    {
        $sequence = Style\dim();

        static::assertSame('2', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[2m", $sequence->toString());
    }

    public function testItalic(): void
    {
        $sequence = Style\italic();

        static::assertSame('3', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[3m", $sequence->toString());
    }

    public function testUnderline(): void
    {
        $sequence = Style\underline();

        static::assertSame('4', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[4m", $sequence->toString());
    }

    public function testBlink(): void
    {
        $sequence = Style\blink();

        static::assertSame('5', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[5m", $sequence->toString());
    }

    public function testReversed(): void
    {
        $sequence = Style\reversed();

        static::assertSame('7', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[7m", $sequence->toString());
    }

    public function testHidden(): void
    {
        $sequence = Style\hidden();

        static::assertSame('8', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[8m", $sequence->toString());
    }

    public function testStrikethrough(): void
    {
        $sequence = Style\strikethrough();

        static::assertSame('9', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[9m", $sequence->toString());
    }

    public function testDoubleUnderline(): void
    {
        $sequence = Style\double_underline();

        static::assertSame('21', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[21m", $sequence->toString());
    }

    public function testOverline(): void
    {
        $sequence = Style\overline();

        static::assertSame('53', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[53m", $sequence->toString());
    }
}
