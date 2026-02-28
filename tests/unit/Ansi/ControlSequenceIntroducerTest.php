<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\CommandInterface;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

final class ControlSequenceIntroducerTest extends TestCase
{
    public function testToString(): void
    {
        $csi = new ControlSequenceIntroducer('1;31', ControlSequenceIntroducerKind::SelectGraphicRendition);

        static::assertSame("\e[1;31m", $csi->toString());
    }

    public function testToStringCursorUp(): void
    {
        $csi = new ControlSequenceIntroducer('5', ControlSequenceIntroducerKind::CursorUp);

        static::assertSame("\e[5A", $csi->toString());
    }

    public function testStringable(): void
    {
        $csi = new ControlSequenceIntroducer('1', ControlSequenceIntroducerKind::SelectGraphicRendition);

        static::assertSame("\e[1m", (string) $csi);
    }

    public function testImplementsCommandInterface(): void
    {
        $csi = new ControlSequenceIntroducer('0', ControlSequenceIntroducerKind::SelectGraphicRendition);

        static::assertInstanceOf(CommandInterface::class, $csi);
    }
}
